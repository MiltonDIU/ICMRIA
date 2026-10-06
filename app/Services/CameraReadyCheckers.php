<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who checks an accepted paper's camera-ready files before the fee (organisers,
 * 2026-10-06): the paper's Track Chair or Sub-Track Chair, and anyone whose role holds
 * camera_ready_all_tracks (the Proceedings Editor role, and the administrators), who
 * check every track. Each also needs camera_ready_approve; nobody checks a paper they
 * wrote or with whom a conflict was declared.
 */
class CameraReadyCheckers
{
    /** Sees every track on the Camera-Ready Check screen. */
    public static function seesAllTracks(User $user): bool
    {
        return ChairScope::for($user)->seesEverything() || self::holds($user, 'camera_ready_all_tracks');
    }

    public static function canCheck(User $user, Paper $paper): bool
    {
        if (self::seesAllTracks($user)) {
            return ChairScope::for($user)->conflictWith($paper) === null;
        }

        return RevisionReviewers::canReview($user, $paper);
    }

    /**
     * Who to email when both files are in: the paper's chairs, and the Proceedings
     * Editors. Administrators see the queue anyway and are not emailed.
     *
     * @return Collection<int, User>
     */
    public static function toNotify(Paper $paper): Collection
    {
        $chairs = RevisionReviewers::chairsToNotify($paper, 'camera_ready_approve', false);

        $editorRoleIds = Permission::where('title', 'camera_ready_all_tracks')->first()?->roles()->pluck('roles.id') ?? collect();
        $editors = $editorRoleIds->isEmpty() ? collect() : User::with('roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $editorRoleIds))
            ->get()
            ->filter(fn (User $user) => !ChairScope::for($user)->seesEverything()
                && self::holds($user, 'camera_ready_approve')
                && ChairScope::for($user)->conflictWith($paper) === null);

        return $chairs->merge($editors)->unique('id')->values();
    }

    /** Whether one of the user's roles holds the permission (works outside a web request too). */
    private static function holds(User $user, string $permission): bool
    {
        return $user->roles()->whereHas('permissions', fn ($q) => $q->where('title', $permission))->exists();
    }
}
