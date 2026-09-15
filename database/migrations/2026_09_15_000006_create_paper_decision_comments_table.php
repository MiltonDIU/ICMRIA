<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Comments between the chairs and the TPC Chair on a paper's decision, kept as history.
 *
 * A decision used to carry one note from the chair (note_to_tpc) and one from the TPC
 * Chair (return_note), each overwritten whenever the decision went back and forth, so
 * the earlier exchange was lost. Each comment is now its own row, tagged with who wrote
 * it (chair or TPC Chair), what it came with (a decision, a return, or on its own) and
 * the round it belongs to. A round starts each time a returned decision is sent again.
 *
 * Existing notes are carried over as round 1 comments before the old columns go.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paper_decision_comments')) {
            Schema::create('paper_decision_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->foreignId('paper_decision_id')->nullable()->constrained('paper_decisions')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('author_role', ['chair', 'tpc']);
                $table->enum('kind', ['comment', 'decision', 'returned', 'approved'])->default('comment');
                $table->unsignedSmallInteger('round')->default(1);
                $table->text('body');
                $table->timestamps();

                $table->index(['paper_id', 'created_at']);
            });
        }

        Schema::table('paper_decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('paper_decisions', 'round')) {
                $table->unsignedSmallInteger('round')->default(1)->after('status');
            }
        });

        if (Schema::hasColumn('paper_decisions', 'note_to_tpc')) {
            foreach (DB::table('paper_decisions')->get() as $decision) {
                if (filled($decision->note_to_tpc)) {
                    DB::table('paper_decision_comments')->insert([
                        'paper_id' => $decision->paper_id, 'paper_decision_id' => $decision->id,
                        'user_id' => $decision->decided_by, 'author_role' => 'chair', 'kind' => 'decision',
                        'round' => 1, 'body' => $decision->note_to_tpc,
                        'created_at' => $decision->decided_at ?? now(), 'updated_at' => now(),
                    ]);
                }
                if (filled($decision->return_note)) {
                    DB::table('paper_decision_comments')->insert([
                        'paper_id' => $decision->paper_id, 'paper_decision_id' => $decision->id,
                        'user_id' => null, 'author_role' => 'tpc', 'kind' => 'returned',
                        'round' => 1, 'body' => $decision->return_note,
                        'created_at' => $decision->updated_at ?? now(), 'updated_at' => now(),
                    ]);
                }
            }

            Schema::table('paper_decisions', function (Blueprint $table) {
                $table->dropColumn(['note_to_tpc', 'return_note']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('paper_decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('paper_decisions', 'note_to_tpc')) {
                $table->text('note_to_tpc')->nullable();
            }
            if (!Schema::hasColumn('paper_decisions', 'return_note')) {
                $table->text('return_note')->nullable();
            }
            if (Schema::hasColumn('paper_decisions', 'round')) {
                $table->dropColumn('round');
            }
        });

        Schema::dropIfExists('paper_decision_comments');
    }
};
