<?php

namespace Database\Seeders;

use App\Models\ConferenceMessageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ConferenceMessageCategorySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('conference_message_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $categories = [
            ['name' => 'Chief Patron & Patron', 'sort_order' => 1],
            ['name' => 'Organizing Chair',       'sort_order' => 2],
            ['name' => 'General Chair',          'sort_order' => 3],
            ['name' => 'TPC Chair',              'sort_order' => 4],
            ['name' => 'Chief Guest',            'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            ConferenceMessageCategory::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'sort_order' => $cat['sort_order'],
                'is_active'  => true,
            ]);
        }
    }
}