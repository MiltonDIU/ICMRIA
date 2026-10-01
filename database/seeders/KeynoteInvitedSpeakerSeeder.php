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
 * Populates the 7 distinguished keynote & invited speakers with demo photos and full academic bios.
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
            // 1. Keynote Speakers
            // =========================================================================
            [
                'name'         => 'Prof. Dr. Hrishikesh Chakraborty',
                'slug'         => 'prof-dr-hrishikesh-chakraborty',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biostatistics & Bioinformatics',
                'affiliation'  => 'Duke University, Durham, North Carolina',
                'country'      => 'United States',
                'description'  => 'International Advisor, Division of Research, DIU & Professor of Biostatistics and Bioinformatics at Duke University.',
                'full_desc'    => 'Prof. Dr. Hrishikesh Chakraborty is a world-renowned statistician and health data scientist with Duke University. His keynote address focuses on state-of-the-art biostatistical methodologies, digital health analytics, and multi-omics bioinformatics applications.',
                'serial'       => 1,
                'show_home'    => 1,
                'img'          => '1.jpg',
            ],
            [
                'name'         => 'Prof. Dr. Hironori Washizaki',
                'slug'         => 'prof-dr-hironori-washizaki',
                'type_id'      => $keynote->id,
                'track_id'     => $t(1),
                'focus'        => 'AI, Data Science & Smart Systems',
                'affiliation'  => 'Waseda University',
                'country'      => 'Japan',
                'description'  => 'Professor, Department of Computer Science & Engineering, Waseda University, Tokyo, Japan.',
                'full_desc'    => 'Prof. Dr. Hironori Washizaki is an internationally recognized scholar in artificial intelligence, software engineering, and smart autonomous systems. His keynote presentation explores foundational models, safe AI architectures, and intelligent software engineering.',
                'serial'       => 2,
                'show_home'    => 1,
                'img'          => '2.jpg',
            ],
            [
                'name'         => 'Prof. Dr. Mohammad Ali Moni',
                'slug'         => 'prof-dr-mohammad-ali-moni',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biotech & Digital Health',
                'affiliation'  => 'University of Queensland',
                'country'      => 'Australia',
                'description'  => 'Associate Professor & AI Health Lead, University of Queensland, Australia.',
                'full_desc'    => 'Prof. Dr. Mohammad Ali Moni leads pioneering research in artificial intelligence for medical diagnosis, computational genomics, and health informatics. His keynote covers digital health interventions and biotechnology transformation.',
                'serial'       => 3,
                'show_home'    => 1,
                'img'          => '3.jpg',
            ],
            [
                'name'         => 'Prof. Dr. Vincenzo Piuri',
                'slug'         => 'prof-dr-vincenzo-piuri',
                'type_id'      => $keynote->id,
                'track_id'     => $t(2),
                'focus'        => 'Sustainable Development & Intelligent Computing',
                'affiliation'  => 'University of Milan',
                'country'      => 'Italy',
                'description'  => 'Professor of Computer Science, University of Milan, Italy; IEEE Fellow.',
                'full_desc'    => 'Prof. Dr. Vincenzo Piuri is an IEEE Fellow and eminent researcher in computational intelligence, pattern analysis, and distributed smart sensor networks for environmental and sustainable development.',
                'serial'       => 4,
                'show_home'    => 1,
                'img'          => '4.jpg',
            ],

            // =========================================================================
            // 2. Invited Plenary & Technical Talks
            // =========================================================================
            [
                'name'         => 'Senior Industry Executive / Tech Leader',
                'slug'         => 'senior-industry-executive-tech-leader',
                'type_id'      => $plenary->id,
                'track_id'     => null,
                'focus'        => 'Industry 4.0, Tech Transfer & Future Work',
                'affiliation'  => 'Leading Multinational Tech Enterprise / R&D Director',
                'country'      => 'International',
                'description'  => 'Distinguished Plenary Speaker bridging academia and global tech industry commercialization.',
                'full_desc'    => 'This special plenary talk examines real-world Industry 4.0 deployments, technology commercialization frameworks, venture incubation, and university-industry intellectual property transfer pipelines.',
                'serial'       => 10,
                'show_home'    => 1,
                'img'          => '5.jpg',
            ],
            [
                'name'         => 'Prof. T. Ramayah',
                'slug'         => 'prof-t-ramayah',
                'type_id'      => $invited->id,
                'track_id'     => $t(1),
                'focus'        => 'Machine Learning, IoT & Edge Computing',
                'affiliation'  => 'Universiti Sains Malaysia (USM)',
                'country'      => 'Malaysia',
                'description'  => 'Visiting Professor, Universiti Sains Malaysia (USM), Penang, Malaysia.',
                'full_desc'    => 'Prof. T. Ramayah is an authoritatively cited researcher specializing in empirical research methods, smart information systems, machine learning applications, and Internet of Things architectures.',
                'serial'       => 11,
                'show_home'    => 1,
                'img'          => '6.jpg',
            ],
            [
                'name'         => 'Dr. Bibhuti Roy',
                'slug'         => 'dr-bibhuti-roy',
                'type_id'      => $invited->id,
                'track_id'     => $t(2),
                'focus'        => 'Green Tech & Renewable Energy Systems',
                'affiliation'  => 'University of Bremen',
                'country'      => 'Germany',
                'description'  => 'Visiting Professor & Researcher, University of Bremen, Germany.',
                'full_desc'    => 'Dr. Bibhuti Roy conducts advanced research in renewable clean energy, sustainable technologies, ecological transitions, and solar engineering implementations across developing delta regions.',
                'serial'       => 12,
                'show_home'    => 1,
                'img'          => '1.jpg',
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

            $demoImg = storage_path("seeders/speakers/{$s['img']}");
            if (file_exists($demoImg)) {
                try {
                    $speaker->addMedia($demoImg)->preservingOriginal()->toMediaCollection('photo');
                } catch (\Exception $e) {
                    // Fail silently
                }
            } elseif (file_exists($defaultImg)) {
                try {
                    $speaker->addMedia($defaultImg)->preservingOriginal()->toMediaCollection('photo');
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        }
    }
}