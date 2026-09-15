<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'registration_type')) {
                $table->string('registration_type')->default('early_bird')->nullable()->after('price');
            }
            if (!Schema::hasColumn('prices', 'currency')) {
                $table->string('currency', 10)->default('BDT')->nullable()->after('registration_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            if (Schema::hasColumn('prices', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('prices', 'registration_type')) {
                $table->dropColumn('registration_type');
            }
        });
    }
};