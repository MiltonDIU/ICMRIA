<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Profiles created for reviewers and chairs have no country to record: they are not
 * registering as delegates and never pay a fee, so the field the registration form
 * insists on does not apply to them. Registration still requires it through its own
 * validation, which is where the rule belongs.
 *
 * Raw SQL because changing a column with doctrine/dbal is not available here.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `profiles` MODIFY `country_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE `profiles` SET `country_id` = (SELECT MIN(id) FROM countries) WHERE `country_id` IS NULL');
        DB::statement('ALTER TABLE `profiles` MODIFY `country_id` BIGINT UNSIGNED NOT NULL');
    }
};
