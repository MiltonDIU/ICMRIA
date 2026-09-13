<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Conference participant amenities / benefits for ICMRIA 2027.
 * Source: Requirement document, section 5 "Author Guidelines, Registration & Costs".
 * Trimmed to 3 core academic entitlements.
 */
class AmenitiesTableSeeder extends Seeder
{
    public function run()
    {
        $amenities = [
            ['name' => 'Access to all Keynote & Technical Sessions'],
            ['name' => 'Official Presentation / Participation Certificate'],
            ['name' => 'Consideration for Scopus-indexed Q2 Journal Publication'],
        ];

        // Delete any extra amenities not in the 3 core entitlements
        $validNames = array_column($amenities, 'name');
        Amenity::whereNotIn('name', $validNames)->delete();

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']]);
        }
    }
}
