<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/**
 * Proceedings Editor (role 8): checks the camera-ready files of every track, like an
 * administrator does, and nothing else. Approves them or sends them back before the
 * author pays.
 *
 * camera_ready_all_tracks lets a role see every track on the Camera-Ready Check screen
 * without being a track chair; camera_ready_approve lets it act there.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['admin_dashboard', 'profile', 'profile_edit', 'camera_ready_approve', 'camera_ready_all_tracks'];

    public function up(): void
    {
        $allTracks = Permission::updateOrCreate(['title' => 'camera_ready_all_tracks'], ['title' => 'camera_ready_all_tracks']);

        // Admins see every track already; listed for consistency with the seeder.
        Role::find(2)?->permissions()->syncWithoutDetaching([$allTracks->id]);

        $role = Role::withTrashed()->find(8) ?? new Role();
        $role->forceFill(['id' => 8, 'title' => 'Proceedings Editor', 'deleted_at' => null])->save();
        $role->permissions()->sync(Permission::whereIn('title', self::PERMISSIONS)->pluck('id'));
    }

    public function down(): void
    {
        if ($role = Role::find(8)) {
            $role->permissions()->detach();
            $role->users()->detach();
            $role->forceDelete();
        }

        if ($permission = Permission::where('title', 'camera_ready_all_tracks')->first()) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
