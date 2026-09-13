<?php

namespace Database\Seeders;

use App\Models\Speaker;
use Illuminate\Database\Seeder;

class SpeakersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Removes legacy faker dummy speakers and seeds official Keynote & Invited speakers.
     *
     * @return void
     */
    public function run()
    {
        // Remove legacy faker dummy speakers
        Speaker::whereIn('name', [
            'Brenden Legros',
            'Hubert Hirthe',
            'Cole Emmerich',
            'Jack Christiansen',
            'Alejandrin Littel',
            'Willow Trantow',
        ])->forceDelete();

        // Delegate to official keynote & invited speakers seeder
        $this->call(KeynoteInvitedSpeakerSeeder::class);
    }
}