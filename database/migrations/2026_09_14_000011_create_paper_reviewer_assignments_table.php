<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which reviewer was given which paper (requirement document, Phase 3).
 *
 * Distinct from track_assignments: that says a reviewer is available for a
 * sub-track, this says they were handed a specific paper.
 *
 * assignment_source records how the pairing came about, because a chair reviewing
 * their own decisions later wants to know whether the system proposed it or a person
 * chose it. 'discussion' marks the extra reviewer brought in when two scores
 * disagree, which the document calls for in Phase 5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_reviewer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            // Null where the system assigned without a person pressing the button.
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('assignment_source', ['manual', 'auto', 'bid', 'discussion'])->default('manual');
            $table->enum('status', ['invited', 'accepted', 'declined', 'in_progress', 'completed'])->default('invited');
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['paper_id', 'reviewer_id']);
            $table->index(['reviewer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_reviewer_assignments');
    }
};
