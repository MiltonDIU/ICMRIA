<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moves fee lookup off the Setting key-value pairs and onto the prices table.
 *
 * Settings were keyed by currency (usd_earlybird_price, bdt_regular_price, ...),
 * which cannot express the Industry/R&D tier at all.
 *
 * One row now holds both stages. Previously each tier had two rows, one per
 * registration_type, so a profile pointing at a price would have had to be
 * repointed at a different id the moment early-bird closed. With both amounts on
 * a single row, the profile keeps its price_id for life and only the amount due
 * is recalculated when the stage turns over.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'category')) {
                $table->string('category', 40)->nullable()->after('name')->index();
            }
            if (!Schema::hasColumn('prices', 'early_bird_price')) {
                $table->decimal('early_bird_price', 15, 2)->default(0)->after('category');
            }
            if (!Schema::hasColumn('prices', 'regular_price')) {
                $table->decimal('regular_price', 15, 2)->default(0)->after('early_bird_price');
            }
        });

        Schema::table('prices', function (Blueprint $table) {
            if (Schema::hasColumn('prices', 'registration_type')) {
                $table->dropColumn('registration_type');
            }
            if (Schema::hasColumn('prices', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'price_id')) {
                $table->foreignId('price_id')->nullable()->after('institution')
                    ->constrained('prices')->nullOnDelete();
            }
        });

        Schema::table('paper_authors', function (Blueprint $table) {
            if (!Schema::hasColumn('paper_authors', 'price_id')) {
                $table->foreignId('price_id')->nullable()->after('is_student')
                    ->constrained('prices')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('paper_authors', function (Blueprint $table) {
            if (Schema::hasColumn('paper_authors', 'price_id')) {
                $table->dropConstrainedForeignId('price_id');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'price_id')) {
                $table->dropConstrainedForeignId('price_id');
            }
        });

        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'price')) {
                $table->decimal('price', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('prices', 'registration_type')) {
                $table->string('registration_type')->default('early_bird')->nullable();
            }
            foreach (['category', 'early_bird_price', 'regular_price'] as $column) {
                if (Schema::hasColumn('prices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
