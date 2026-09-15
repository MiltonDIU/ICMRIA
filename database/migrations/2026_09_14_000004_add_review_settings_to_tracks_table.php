<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-track overrides of the conference-wide review workload settings.
 *
 * Tracks differ in how many reviewers they can draw on — Track 1 (AI) has far more
 * than Track 8 (Agriculture) — so a single fixed figure would stall the smaller
 * ones. A null here means "use the value from the settings table".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            if (!Schema::hasColumn('tracks', 'reviewers_per_paper')) {
                $table->unsignedTinyInteger('reviewers_per_paper')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tracks', 'max_papers_per_reviewer')) {
                $table->unsignedSmallInteger('max_papers_per_reviewer')->nullable()->after('reviewers_per_paper');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            foreach (['reviewers_per_paper', 'max_papers_per_reviewer'] as $column) {
                if (Schema::hasColumn('tracks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
