<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds track-wise grouping + affiliation details to speakers so the public
     * "Speakers" page can render Keynote / Invited speakers track-wise
     * (requirement document, section 7 "Speakers & Co-located Events").
     */
    public function up(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (!Schema::hasColumn('speakers', 'track_id')) {
                $table->foreignId('track_id')->nullable()->after('speaker_type_id')->constrained('tracks')->nullOnDelete();
            }
            if (!Schema::hasColumn('speakers', 'focus_area')) {
                $table->string('focus_area')->nullable()->after('track_id');
            }
            if (!Schema::hasColumn('speakers', 'affiliation')) {
                $table->string('affiliation')->nullable()->after('focus_area');
            }
            if (!Schema::hasColumn('speakers', 'country')) {
                $table->string('country')->nullable()->after('affiliation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (Schema::hasColumn('speakers', 'track_id')) {
                $table->dropForeign(['track_id']);
                $table->dropColumn('track_id');
            }
            $table->dropColumn(array_values(array_filter(
                ['focus_area', 'affiliation', 'country'],
                fn ($c) => Schema::hasColumn('speakers', $c)
            )));
        });
    }
};
