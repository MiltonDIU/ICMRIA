<?php
namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GalleriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('galleries')->truncate();
        Schema::enableForeignKeyConstraints();

        $gallery = Gallery::create([
            'name' => 'Event'
        ]);
        foreach(range(1,8) as $id)
        {
            $mediaPath = storage_path()."/seeders/gallery/$id.jpg";
            if (file_exists($mediaPath)) {
                $gallery->addMedia($mediaPath)->preservingOriginal()->toMediaCollection('photos');
            }
        }
    }
}
