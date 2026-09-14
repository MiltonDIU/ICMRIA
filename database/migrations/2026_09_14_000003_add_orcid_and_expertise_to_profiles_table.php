<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ORCID is asked for at registration (requirement document, Phase 1).
 * reviewer_expertise feeds the keyword matching that automatic reviewer
 * assignment uses (Phase 3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'orcid_id')) {
                $table->string('orcid_id', 19)->nullable()->after('institution');
            }
            if (!Schema::hasColumn('profiles', 'reviewer_expertise')) {
                $table->text('reviewer_expertise')->nullable()->after('orcid_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            foreach (['orcid_id', 'reviewer_expertise'] as $column) {
                if (Schema::hasColumn('profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
