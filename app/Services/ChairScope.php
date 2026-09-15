<?php

namespace App\Services;

use App\Models\SubTrack;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Answers "which tracks and sub-tracks is this person in charge of".
 *
 * An overall Track Chair holds the track itself and therefore every sub-track under
 * it; a Sub-Track Chair holds only their own. Admins and the TPC Chair see the whole
 * conference. Keeping that rule here means reviewer management, paper visibility and
 * the decision screens cannot drift apart later.
 */
class ChairScope
{
    private const ROLE_SUPER_ADMIN = 1;
    private const ROLE_ADMIN = 2;
    private const ROLE_TPC_CHAIR = 7;

    private User $user;
    private ?Collection $assignments = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public static function for(User $user): self
    {
        return new self($user);
    }

    public function seesEverything(): bool
    {
        return $this->user->roles
            ->whereIn('id', [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_TPC_CHAIR])
            ->isNotEmpty();
    }

    /** Tracks held outright, i.e. where this user is the overall chair. */
    public function wholeTrackIds(): array
    {
        if ($this->seesEverything()) {
            return Track::pluck('id')->all();
        }

        return $this->chairAssignments()
            ->whereNull('sub_track_id')
            ->pluck('track_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Sub-tracks this user may act on: the ones assigned to them directly, plus every
     * sub-track belonging to a track they chair outright.
     */
    public function subTrackIds(): array
    {
        if ($this->seesEverything()) {
            return SubTrack::pluck('id')->all();
        }

        $direct = $this->chairAssignments()
            ->whereNotNull('sub_track_id')
            ->pluck('sub_track_id');

        $inherited = SubTrack::whereIn('track_id', $this->wholeTrackIds())->pluck('id');

        return $direct->merge($inherited)->unique()->values()->all();
    }

    /** Every track they touch, whether outright or through a single sub-track. */
    public function trackIds(): array
    {
        if ($this->seesEverything()) {
            return Track::pluck('id')->all();
        }

        return $this->chairAssignments()
            ->pluck('track_id')
            ->unique()
            ->values()
            ->all();
    }

    public function canManage(int $trackId, ?int $subTrackId): bool
    {
        if ($this->seesEverything()) {
            return true;
        }

        if ($subTrackId === null) {
            return in_array($trackId, $this->wholeTrackIds(), true);
        }

        return in_array($subTrackId, $this->subTrackIds(), true);
    }

    /**
     * The papers this person may act on as a chair: every paper for SuperAdmin, Admin and
     * the TPC Chair, otherwise those in their tracks and sub-tracks. Rejected abstracts
     * are left out, since they go no further.
     */
    public function papers()
    {
        return $this->constrainPapers(\App\Models\Paper::query()->underConsideration());
    }

    /**
     * Narrows a paper query to what this person may see as a chair. Nothing is removed for
     * SuperAdmin, Admin or the TPC Chair; a Track Chair keeps every sub-track of their
     * track; a Sub-Track Chair keeps only their own sub-tracks; someone who chairs nothing
     * is left with no papers.
     */
    public function constrainPapers($query)
    {
        if ($this->seesEverything()) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereIn('papers.sub_track_id', $this->subTrackIds() ?: [0])
              ->orWhereIn('papers.track_id', $this->wholeTrackIds() ?: [0]);
        });
    }

    /** Whether this person may open the paper as a chair. */
    public function canSee(\App\Models\Paper $paper): bool
    {
        if ($this->seesEverything()) {
            return true;
        }

        return $paper->track_id
            && $this->canManage((int) $paper->track_id, $paper->sub_track_id ? (int) $paper->sub_track_id : null);
    }

    /**
     * Why this person has to stay out of decisions on a paper, or null when nothing
     * stands in the way: they wrote it, or its author declared a conflict with them.
     * Scope says where someone may act; this says where they must not, whatever their role.
     */
    public function conflictWith(\App\Models\Paper $paper): ?string
    {
        $email = \Illuminate\Support\Str::lower((string) $this->user->email);

        $authorEmails = $paper->authors->pluck('email')
            ->push($paper->user?->email)
            ->filter()
            ->map(fn ($e) => \Illuminate\Support\Str::lower($e));

        if ($authorEmails->contains($email)) {
            return 'You are an author on this paper, so another member of the committee has to handle it.';
        }

        if ($paper->conflicts->pluck('conflicted_user_id')->filter()->contains($this->user->id)) {
            return 'The author declared a conflict of interest with you, so another member of the committee has to handle it.';
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return !$this->seesEverything() && $this->chairAssignments()->isEmpty();
    }

    /**
     * The scopes to show on screen: each track they hold outright, followed by the
     * individual sub-tracks they may act on.
     *
     * @return Collection<int, array{track: Track, sub_track: SubTrack|null}>
     */
    public function manageableScopes(): Collection
    {
        $wholeTrackIds = $this->wholeTrackIds();
        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])
            ->whereIn('id', $this->trackIds())
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $subTrackIds = $this->subTrackIds();
        $scopes = collect();

        foreach ($tracks as $track) {
            if (in_array($track->id, $wholeTrackIds, true)) {
                $scopes->push(['track' => $track, 'sub_track' => null]);
            }

            foreach ($track->subTracks as $subTrack) {
                if (in_array($subTrack->id, $subTrackIds, true)) {
                    $scopes->push(['track' => $track, 'sub_track' => $subTrack]);
                }
            }
        }

        return $scopes;
    }

    private function chairAssignments(): Collection
    {
        return $this->assignments ??= TrackAssignment::where('user_id', $this->user->id)
            ->where('role', 'chair')
            ->get();
    }
}
