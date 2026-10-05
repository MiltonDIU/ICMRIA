<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The organisers' answers of 2026-10-05.
 *
 * paper_authors.is_attending: the registration fee is charged only for the authors who
 * will attend, each at their own tier, instead of for every author on the paper. Existing
 * papers keep their presenting author as the one attending (the first author where none
 * is marked), since every paper needs at least one registered author.
 *
 * paper_camera_ready.revision_*: a revised manuscript (Accept with Minor Revisions) is
 * checked by the paper's Track Chair or Sub-Track Chair, or the TPC Chair, before the
 * camera-ready version counts. Who may check is the revision_review permission.
 *
 * Settings: email_all_authors decides whether paper emails go to every author or only
 * the corresponding author; early_registration_start_date opens the early-bird window
 * that early_registration_last_date already closes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('paper_authors', 'is_attending')) {
            Schema::table('paper_authors', function (Blueprint $table) {
                $table->boolean('is_attending')->default(false)->after('is_student');
            });

            DB::table('paper_authors')->where('is_presenting_author', 1)->update(['is_attending' => 1]);

            $withoutAttendee = DB::table('paper_authors')
                ->whereNull('deleted_at')
                ->groupBy('paper_id')
                ->havingRaw('SUM(is_attending) = 0')
                ->pluck('paper_id');

            foreach ($withoutAttendee as $paperId) {
                $first = DB::table('paper_authors')->where('paper_id', $paperId)->whereNull('deleted_at')
                    ->orderBy('author_order')->orderBy('id')->value('id');
                DB::table('paper_authors')->where('id', $first)->update(['is_attending' => 1]);
            }
        }

        if (!Schema::hasColumn('paper_camera_ready', 'revision_status')) {
            Schema::table('paper_camera_ready', function (Blueprint $table) {
                $table->enum('revision_status', ['pending', 'approved', 'changes_requested'])->nullable()->after('revision_summary');
                $table->text('revision_note')->nullable()->after('revision_status');
                $table->foreignId('revision_reviewed_by')->nullable()->after('revision_note')->constrained('users')->nullOnDelete();
                $table->timestamp('revision_reviewed_at')->nullable()->after('revision_reviewed_by');
            });

            // A revision already on file is waiting for a chair.
            DB::table('paper_camera_ready')->whereNotNull('revised_path')->update(['revision_status' => 'pending']);
        }

        $permission = Permission::updateOrCreate(['title' => 'revision_review'], ['title' => 'revision_review']);

        // Admin, Track Chair, Sub-Track Chair (the co-chairs) and TPC Chair. SuperAdmin
        // passes every gate already.
        foreach ([2, 5, 6, 7] as $roleId) {
            Role::find($roleId)?->permissions()->syncWithoutDetaching([$permission->id]);
        }

        Setting::firstOrCreate(['key' => 'email_all_authors'], ['value' => 'false']);

        // Opens with registration, so the price anyone sees today does not change.
        Setting::firstOrCreate(
            ['key' => 'early_registration_start_date'],
            ['value' => Setting::where('key', 'registration_start_date')->value('value') ?: '2026-09-01 00:00:00']
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', ['email_all_authors', 'early_registration_start_date'])->delete();

        if ($permission = Permission::where('title', 'revision_review')->first()) {
            $permission->roles()->detach();
            $permission->delete();
        }

        if (Schema::hasColumn('paper_camera_ready', 'revision_status')) {
            Schema::table('paper_camera_ready', function (Blueprint $table) {
                $table->dropConstrainedForeignId('revision_reviewed_by');
                $table->dropColumn(['revision_status', 'revision_note', 'revision_reviewed_at']);
            });
        }

        if (Schema::hasColumn('paper_authors', 'is_attending')) {
            Schema::table('paper_authors', function (Blueprint $table) {
                $table->dropColumn('is_attending');
            });
        }
    }
};
