<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Camera-ready submission and registration (requirement document, Phase 6).
 *
 * paper_camera_ready holds what an accepted paper needs before it goes into the
 * proceedings: the final manuscript (with author names, unlike the anonymised one),
 * the signed copyright transfer form, and the administrator's confirmation. Once
 * confirmed, a paper can be placed in a programme session; that placement lives here
 * too, since only confirmed papers are scheduled.
 *
 * paper_payment_proofs records a registration fee paid outside the online gateway: a
 * bank or mobile transfer the author reports with a transaction reference and proof,
 * for an administrator to verify. A paper can have several, since a rejected proof is
 * followed by a corrected one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('paper_camera_ready')) {
            Schema::create('paper_camera_ready', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->string('camera_ready_path')->nullable();
                $table->string('camera_ready_name')->nullable();
                $table->timestamp('camera_ready_uploaded_at')->nullable();
                $table->text('revision_summary')->nullable();
                $table->string('copyright_path')->nullable();
                $table->string('copyright_name')->nullable();
                $table->timestamp('copyright_uploaded_at')->nullable();
                $table->enum('status', ['submitted', 'changes_requested', 'confirmed'])->default('submitted');
                $table->text('admin_note')->nullable();
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
                $table->unsignedSmallInteger('presentation_order')->nullable();
                $table->timestamps();

                $table->unique('paper_id');
            });
        }

        if (!Schema::hasTable('paper_payment_proofs')) {
            Schema::create('paper_payment_proofs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('method', 40);
                $table->string('transaction_id', 100);
                $table->decimal('amount', 12, 2);
                $table->string('currency', 8);
                $table->date('paid_on');
                $table->string('proof_path');
                $table->string('proof_name');
                $table->enum('status', ['submitted', 'verified', 'rejected'])->default('submitted');
                $table->text('review_note')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('transaction_id');
            });
        }

        // The registration deadline is the natural default: a camera-ready paper whose
        // author has not registered by then cannot be presented anyway.
        Setting::firstOrCreate(['key' => 'camera_ready_deadline'], ['value' => '2026-12-26 23:59:00']);
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_payment_proofs');
        Schema::dropIfExists('paper_camera_ready');
        Setting::where('key', 'camera_ready_deadline')->delete();
    }
};
