<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "Messages" section of the conference website
     * (requirement document, section 1 navigation + "Messages" menu):
     *   - Messages from Chief Guest (Opening & Closing)
     *   - Messages from Chief Patron & Patrons
     *   - Messages from General Chair, Organizing Chair, and TPC Chair
     *
     * `category` and `variant` are free-text so the organising committee can
     * add / rename message groups from the admin panel.
     */
    public function up(): void
    {
        Schema::create('conference_messages', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('General')->comment('e.g. Chief Guest, Chief Patron & Patron, General Chair');
            $table->string('variant')->nullable()->comment('e.g. Opening, Closing');
            $table->string('person_name');
            $table->string('designation')->nullable();
            $table->string('affiliation')->nullable();
            $table->longText('message');
            $table->string('profile_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conference_messages');
    }
};
