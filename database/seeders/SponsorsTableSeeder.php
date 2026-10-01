<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sponsor & Partner Seeder
 * Official confirmed logos will be provided by the conference organizing committee.
 * Kept empty / TBA until official logos are released.
 */
class SponsorsTableSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('sponsors')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
