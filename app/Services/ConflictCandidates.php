<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperConflict;
use App\Models\TrackAssignment;

/**
 * Conflict-of-interest declarations made while a paper is being submitted, on either
 * the registration form or the submission form (requirement document, Phase 2).
 *
 * The author names reviewers of the chosen track (and its chairs, if Settings allows:
 * author_conflict_include_chairs). Those people are then never offered the paper to
 * review or decide on; ReviewerMatcher and ChairScope read these declarations.
 *
 * A conflict could once be declared against a free-text institution as well. Nothing
 * could act on it — reviewers carry no institution to match against — so it was dropped.
 * The requirement document asks only for "institutional reviewers/chairs", meaning the
 * people, not a separate institution.
 */
class ConflictCandidates
{
    /**
     * Whether authors may name conflicts themselves: on the submission forms and their
     * paper page. Setting author_conflict_declaration_enabled = 'false' switches that off;
     * declarations already made still apply, and someone holding paper_conflict_manage can
     * still record one on an author's behalf.
     */
    public static function authorsMayDeclare(): bool
    {
        return \App\Models\Setting::where('key', 'author_conflict_declaration_enabled')->value('value') !== 'false';
    }

    /**
     * Whether authors are offered the track's chairs and co-chairs as well as its
     * reviewers. Setting author_conflict_include_chairs; on unless set to 'false', in
     * which case authors name reviewers only. Conflicts already declared still apply, and
     * someone holding paper_conflict_manage can still record one against a chair.
     */
    public static function chairsOffered(): bool
    {
        return \App\Models\Setting::where('key', 'author_conflict_include_chairs')->value('value') !== 'false';
    }

    /**
     * Who may be named for a paper in this track: its reviewers, and its chairs when
     * $withChairs (by default, as Settings says).
     *
     * @return \Illuminate\Support\Collection<int, int> user ids
     */
    public static function userIdsForTrack(int $trackId, ?bool $withChairs = null): \Illuminate\Support\Collection
    {
        $withChairs ??= self::chairsOffered();
        $rows = TrackAssignment::where('track_id', $trackId)->get(['user_id', 'role']);
        $ids = $rows->pluck('user_id')->unique();

        // A chair who also reviews in the track counts as a chair.
        return ($withChairs ? $ids : $ids->diff($rows->where('role', 'chair')->pluck('user_id')))->values();
    }

    /**
     * The people of every track an author may name, for the form to offer once a track is
     * chosen: reviewers, and chairs if Settings allows. A person who both chairs and
     * reviews in a track is listed once, as chair.
     *
     * @return array<int, array<int, array{id: int, label: string}>> track id => people
     */
    public static function byTrack(): array
    {
        $withChairs = self::chairsOffered();

        return TrackAssignment::with('user')
            ->get()
            ->filter(fn ($row) => $row->user !== null)
            ->groupBy('track_id')
            ->map(function ($rows) use ($withChairs) {
                return $rows->groupBy('user_id')
                    ->reject(fn ($personRows) => !$withChairs && $personRows->contains('role', 'chair'))
                    ->map(function ($personRows) {
                        $user = $personRows->first()->user;
                        $role = $personRows->contains('role', 'chair') ? 'Chair' : 'Reviewer';

                        return ['id' => $user->id, 'label' => "{$user->name} ({$role})"];
                    })
                    ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();
            })
            ->all();
    }

    /** Validation rules for the conflict fields; all optional. */
    public static function rules(): array
    {
        return [
            'conflict_user_ids' => ['nullable', 'array', 'max:50'],
            'conflict_user_ids.*' => ['integer', 'exists:users,id'],
            'conflict_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Records the declarations for a new paper. Anyone the form would not have offered
     * (outside the paper's track, or a chair while chairs are not offered) is ignored.
     *
     * @return int how many conflicts were recorded
     */
    public static function record(Paper $paper, int $declaredBy, array $input): int
    {
        if (!$paper->track_id || !self::authorsMayDeclare()) {
            return 0;
        }

        $inTrack = self::userIdsForTrack((int) $paper->track_id);
        $note = filled($input['conflict_note'] ?? null) ? trim($input['conflict_note']) : null;
        $recorded = 0;

        foreach (array_unique(array_map('intval', (array) ($input['conflict_user_ids'] ?? []))) as $userId) {
            if (!$inTrack->contains($userId)) {
                continue;
            }

            PaperConflict::firstOrCreate(
                ['paper_id' => $paper->id, 'conflicted_user_id' => $userId],
                ['declared_by_user_id' => $declaredBy, 'note' => $note]
            );
            $recorded++;
        }

        return $recorded;
    }
}
