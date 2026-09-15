<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every manuscript upload, kept as a record.
 *
 * papers.manuscript_path still points at the current file, so nothing that reads a
 * manuscript has to change. This table answers the questions that one column cannot:
 *
 *   - when was a file first submitted, as opposed to last replaced? A deadline
 *     dispute turns on the first upload, which a single timestamp loses.
 *   - which version did a reviewer actually read? Once review begins, an author
 *     replacing the file silently changes what the review refers to.
 *   - did the author confirm the file was anonymised, and when?
 *
 * The superseded files are kept rather than deleted. Discarding work an author
 * submitted cannot be undone, and storage is the cheaper side of that trade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_manuscript_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->unsignedSmallInteger('version');
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('anonymity_confirmed')->default(false);
            $table->timestamps();

            $table->unique(['paper_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_manuscript_versions');
    }
};
