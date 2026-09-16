<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperBid;
use App\Models\PaperReviewerAssignment;
use App\Models\Setting;
use App\Models\TrackAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Works out who may review a paper, and how well each of them fits.
 *
 * The document names four criteria for automatic assignment (Phase 3): subject
 * keywords, reviewer expertise, workload limits and conflict detection. The first
 * two become the score; the last two are hard filters, because a conflicted or
 * overloaded reviewer must not be offered at all rather than merely ranked lower.
 *
 * Bids from the optional bidding step sit on top. A Conflict bid refuses the reviewer
 * just as a declared conflict does, and someone who asked for the paper ranks above
 * someone who did not before keyword match is compared.
 */
class ReviewerMatcher
{
    /**
     * The whole reviewer pool for this paper's track, best fit first, with the ones
     * who cannot take it marked and pushed to the bottom.
     *
     * Nobody is hidden. If a conflicted reviewer simply vanished, a chair looking at a
     * short list would have no way to tell an empty pool from an author who has
     * declared a conflict against everyone; naming the reason makes that visible.
     * People already assigned are the exception, since they are listed above the
     * candidates on the same screen.
     *
     * @return Collection<int, array{
     *     reviewer: User, score: int, load: int, capacity: int,
     *     expertise: array<int, string>, eligible: bool, reason: string|null, bid: string|null,
     *     institution_warning: bool
     * }>
     */
    public function candidatesFor(Paper $paper): Collection
    {
        $pool = $this->pool($paper);

        if ($pool->isEmpty()) {
            return collect();
        }

        $capacity = $this->capacityFor($paper);
        $loads = $this->openLoads($pool->pluck('user_id')->all());
        $institutions = $this->conflictedInstitutions($paper);
        $alreadyAssigned = $paper->reviewerAssignments->pluck('reviewer_id');
        $bids = $paper->bids->pluck('preference', 'reviewer_id');

        return $pool
            ->reject(fn ($row) => $alreadyAssigned->contains($row->user_id))
            ->map(function ($row) use ($paper, $loads, $capacity, $institutions, $bids) {
                $reason = $this->reasonToRefuse($paper, $row->user);

                // What the chairs recorded for this track, plus the reviewer's own research keywords.
                $expertise = SubmissionRules::splitKeywords(array_merge(
                    SubmissionRules::splitKeywords($row->expertise),
                    SubmissionRules::splitKeywords($row->user->research_keywords)
                ));

                return [
                    'reviewer' => $row->user,
                    'score' => SubmissionRules::keywordOverlap($paper->keywords, $expertise),
                    'load' => $loads[$row->user_id] ?? 0,
                    'capacity' => $capacity,
                    'expertise' => $expertise,
                    'eligible' => $reason === null,
                    'reason' => $reason,
                    'bid' => $bids->get($row->user_id),
                    // Reviewers carry no institution of their own, so a conflict named
                    // against an institution cannot be matched automatically. The chair
                    // is told to weigh it instead of it being silently ignored.
                    'institution_warning' => $institutions->isNotEmpty(),
                ];
            })
            // Eligible first, then those who asked for the paper, then by how well they match.
            ->sortByDesc(fn ($row) => [$row['eligible'] ? 1 : 0, PaperBid::rank($row['bid']), $row['score']])
            ->values();
    }

    /** Only those who can actually be given the paper. */
    public function eligibleFor(Paper $paper): Collection
    {
        return $this->candidatesFor($paper)->where('eligible', true)->values();
    }

    /**
     * Picks the best-fitting reviewers for a paper without assigning them, so the
     * caller can show the proposal before acting on it.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function propose(Paper $paper, ?int $howMany = null): Collection
    {
        $howMany ??= $this->reviewersWanted($paper);

        return $this->eligibleFor($paper)->take($howMany);
    }

    /** How many reviewers this paper should end up with. */
    public function reviewersWanted(Paper $paper): int
    {
        return (int) ($paper->track?->reviewers_per_paper
            ?: Setting::where('key', 'reviewers_per_paper')->value('value')
            ?: 3);
    }

