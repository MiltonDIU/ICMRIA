<?php

namespace Database\Seeders;

use App\Models\Speaker;
use App\Models\SpeakerType;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Keynote & Invited Speakers Seeder
 * Per organizing committee instructions:
 * The 7 speaker slots from the conference document are created with photograph
 * placeholders and marked as 'TBA' (To Be Announced).
 */
class KeynoteInvitedSpeakerSeeder extends Seeder
{
    public function run(): void
    {
        $keynote = SpeakerType::firstOrCreate(['title' => 'Keynote Speaker']);
        $invited = SpeakerType::firstOrCreate(['title' => 'Invited Speaker']);
        $plenary = SpeakerType::firstOrCreate(['title' => 'Plenary Speaker']);

        $trackMap = Track::pluck('id', 'name')->toArray();
        $t = function (int $num) use ($trackMap) {
            foreach ($trackMap as $name => $id) {
                if (str_starts_with($name, "Track {$num}:")) {
                    return $id;
                }
            }
            return null;
        };

        $speakers = [
            // =========================================================================
            // 1. Keynote Speakers (TBA)
            // =========================================================================
            [
                'name'         => 'Keynote Speaker (TBA)',
                'slug'         => 'keynote-speaker-health-sciences-tba',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biostatistics & Bioinformatics',
                'affiliation'  => 'International Academic Partner (TBA)',
                'country'      => 'International',
                'description'  => 'Distinguished keynote address on state-of-the-art biostatistical methodologies, digital health analytics, and multi-omics bioinformatics.',
                'full_desc'    => 'Official speaker announcement and comprehensive bio will be published once confirmed by the organizing committee.',
                'serial'       => 1,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Keynote Speaker (TBA)',
                'slug'         => 'keynote-speaker-ai-smart-systems-tba',
                'type_id'      => $keynote->id,
                'track_id'     => $t(1),
                'focus'        => 'AI, Data Science & Smart Systems',
                'affiliation'  => 'International Research University (TBA)',
                'country'      => 'International',
                'description'  => 'Keynote presentation exploring foundational AI models, safe autonomous architectures, and intelligent software engineering.',
                'full_desc'    => 'Official speaker announcement and comprehensive bio will be published once confirmed by the organizing committee.',
                'serial'       => 2,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Keynote Speaker (TBA)',
                'slug'         => 'keynote-speaker-biotech-digital-health-tba',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biotech & Digital Health',
                'affiliation'  => 'Distinguished Global Scholar (TBA)',
                'country'      => 'International',
                'description'  => 'Pioneering keynote on computational genomics, medical artificial intelligence, and health informatics applications.',
                'full_desc'    => 'Official speaker announcement and comprehensive bio will be published once confirmed by the organizing committee.',
                'serial'       => 3,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Keynote Speaker (TBA)',
                'slug'         => 'keynote-speaker-sustainable-computing-tba',
                'type_id'      => $keynote->id,
                'track_id'     => $t(2),
                'focus'        => 'Sustainable Development & Intelligent Computing',
                'affiliation'  => 'Eminent Research Scholar (TBA)',
                'country'      => 'International',
                'description'  => 'Keynote address covering computational intelligence, pattern analysis, and distributed smart sensor networks for sustainable development.',
                'full_desc'    => 'Official speaker announcement and comprehensive bio will be published once confirmed by the organizing committee.',
                'serial'       => 4,
                'show_home'    => 1,
            ],

            // =========================================================================
            // 2. Invited Plenary & Technical Talks (TBA)
            // =========================================================================
            [
                'name'         => 'Plenary Speaker (TBA)',
                'slug'         => 'plenary-speaker-industry-4-0-tba',
                'type_id'      => $plenary->id,
                'track_id'     => null,
                'focus'        => 'Industry 4.0, Tech Transfer & Future Work',
                'affiliation'  => 'Leading Tech Enterprise / R&D Leader (TBA)',
                'country'      => 'International',
                'description'  => 'Plenary address bridging academic research and global tech commercialization pipelines.',
                'full_desc'    => 'Official speaker announcement and session details will be published once confirmed by the organizing committee.',
                'serial'       => 10,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Invited Speaker (TBA)',
                'slug'         => 'invited-speaker-machine-learning-iot-tba',
                'type_id'      => $invited->id,
                'track_id'     => $t(1),
                'focus'        => 'Machine Learning, IoT & Edge Computing',
                'affiliation'  => 'Distinguished International Scholar (TBA)',
                'country'      => 'International',
                'description'  => 'Invited technical talk on empirical methods, smart information systems, and Internet of Things architectures.',
                'full_desc'    => 'Official speaker announcement and session details will be published once confirmed by the organizing committee.',
                'serial'       => 11,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Invited Speaker (TBA)',
                'slug'         => 'invited-speaker-green-tech-renewable-energy-tba',
                'type_id'      => $invited->id,
                'track_id'     => $t(2),
                'focus'        => 'Green Tech & Renewable Energy Systems',
                'affiliation'  => 'Visiting Researcher & Scholar (TBA)',
                'country'      => 'International',
                'description'  => 'Special invited lecture on clean renewable energy, sustainable technologies, and ecological transitions.',
                'full_desc'    => 'Official speaker announcement and session details will be published once confirmed by the organizing committee.',
                'serial'       => 12,
                'show_home'    => 1,
            ],
        ];

        Schema::disableForeignKeyConstraints();
        Speaker::truncate();
        Schema::enableForeignKeyConstraints();

        $defaultImg = public_path('img/default-speaker.jpg');

        foreach ($speakers as $s) {
            $speaker = Speaker::create([
                'name'             => $s['name'],
                'slug'             => $s['slug'],
                'speaker_type_id'  => $s['type_id'],
                'track_id'         => $s['track_id'],
                'focus_area'       => $s['focus'],
                'affiliation'      => $s['affiliation'],
                'country'          => $s['country'],
                'description'      => $s['description'],
                'full_description' => $s['full_desc'],
                'show_home'        => $s['show_home'],
                'serial'           => $s['serial'],
                'twitter'          => '#',
                'facebook'         => '#',
                'linkedin'         => '#',
            ]);

            if (file_exists($defaultImg)) {
                try {
                    $speaker->addMedia($defaultImg)->preservingOriginal()->toMediaCollection('photo');
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        }
    }
}
