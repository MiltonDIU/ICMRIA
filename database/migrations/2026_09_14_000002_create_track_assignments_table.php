<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who chairs or reviews where.
 *
 * A role says what a person may do; this says where. Both are needed: several
 * people in the requirement document hold the same post in more than one place
 * (Prof. Dr. Liza Sharmin chairs Track 5 and Track 7; Dr. Kazi A. S. M. Nurul Huda
 * chairs a sub-track under both Track 2 and Track 4), so a single chair_id column
 * on tracks could never express it.
 *
 * A null sub_track_id means the whole track — that is the overall Track Chair.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_track_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('role', ['chair', 'reviewer'])->default('chair');
            $table->timestamps();

            $table->index(['track_id', 'sub_track_id']);
            $table->unique(['user_id', 'track_id', 'sub_track_id', 'role'], 'track_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_assignments');
    }
};
