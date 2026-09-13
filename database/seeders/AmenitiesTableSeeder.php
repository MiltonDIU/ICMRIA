<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

/**
 * Conference participant amenities / benefits for ICMRIA 2027.
 * Source: Requirement document, section 5 "Author Guidelines, Registration & Costs".
 */
class AmenitiesTableSeeder extends Seeder
{
    public function run()
    {
        // Remove old dummy amenities
        Amenity::whereIn('name', [
            'Regular Seating',
            'Coffee Break',
            'Custom Badge',
            'Community Access',
            'Workshop Access',
            'After Party',
        ])->delete();

        $amenities = [
            ['name' => 'Access to all Keynote & Technical Sessions'],
            ['name' => 'Official Presentation / Participation Certificate'],
            ['name' => 'Paper Presentation Slot (Onsite / Online)'],
            ['name' => 'Conference Kit, Badge & Program Book'],
            ['name' => 'Networking Lunch & Refreshments'],
            ['name' => 'Consideration for Scopus-indexed Q2 Journal Publication'],
            ['name' => 'Access to Co-located Workshops & Exhibitions'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']]);
        }
    }
}