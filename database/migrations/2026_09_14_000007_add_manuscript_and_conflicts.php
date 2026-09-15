<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 of the requirement document: the full manuscript and the author's
 * declaration of conflicts.
 *
 * manuscript_status is kept apart from papers.status. status is the abstract gate
 * (pending / approved / rejected) that the existing screens already branch on;
 * overloading it would break them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            if (!Schema::hasColumn('papers', 'manuscript_path')) {
                $table->string('manuscript_path')->nullable()->after('keywords');
            }
            if (!Schema::hasColumn('papers', 'manuscript_original_name')) {
                $table->string('manuscript_original_name')->nullable()->after('manuscript_path');
            }
            if (!Schema::hasColumn('papers', 'manuscript_uploaded_at')) {
                $table->timestamp('manuscript_uploaded_at')->nullable()->after('manuscript_original_name');
            }
            if (!Schema::hasColumn('papers', 'manuscript_status')) {
                $table->enum('manuscript_status', ['not_submitted', 'submitted', 'revised'])
                    ->default('not_submitted')
                    ->after('manuscript_uploaded_at');
            }
        });

        Schema::create('paper_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('declared_by_user_id')->constrained('users')->cascadeOnDelete();
            // Either a named person in the system, or a free-text institution when the
            // author only knows where the conflict lies.
            $table->foreignId('conflicted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('conflicted_institution')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['paper_id', 'conflicted_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_conflicts');

        Schema::table('papers', function (Blueprint $table) {
            foreach (['manuscript_path', 'manuscript_original_name', 'manuscript_uploaded_at', 'manuscript_status'] as $column) {
                if (Schema::hasColumn('papers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
