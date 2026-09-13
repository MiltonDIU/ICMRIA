<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_category_id')->nullable()->after('speaker_id');
            $table->foreign('schedule_category_id')
                  ->references('id')
                  ->on('schedule_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['schedule_category_id']);
            $table->dropColumn('schedule_category_id');
        });
    }
};
