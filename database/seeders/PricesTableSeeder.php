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
 * Categories:
 *   - International Presenter/Participant: Early US$ 150 / Late US$ 175
 *   - SAARC Presenter/Participant:         Early US$ 100 / Late US$ 75
 *   - Student Presenter/Participant:       Early BDT 4,000 / Late BDT 5,000
 *   - Industry/R&D Presenter/Participant:  Early BDT 6,500 / Late BDT 7,500
 *   - Academic Presenter/Participant:      Early BDT 6,000 / Late BDT 7,000
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
            // ==========================================
            // Early Bird Registration
            // ==========================================
            [
                'name'              => 'Student Presenter / Participant',
                'price'             => 4000,
                'currency'          => 'BDT',
                'registration_type' => 'early_bird',
            ],
            [
                'name'              => 'Academic Presenter / Participant',
                'price'             => 6000,
                'currency'          => 'BDT',
                'registration_type' => 'early_bird',
            ],
            [
                'name'              => 'Industry / R&D Presenter / Participant',
                'price'             => 6500,
                'currency'          => 'BDT',
                'registration_type' => 'early_bird',
            ],
            [
                'name'              => 'SAARC Presenter / Participant',
                'price'             => 75,
                'currency'          => 'USD',
                'registration_type' => 'early_bird',
            ],
            [
                'name'              => 'International Presenter / Participant',
                'price'             => 150,
                'currency'          => 'USD',
                'registration_type' => 'early_bird',
            ],

            // ==========================================
            // Regular / Late Registration
            // ==========================================
            [
                'name'              => 'Student Presenter / Participant',
                'price'             => 5000,
                'currency'          => 'BDT',
                'registration_type' => 'regular',
            ],
            [
                'name'              => 'Academic Presenter / Participant',
                'price'             => 7000,
                'currency'          => 'BDT',
                'registration_type' => 'regular',
            ],
            [
                'name'              => 'Industry / R&D Presenter / Participant',
                'price'             => 7500,
                'currency'          => 'BDT',
                'registration_type' => 'regular',
            ],
            [
                'name'              => 'SAARC Presenter / Participant',
                'price'             => 175,
                'currency'          => 'USD',
                'registration_type' => 'regular',
            ],
            [
                'name'              => 'International Presenter / Participant',
                'price'             => 175,
                'currency'          => 'USD',
                'registration_type' => 'regular',
            ],
        ];

        foreach ($prices as $item) {
            Price::create($item);
        }
    }
}