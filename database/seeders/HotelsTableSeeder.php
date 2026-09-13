<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accommodation and hotels for ICMRIA 2027 delegates.
 * Source: Requirement document, section 1 "Accommodation & Transportation Info".
 */
class HotelsTableSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('event_hotel')) {
            DB::table('event_hotel')->truncate();
        }
        DB::table('hotels')->truncate();
        Schema::enableForeignKeyConstraints();

        $hotels = [
            [
                'name'        => 'DIU International Guest House & Dormitory',
                'address'     => 'Daffodil Smart City, Birulia, Savar, Dhaka-1216',
                'description' => 'On-campus modern accommodation at Daffodil Smart City, Birulia. Ideal for international and national conference delegates, keynote speakers, and attendees.',
                'rating'      => 5,
            ],
            [
                'name'        => 'BRAC CDM Savar',
                'address'     => 'Khagan, Birulia, Savar, Dhaka',
                'description' => 'Located at Khagan, Birulia, Savar — approximately 2.5 km from Daffodil Smart City. Premium residential conference centre with modern amenities and peaceful green campus.',
                'rating'      => 4,
            ],
            [
                'name'        => 'Uttara / Airport Partner Hotels',
                'address'     => 'Sector 1 & 3, Uttara Model Town, Dhaka',
                'description' => 'Conveniently accessible within 20 minutes from the conference venue via the Birulia-Uttara bridge corridor, with regular conference shuttle services.',
                'rating'      => 4,
            ],
        ];

        foreach ($hotels as $key => $hotelData) {
            $photo_id = $key + 1;
            $hotel = Hotel::create($hotelData);
            $mediaPath = storage_path() . "/seeders/hotels/{$photo_id}.jpg";
            if (file_exists($mediaPath)) {
                $hotel->addMedia($mediaPath)->preservingOriginal()->toMediaCollection('photo');
            }
        }
    }
}