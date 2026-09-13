<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conference_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('conference_message_category_id')->nullable()->after('id');

            $table->foreign('conference_message_category_id')
                  ->references('id')
                  ->on('conference_message_categories')
                  ->onDelete('set null');

            if (Schema::hasColumn('conference_messages', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conference_messages', function (Blueprint $table) {
            $table->dropForeign(['conference_message_category_id']);
            $table->dropColumn('conference_message_category_id');
            $table->string('category')->nullable();
        });
    }
};