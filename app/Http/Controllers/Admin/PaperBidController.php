<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperBid;
use App\Models\PaperReviewerAssignment;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\SubmissionRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paper bidding (requirement document, Phase 3): "System presents reviewers with paper
 * titles and abstracts. Reviewers mark their preference."
 *
 * A reviewer sees the papers in the tracks and sub-tracks they review for, and only
 * the title, abstract, keywords and track. Authors are never shown here, whatever the
 * blind review setting, since the document offers nothing more at this stage. The
 * reviewer's own papers are left out.
 */
class PaperBidController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('review_bid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();

        // 1. Fetch reviewer's assigned pool/scopes
        $poolAssignments = TrackAssignment::where('user_id', $user->id)
            ->where('role', 'reviewer')
            ->with(['track', 'subTrack'])
            ->get();

        $inPool = $poolAssignments->isNotEmpty();

        // Build list of selectable scopes (only reviewer's assigned tracks/sub-tracks)
        $assignedScopes = [];
        foreach ($poolAssignments as $ta) {
            if ($ta->sub_track_id && $ta->subTrack) {
                $key = 'subtrack_' . $ta->sub_track_id;
                $label = ($ta->track ? $ta->track->name . ' → ' : '') . $ta->subTrack->name;
                $assignedScopes[$key] = [
                    'key' => $key,
                    'type' => 'subtrack',
                    'id' => $ta->sub_track_id,
                    'label' => $label,
                ];
            } elseif ($ta->track) {
                $key = 'track_' . $ta->track_id;
                $label = $ta->track->name . ' (Whole Track)';
                $assignedScopes[$key] = [
                    'key' => $key,
                    'type' => 'track',
                    'id' => $ta->track_id,
                    'label' => $label,
                ];
            }
        }

        // Base query for all biddable papers for this reviewer
        $baseQuery = $this->biddablePapers($user)->with(['track', 'subTrack']);

        // Filters
        $trackScope = $request->string('track_scope')->toString();
        if ($trackScope && isset($assignedScopes[$trackScope])) {
            $scopeInfo = $assignedScopes[$trackScope];
            if ($scopeInfo['type'] === 'subtrack') {
                $baseQuery->where('sub_track_id', $scopeInfo['id']);
            } elseif ($scopeInfo['type'] === 'track') {
                $baseQuery->where('track_id', $scopeInfo['id']);
            }
        }

        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        if ($dateFrom) {
            $baseQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('created_at', '<=', $dateTo);
        }

        $searchQuery = trim($request->string('q')->toString());
        if ($searchQuery !== '') {
            $baseQuery->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                  ->orWhere('submission_id', 'like', "%{$searchQuery}%")
                  ->orWhere('keywords', 'like', "%{$searchQuery}%")
                  ->orWhere('abstract', 'like', "%{$searchQuery}%");
            });
        }

        $allBiddablePapers = $baseQuery->get();

        // Existing bids for this reviewer
        $bids = PaperBid::where('reviewer_id', $user->id)
            ->whereIn('paper_id', $allBiddablePapers->pluck('id'))
            ->pluck('preference', 'paper_id');

        // Reviewer expertise keywords
        $reviewerExpertise = $user->allExpertise();
        $normReviewerMap = [];
        foreach ($reviewerExpertise as $exp) {
            $normReviewerMap[SubmissionRules::normaliseKeyword($exp)] = true;
            $normReviewerMap[mb_strtolower(trim($exp))] = true;
        }

        // Calculate keyword matches for each paper
        foreach ($allBiddablePapers as $paper) {
            $paperKeywords = SubmissionRules::splitKeywords($paper->keywords);
            $matchedKeywords = [];

            foreach ($paperKeywords as $kw) {
                $kwNorm = SubmissionRules::normaliseKeyword($kw);
                $kwLower = mb_strtolower(trim($kw));
                $isMatch = isset($normReviewerMap[$kwNorm]) || isset($normReviewerMap[$kwLower]);

                if (!$isMatch) {
                    foreach ($reviewerExpertise as $exp) {
                        $expLower = mb_strtolower(trim($exp));
                        if ($expLower === $kwLower || str_contains($expLower, $kwLower) || str_contains($kwLower, $expLower)) {
                            $isMatch = true;
                            break;
                        }
                    }
                }

                if ($isMatch) {
                    $matchedKeywords[$kw] = true;
                }
            }

            $paper->matched_keywords = $matchedKeywords;
            $paper->match_count = count($matchedKeywords);
        }

        // Counts for tabs/filters
        $totalAll = $allBiddablePapers->count();
        $totalMarked = $bids->count();
        $totalUnmarked = $totalAll - $totalMarked;
        $totalMatched = $allBiddablePapers->where('match_count', '>', 0)->count();

        $countWant = $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'want')->count();
        $countCan = $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'can')->count();
        $countNeutral = $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'neutral')->count();
        $countConflict = $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'conflict')->count();

        // Apply Status Filter
        $filter = $request->string('filter')->toString() ?: 'all';
        $papers = match ($filter) {
            'unmarked' => $allBiddablePapers->reject(fn ($p) => $bids->has($p->id)),
            'matched' => $allBiddablePapers->where('match_count', '>', 0),
            'want' => $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'want'),
            'can' => $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'can'),
            'neutral' => $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'neutral'),
            'conflict' => $allBiddablePapers->filter(fn ($p) => ($bids[$p->id] ?? null) === 'conflict'),
            default => $allBiddablePapers,
        };

        // Apply Sorting
        // Default: 'match' places papers matching reviewer expertise at the very TOP!
        $sort = $request->string('sort')->toString() ?: 'match';
        $papers = match ($sort) {
            'latest' => $papers->sortByDesc(fn ($p) => $p->created_at?->timestamp ?? 0),
            'oldest' => $papers->sortBy(fn ($p) => $p->created_at?->timestamp ?? 0),
            'title' => $papers->sortBy(fn ($p) => mb_strtolower($p->title)),
            default => $papers->sort(function ($a, $b) {
                // Primary: highest match count first
                if ($a->match_count !== $b->match_count) {
                    return $b->match_count <=> $a->match_count;
                }
                // Secondary: newest submission first
                $timeA = $a->created_at ? $a->created_at->timestamp : 0;
                $timeB = $b->created_at ? $b->created_at->timestamp : 0;
                return $timeB <=> $timeA;
            }),
        };

        $papers = $papers->values();

        return view('admin.paper_bids.index', [
            'papers' => $papers,
            'bids' => $bids,
            'assignedIds' => PaperReviewerAssignment::where('reviewer_id', $user->id)->pluck('paper_id'),
            'preferences' => PaperBid::LABELS,
            'biddingOpen' => SubmissionRules::biddingIsOpen(),
            'inPool' => $inPool,
            'assignedScopes' => $assignedScopes,
            'trackScope' => $trackScope,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'searchQuery' => $searchQuery,
            'sort' => $sort,
            'filter' => $filter,
            'marked' => $totalMarked,
            'total' => $totalAll,
            'totalUnmarked' => $totalUnmarked,
            'totalMatched' => $totalMatched,
            'countWant' => $countWant,
            'countCan' => $countCan,
            'countNeutral' => $countNeutral,
            'countConflict' => $countConflict,
            'reviewerExpertise' => $reviewerExpertise,
        ]);
    }

    /**
     * Handles both instant single-paper AJAX auto-save and bulk form submit.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('review_bid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (!SubmissionRules::biddingIsOpen()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bidding is closed, so preferences can no longer be changed.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return back()->with('error', 'Bidding is closed, so preferences can no longer be changed.');
        }

        $user = auth()->user();
        $allowedIds = $this->biddablePapers($user)->pluck('id')->all();

        // 1. Instant Single Paper Auto-Save via AJAX
        if ($request->has('paper_id')) {
            $validated = $request->validate([
                'paper_id' => ['required', 'integer', Rule::in($allowedIds)],
                'preference' => ['required', Rule::in(array_keys(PaperBid::LABELS))],
            ], [
                'paper_id.in' => 'This paper is not in your review pool.',
                'preference.in' => 'Invalid preference option selected.',
            ]);

            $paperId = (int) $validated['paper_id'];
            $preference = $validated['preference'];

            $bid = PaperBid::updateOrCreate(
                ['paper_id' => $paperId, 'reviewer_id' => $user->id],
                ['preference' => $preference]
            );

            $marked = PaperBid::where('reviewer_id', $user->id)
                ->whereIn('paper_id', $allowedIds)
                ->count();
            $total = count($allowedIds);

            return response()->json([
                'success' => true,
                'message' => 'Saved',
                'paper_id' => $paperId,
                'preference' => $preference,
                'label' => PaperBid::LABELS[$preference] ?? $preference,
                'marked' => $marked,
                'total' => $total,
                'percentage' => $total > 0 ? round(($marked / $total) * 100) : 0,
            ]);
        }

        // 2. Fallback Bulk Form Submit
        $data = $request->validate([
            'bids' => 'required|array',
            'bids.*' => ['required', Rule::in(array_keys(PaperBid::LABELS))],
        ], [
            'bids.required' => 'Mark at least one paper before saving.',
            'bids.*.in' => 'Choose Want to Review, Can Review, Neutral or Conflict for each paper.',
        ]);

        $changed = 0;
        DB::transaction(function () use ($data, $user, $allowedIds, &$changed) {
            foreach ($data['bids'] as $paperId => $preference) {
                abort_unless(in_array((int) $paperId, $allowedIds, true), Response::HTTP_FORBIDDEN,
                    '403 Forbidden - that paper is outside your review pool.');

                $bid = PaperBid::updateOrCreate(
                    ['paper_id' => (int) $paperId, 'reviewer_id' => $user->id],
                    ['preference' => $preference]
                );

                $changed += ($bid->wasRecentlyCreated || $bid->wasChanged()) ? 1 : 0;
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            $marked = PaperBid::where('reviewer_id', $user->id)
                ->whereIn('paper_id', $allowedIds)
                ->count();
            $total = count($allowedIds);
            return response()->json([
                'success' => true,
                'message' => 'Preferences saved.',
                'marked' => $marked,
                'total' => $total,
                'percentage' => $total > 0 ? round(($marked / $total) * 100) : 0,
            ]);
        }

        return back()->with('success', $changed
            ? $changed . ' preference' . ($changed === 1 ? '' : 's') . ' saved.'
            : 'Nothing had changed.');
    }

    /**
     * Papers in the reviewer's pool: a whole track they review for, or one of their
     * sub-tracks. Rejected abstracts and the reviewer's own papers are left out.
     */
    private function biddablePapers(User $user)
    {
        return \App\Services\BiddablePapers::query($user);
    }
}