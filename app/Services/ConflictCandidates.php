<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperConflict;
use App\Models\TrackAssignment;

/**
 * Conflict-of-interest declarations made while a paper is being submitted, on either
 * the registration form or the submission form (requirement document, Phase 2).
 *
 * The author can name anyone who chairs or reviews in the chosen track, and an
 * institution besides. Declared people are then never offered the paper to review or
 * decide on; ReviewerMatcher and ChairScope already read these declarations.
 */
class ConflictCandidates
{
    /**
     * The chairs and reviewers of every track, for the form to offer once a track is
     * chosen. A person who both chairs and reviews in a track is listed once, as chair.
     *
     * @return array<int, array<int, array{id: int, label: string}>> track id => people
     */
    public static function byTrack(): array
    {
        return TrackAssignment::with('user')
            ->get()
            ->filter(fn ($row) => $row->user !== null)
            ->groupBy('track_id')
            ->map(function ($rows) {
                return $rows->groupBy('user_id')
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
            'conflict_institution' => ['nullable', 'string', 'max:255'],
            'conflict_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Records the declarations for a new paper. People outside the paper's track are
     * ignored, since the form only ever offers the track's own chairs and reviewers.
     *
     * @return int how many conflicts were recorded
     */
    public static function record(Paper $paper, int $declaredBy, array $input): int
    {
        if (!$paper->track_id) {
            return 0;
        }

        $inTrack = TrackAssignment::where('track_id', $paper->track_id)->pluck('user_id')->unique();
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

        if (filled($input['conflict_institution'] ?? null)) {
            PaperConflict::create([
                'paper_id' => $paper->id,
                'declared_by_user_id' => $declaredBy,
                'conflicted_institution' => trim($input['conflict_institution']),
                'note' => $note,
            ]);
            $recorded++;
        }

        return $recorded;
    }
}
