<?php

namespace Database\Seeders;

use App\Models\SpeakerType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Speaker categories used by the public "Speakers" page & admin management
 * (requirement document, section 7): Keynote Speakers, Invited Speakers,
 * Plenary Speakers, and Session Chairs & Organizations.
 */
class SpeakerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['title' => 'Keynote Speaker', 'slug' => 'keynote-speaker', 'publication_status' => 1],
            ['title' => 'Invited Speaker', 'slug' => 'invited-speaker', 'publication_status' => 1],
            ['title' => 'Plenary Speaker', 'slug' => 'plenary-speaker', 'publication_status' => 1],
            ['title' => 'Session Chair & Organization', 'slug' => 'session-chair', 'publication_status' => 1],
        ];

        foreach ($types as $item) {
            SpeakerType::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
