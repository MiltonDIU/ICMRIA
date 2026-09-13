<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SponsorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Keeps exactly 3 confirmed sample sponsors.
     *
     * @return void
     */
    public function run()
    {
        $sponsors = [
            [
                'name' => 'Strider',
                'link' => '#'
            ],
            [
                'name' => 'Runtastic',
                'link' => '#'
            ],
            [
                'name' => 'EditShare',
                'link' => '#'
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('sponsors')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach($sponsors as $key => $sponsorData)
        {
            $photo_id = $key + 1;
            $sponsor = Sponsor::create($sponsorData);
            $mediaPath = storage_path()."/seeders/supporters/$photo_id.png";
            if (file_exists($mediaPath)) {
                $sponsor->addMedia($mediaPath)->preservingOriginal()->toMediaCollection('logo');
            }
        }
    }
}
