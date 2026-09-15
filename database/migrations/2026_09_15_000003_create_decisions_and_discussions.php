<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Decision making and notification (requirement document, Phase 5).
 *
 * paper_decisions holds the chair's formal decision and its passage through the TPC
 * Chair: pending_approval until approved, or returned to the chair with a note. One row
 * per paper; a revised decision overwrites it. notified_at is set when the author is
 * sent the result, which is a separate, deliberate step after approval.
 *
 * paper_discussion_messages is the internal discussion a chair opens when the
 * evaluations disagree. papers.discussion_opened_at marks that it was opened, since a
 * discussion can be open before anyone has written in it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paper_decisions')) {
            Schema::create('paper_decisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->enum('decision', ['accept', 'minor_revisions', 'reject']);
                $table->enum('status', ['pending_approval', 'approved', 'returned'])->default('pending_approval');
                $table->text('note_to_authors')->nullable();
                $table->text('note_to_tpc')->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('return_note')->nullable();
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->unique('paper_id');
                $table->index(['status', 'notified_at']);
            });
        }

        if (!Schema::hasTable('paper_discussion_messages')) {
            Schema::create('paper_discussion_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();

                $table->index(['paper_id', 'created_at']);
            });
        }

        Schema::table('papers', function (Blueprint $table) {
            if (!Schema::hasColumn('papers', 'discussion_opened_at')) {
                $table->timestamp('discussion_opened_at')->nullable();
            }
            if (!Schema::hasColumn('papers', 'discussion_opened_by')) {
                $table->foreignId('discussion_opened_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            if (Schema::hasColumn('papers', 'discussion_opened_by')) {
                $table->dropConstrainedForeignId('discussion_opened_by');
            }
            if (Schema::hasColumn('papers', 'discussion_opened_at')) {
                $table->dropColumn('discussion_opened_at');
            }
        });

        Schema::dropIfExists('paper_discussion_messages');
        Schema::dropIfExists('paper_decisions');
    }
};
