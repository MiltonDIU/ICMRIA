<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Price;
use Illuminate\Database\Seeder;

/**
 * Link conference registration pricing to participant amenities.
 */
class AmenityPriceTableSeeder extends Seeder
{
    public function run()
    {
        $allAmenities = Amenity::pluck('id')->toArray();

        $prices = Price::all();
        foreach ($prices as $price) {
            $price->amenities()->sync($allAmenities);
        }
    }
}