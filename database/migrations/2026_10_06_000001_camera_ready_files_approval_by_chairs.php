<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Organisers, 2026-10-06: the camera-ready files are checked before the fee, not after.
 *
 *   camera-ready + copyright uploaded -> the paper's Track Chair or Sub-Track Chair
 *   approves the files (or sends them back) -> the author pays -> the paper is confirmed
 *   for the proceedings automatically.
 *
 * paper_camera_ready.status gains 'approved' (files approved, fee due), and
 * files_reviewed_by / files_reviewed_at record who checked them. Who may check is the
 * new camera_ready_approve permission: Admin, Track Chair and Sub-Track Chair.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE paper_camera_ready MODIFY status ENUM('submitted', 'changes_requested', 'approved', 'confirmed') NOT NULL DEFAULT 'submitted'");

        if (!Schema::hasColumn('paper_camera_ready', 'files_reviewed_by')) {
            Schema::table('paper_camera_ready', function (Blueprint $table) {
                $table->foreignId('files_reviewed_by')->nullable()->after('admin_note')->constrained('users')->nullOnDelete();
                $table->timestamp('files_reviewed_at')->nullable()->after('files_reviewed_by');
            });
        }

        $permission = Permission::updateOrCreate(['title' => 'camera_ready_approve'], ['title' => 'camera_ready_approve']);

        // Admin, Track Chair, Sub-Track Chair. SuperAdmin passes every gate already.
        foreach ([2, 5, 6] as $roleId) {
            Role::find($roleId)?->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    public function down(): void
    {
        if ($permission = Permission::where('title', 'camera_ready_approve')->first()) {
            $permission->roles()->detach();
            $permission->delete();
        }

        if (Schema::hasColumn('paper_camera_ready', 'files_reviewed_by')) {
            Schema::table('paper_camera_ready', function (Blueprint $table) {
                $table->dropConstrainedForeignId('files_reviewed_by');
                $table->dropColumn('files_reviewed_at');
            });
        }

        DB::table('paper_camera_ready')->where('status', 'approved')->update(['status' => 'submitted']);
        DB::statement("ALTER TABLE paper_camera_ready MODIFY status ENUM('submitted', 'changes_requested', 'confirmed') NOT NULL DEFAULT 'submitted'");
    }
};