    /** The floor the document sets: "at least 2 to 3 independent reviewers". */
    public function minimumReviewers(): int
    {
        return (int) (Setting::where('key', 'min_reviewers_per_paper')->value('value') ?: 2);
    }

    /** Papers one reviewer may hold at once, overridable per track. */
    public function capacityFor(Paper $paper): int
    {
        return (int) ($paper->track?->max_papers_per_reviewer
            ?: Setting::where('key', 'max_papers_per_reviewer')->value('value')
            ?: 10);
    }

    /**
     * Why a particular reviewer cannot take this paper, or null if they can. Used to
     * explain a refusal rather than just rejecting the request.
     */
    public function reasonToRefuse(Paper $paper, User $reviewer): ?string
    {
        // Only reviewers score. A chair of the paper's track, the TPC Chair or an
        // administrator decides on the paper instead, so cannot be handed it to score.
        if (ChairScope::for($reviewer)->canSee($paper)) {
            return 'They are on the committee that decides this paper, so they cannot score it.';
        }

        if ($this->authorEmails($paper)->contains(Str::lower($reviewer->email))) {
            return 'They are an author on this paper.';
        }

        if ($paper->conflicts->pluck('conflicted_user_id')->filter()->contains($reviewer->id)) {
            return 'The author declared a conflict of interest with them.';
        }

        if ($paper->bids->where('reviewer_id', $reviewer->id)->where('preference', PaperBid::CONFLICT)->isNotEmpty()) {
            return 'They marked a conflict with this paper when bidding.';
        }

        if (!$this->pool($paper)->pluck('user_id')->contains($reviewer->id)) {
            return 'They are not in the reviewer pool for this track.';
        }

        $load = $this->openLoads([$reviewer->id])[$reviewer->id] ?? 0;
        $capacity = $this->capacityFor($paper);
        if ($load >= $capacity) {
            $papers = $load === 1 ? '1 paper' : "{$load} papers";

            return "They already hold {$papers}, which is the limit for this track.";
        }

        if ($paper->reviewerAssignments()->where('reviewer_id', $reviewer->id)->exists()) {
            return 'They are already assigned to this paper.';
        }

        return null;
    }

    /** The reviewer assignments covering this paper's sub-track, or its track. */
    private function pool(Paper $paper): Collection
    {
        if (!$paper->track_id) {
            return collect();
        }

        return TrackAssignment::with('user')
            ->where('role', 'reviewer')
            ->where('track_id', $paper->track_id)
            ->where(function ($query) use ($paper) {
                $query->whereNull('sub_track_id')
                    ->orWhere('sub_track_id', $paper->sub_track_id);
            })
            ->get()
            ->filter(fn ($row) => $row->user !== null)
            ->unique('user_id')
            ->values();
    }

    /** Everyone named on the paper, so nobody reviews their own work. */
    private function authorEmails(Paper $paper): Collection
    {
        return $paper->authors
            ->pluck('email')
            ->push($paper->user?->email)
            ->filter()
            ->map(fn ($e) => Str::lower($e))
            ->unique()
            ->values();
    }

    private function conflictedInstitutions(Paper $paper): Collection
    {
        return $paper->conflicts->pluck('conflicted_institution')->filter()->values();
    }

    /**
     * How many open papers each of these reviewers currently holds.
     *
     * @param array<int, int> $reviewerIds
     * @return array<int, int>
     */
    private function openLoads(array $reviewerIds): array
    {
        if (!$reviewerIds) {
            return [];
        }

        return PaperReviewerAssignment::open()
            ->whereIn('reviewer_id', $reviewerIds)
            ->selectRaw('reviewer_id, COUNT(*) as total')
            ->groupBy('reviewer_id')
            ->pluck('total', 'reviewer_id')
            ->all();
    }
}
