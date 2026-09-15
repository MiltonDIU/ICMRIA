<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The reviewer's evaluation form (requirement document, Phase 4).
 *
 * One evaluation per assignment. Every field is nullable because a reviewer can save a
 * draft part-way through; submitted_at is what makes it final, and the controller
 * insists on the full form before setting it.
 *
 * manuscript_version records which upload the reviewer judged. The author may replace
 * the file while the window is open, and a chair weighing two reviews needs to know
 * whether both reviewers read the same one.
 *
 * decline_reason goes on the assignment: a reviewer who turns a paper down is asked
 * why, so the chair can find someone better suited.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paper_evaluations')) {
            Schema::create('paper_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('paper_reviewer_assignments')->cascadeOnDelete();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('originality')->nullable();
                $table->unsignedTinyInteger('soundness')->nullable();
                $table->unsignedTinyInteger('relevance')->nullable();
                $table->enum('recommendation', ['strong_accept', 'accept', 'borderline', 'reject', 'strong_reject'])->nullable();
                $table->text('feedback_for_authors')->nullable();
                $table->text('confidential_comments')->nullable();
                $table->unsignedInteger('manuscript_version')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique('assignment_id');
                $table->index(['paper_id', 'submitted_at']);
            });
        }

        Schema::table('paper_reviewer_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('paper_reviewer_assignments', 'decline_reason')) {
                $table->string('decline_reason', 500)->nullable()->after('responded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_evaluations');

        Schema::table('paper_reviewer_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('paper_reviewer_assignments', 'decline_reason')) {
                $table->dropColumn('decline_reason');
            }
        });
    }
};
