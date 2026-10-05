<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row each time a paper moves along the chain (abstract, manuscript, acceptance,
 * revision, camera-ready, copyright, payment, confirmation): who did it and when, for
 * the organisers' reports. Rows are only ever added, never edited.
 *
 * reference carries an outside identifier where there is one (a gateway transaction),
 * so a payment confirmed twice by the gateway is still recorded once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_progress_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->string('step', 40);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('reference', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['paper_id', 'created_at']);
            $table->index(['step', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_progress_events');
    }
};
