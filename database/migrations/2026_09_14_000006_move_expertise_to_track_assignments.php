<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expertise belongs to the assignment, not to the person.
 *
 * Several chairs hold two posts in different fields — Dr. Kazi A. S. M. Nurul Huda
 * chairs Environmental Risk Assessment under Track 2 and Civil Engineering under
 * Track 4. Held on the profile, those two subjects collapse into one blob and the
 * keyword matching that drives automatic reviewer assignment cannot tell which
 * applies where. Held on the assignment, each scope keeps its own subject.
 *
 * profiles.reviewer_expertise goes with it: chairs and reviewers are users with
 * track assignments, they never register as delegates and so have no profile at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('track_assignments', 'expertise')) {
                $table->text('expertise')->nullable()->after('role');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'reviewer_expertise')) {
                $table->dropColumn('reviewer_expertise');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'reviewer_expertise')) {
                $table->text('reviewer_expertise')->nullable();
            }
        });

        Schema::table('track_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('track_assignments', 'expertise')) {
                $table->dropColumn('expertise');
            }
        });
    }
};
