<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refinements across the submission portal.
 *
 * - users.research_keywords: the 3-5 research keywords a reviewer gives about
 *   themselves, matched against paper keywords alongside what chairs record per track.
 * - paper_camera_ready.revised_*: the revised manuscript a paper accepted with minor
 *   revisions has to supply before its camera-ready version, with its own deadline.
 * - Settings for the manuscript page limit, the reviewer keyword count and the
 *   revision deadline, so the organisers can change them without code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'research_keywords')) {
                $table->json('research_keywords')->nullable();
            }
        });

        Schema::table('paper_camera_ready', function (Blueprint $table) {
            if (!Schema::hasColumn('paper_camera_ready', 'revised_path')) {
                $table->string('revised_path')->nullable()->after('camera_ready_uploaded_at');
                $table->string('revised_name')->nullable()->after('revised_path');
                $table->timestamp('revised_uploaded_at')->nullable()->after('revised_name');
            }
        });

        foreach ([
            'manuscript_min_pages' => '6',
            'manuscript_max_pages' => '8',
            'reviewer_keywords_min' => '3',
            'reviewer_keywords_max' => '5',
            'revision_deadline' => '2026-12-10 23:59:00',
        ] as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', ['manuscript_min_pages', 'manuscript_max_pages', 'reviewer_keywords_min',
                                 'reviewer_keywords_max', 'revision_deadline'])->delete();

        Schema::table('paper_camera_ready', function (Blueprint $table) {
            foreach (['revised_uploaded_at', 'revised_name', 'revised_path'] as $column) {
                if (Schema::hasColumn('paper_camera_ready', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'research_keywords')) {
                $table->dropColumn('research_keywords');
            }
        });
    }
};
