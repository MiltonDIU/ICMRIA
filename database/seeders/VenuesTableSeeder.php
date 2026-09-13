<?php
namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VenuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('venues')->truncate();
        Schema::enableForeignKeyConstraints();

        $venue = Venue::create([
            'name'          => 'Daffodil International University',
            'address'       => 'Daffodil Smart City, Birulia, Savar, Dhaka-1216',
            'latitude'      => '23.75484855496525',
            'longitude'     => '90.37654019499453',
            'description'   => 'ICMRIA 2027 will be held on the permanent campus of Daffodil International University at Daffodil Smart City, Birulia, Savar, Dhaka-1216. The conference runs in blended mode (onsite + online) on 9–10 January 2027.',
        ]);

        foreach(range(1,8) as $id)
        {
            $mediaPath = storage_path()."/seeders/venue-gallery/$id.jpg";
            if (file_exists($mediaPath)) {
                $venue->addMedia($mediaPath)->preservingOriginal()->toMediaCollection('photos');
            }
        }
    }
}
