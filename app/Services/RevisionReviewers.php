<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who checks the revised manuscript of a paper accepted with minor revisions
 * (organisers, 2026-10-05): the paper's Track Chair and Sub-Track Chair (the co-chair),
 * and the TPC Chair. Each must also hold the revision_review permission, so taking it
 * away under Roles stops them seeing or acting on revisions.
 */
class RevisionReviewers
{
    private const ROLE_TPC_CHAIR = 7;

    /** Whether this person may check this paper's revision. */
    public static function canReview(User $user, Paper $paper): bool
    {
        $scope = ChairScope::for($user);

        return $paper->track_id
            && $scope->canManage((int) $paper->track_id, $paper->sub_track_id ? (int) $paper->sub_track_id : null)
            && $scope->conflictWith($paper) === null;
    }

    /**
     * The chairs to tell that a revision is waiting: everyone holding the permission
     * whose scope covers the paper, apart from administrators, who see the queue anyway.
     *
     * @return Collection<int, User>
     */
    public static function toNotify(Paper $paper): Collection
    {
        $roleIds = Permission::where('title', 'revision_review')->first()?->roles()->pluck('roles.id') ?? collect();

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $paper->loadMissing(['authors', 'conflicts', 'user']);

        return User::with('roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->get()
            ->filter(function (User $user) use ($paper) {
                $isTpcChair = $user->roles->contains('id', self::ROLE_TPC_CHAIR);
                $chairsTrack = \App\Models\TrackAssignment::where('user_id', $user->id)->where('role', 'chair')->exists();

                return ($isTpcChair || $chairsTrack) && self::canReview($user, $paper);
            })
            ->values();
    }
}
