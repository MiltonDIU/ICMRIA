<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ICMRIA 2027 Conference Registration Pricing.
 * Source: Requirement document, section 5 "Registration Info and Cost Structure".
 *
 * One row per category, carrying both stages. A profile stores this row's id, so
 * when early-bird closes the amount due moves to regular_price without the profile
 * ever having to point at a different row.
 *
 * The requirement document prints SAARC as "Early US$ 100 / Late US$ 75". That row
 * is wrong on its face (late cheaper than early); the organisers confirmed 75/175.
 *
 * `category` is the slug PricingService matches on, never the display name.
 */
class PricesTableSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('amenity_price')) {
            DB::table('amenity_price')->truncate();
        }
        if (Schema::hasTable('event_price')) {
            DB::table('event_price')->truncate();
        }
        DB::table('prices')->truncate();
        Schema::enableForeignKeyConstraints();

        $prices = [
            [
                'name'             => 'Student Presenter / Participant',
                'category'         => 'student',
                'early_bird_price' => 4000,
                'regular_price'    => 5000,
                'currency'         => 'BDT',
            ],
            [
                'name'             => 'Academic Presenter / Participant',
                'category'         => 'academic',
                'early_bird_price' => 6000,
                'regular_price'    => 7000,
                'currency'         => 'BDT',
            ],
            [
                'name'             => 'Industry / R&D Presenter / Participant',
                'category'         => 'industry',
                'early_bird_price' => 6500,
                'regular_price'    => 7500,
                'currency'         => 'BDT',
            ],
            [
                'name'             => 'SAARC Presenter / Participant',
                'category'         => 'saarc',
                'early_bird_price' => 75,
                'regular_price'    => 175,
                'currency'         => 'USD',
            ],
            [
                'name'             => 'International Presenter / Participant',
                'category'         => 'international',
                'early_bird_price' => 150,
                'regular_price'    => 175,
                'currency'         => 'USD',
            ],
        ];

        foreach ($prices as $item) {
            Price::create($item);
        }
    }
}
