<?php

namespace Database\Seeders;

use App\Models\Speaker;
use App\Models\SpeakerType;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Keynote / Invited speakers and Session Chairs for ICMRIA 2027.
 * All speakers are seeded with a uniform, professional dummy picture (default-speaker.jpg)
 * until real photographs are uploaded via the admin panel.
 *
 * Source: Master Requirement Document (ICMRI 2027.docx), section 7 & 6.
 */
class KeynoteInvitedSpeakerSeeder extends Seeder
{
    public function run(): void
    {
        $keynote = SpeakerType::firstOrCreate(['title' => 'Keynote Speaker'], ['slug' => 'keynote-speaker', 'publication_status' => 1]);
        $invited = SpeakerType::firstOrCreate(['title' => 'Invited Speaker'], ['slug' => 'invited-speaker', 'publication_status' => 1]);
        $plenary = SpeakerType::firstOrCreate(['title' => 'Plenary Speaker'], ['slug' => 'plenary-speaker', 'publication_status' => 1]);
        $chair   = SpeakerType::firstOrCreate(['title' => 'Session Chair & Organization'], ['slug' => 'session-chair', 'publication_status' => 1]);

        $t = fn (int $n) => optional(Track::where('name', 'like', "Track {$n}:%")->first())->id;

        $speakers = [
            // =========================================================================
            // 1. Keynote Speakers (Track-wise, show_home = 1)
            // =========================================================================
            [
                'name'         => 'Prof. Dr. Hrishikesh Chakraborty',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biostatistics & Bioinformatics',
                'affiliation'  => 'Duke University, Durham, North Carolina',
                'country'      => 'United States',
                'description'  => 'International Advisor, Division of Research, DIU & Professor of Biostatistics and Bioinformatics at Duke University.',
                'full_desc'    => 'Prof. Dr. Hrishikesh Chakraborty is a world-renowned statistician and health data scientist with Duke University. His keynote address focuses on state-of-the-art biostatistical methodologies, digital health analytics, and multi-omics bioinformatics applications.',
                'serial'       => 1,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Prof. Dr. Hironori Washizaki',
                'type_id'      => $keynote->id,
                'track_id'     => $t(1),
                'focus'        => 'AI, Data Science & Smart Systems',
                'affiliation'  => 'Waseda University',
                'country'      => 'Japan',
                'description'  => 'Professor, Department of Computer Science & Engineering, Waseda University, Tokyo, Japan.',
                'full_desc'    => 'Prof. Dr. Hironori Washizaki is an internationally recognized scholar in artificial intelligence, software engineering, and smart autonomous systems. His keynote presentation explores foundational models, safe AI architectures, and intelligent software engineering.',
                'serial'       => 2,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Prof. Dr. Mohammad Ali Moni',
                'type_id'      => $keynote->id,
                'track_id'     => $t(6),
                'focus'        => 'Health Sciences, Biotech & Digital Health',
                'affiliation'  => 'University of Queensland',
                'country'      => 'Australia',
                'description'  => 'Associate Professor & AI Health Lead, University of Queensland, Australia.',
                'full_desc'    => 'Prof. Dr. Mohammad Ali Moni leads pioneering research in artificial intelligence for medical diagnosis, computational genomics, and health informatics. His keynote covers digital health interventions and biotechnology transformation.',
                'serial'       => 3,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Prof. Dr. Vincenzo Piuri',
                'type_id'      => $keynote->id,
                'track_id'     => $t(2),
                'focus'        => 'Sustainable Development & Intelligent Computing',
                'affiliation'  => 'University of Milan',
                'country'      => 'Italy',
                'description'  => 'Professor of Computer Science, University of Milan, Italy; IEEE Fellow.',
                'full_desc'    => 'Prof. Dr. Vincenzo Piuri is an IEEE Fellow and eminent researcher in computational intelligence, pattern analysis, and distributed smart sensor networks for environmental and sustainable development.',
                'serial'       => 4,
                'show_home'    => 1,
            ],

            // =========================================================================
            // 2. Invited Plenary & Technical Talks (show_home = 1)
            // =========================================================================
            [
                'name'         => 'Senior Industry Executive / Tech Leader',
                'type_id'      => $plenary->id,
                'track_id'     => null,
                'focus'        => 'Industry 4.0, Tech Transfer & Future Work',
                'affiliation'  => 'Leading Multinational Tech Enterprise / R&D Director',
                'country'      => 'International',
                'description'  => 'Distinguished Plenary Speaker bridging academia and global tech industry commercialization.',
                'full_desc'    => 'This special plenary talk examines real-world Industry 4.0 deployments, technology commercialization frameworks, venture incubation, and university-industry intellectual property transfer pipelines.',
                'serial'       => 10,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Prof. T. Ramayah',
                'type_id'      => $invited->id,
                'track_id'     => $t(1),
                'focus'        => 'Machine Learning, IoT & Edge Computing',
                'affiliation'  => 'Universiti Sains Malaysia (USM)',
                'country'      => 'Malaysia',
                'description'  => 'Visiting Professor, Universiti Sains Malaysia (USM), Penang, Malaysia.',
                'full_desc'    => 'Prof. T. Ramayah is an authoritatively cited researcher specializing in empirical research methods, smart information systems, machine learning applications, and Internet of Things architectures.',
                'serial'       => 11,
                'show_home'    => 1,
            ],
            [
                'name'         => 'Dr. Bibhuti Roy',
                'type_id'      => $invited->id,
                'track_id'     => $t(2),
                'focus'        => 'Green Tech & Renewable Energy Systems',
                'affiliation'  => 'University of Bremen',
                'country'      => 'Germany',
                'description'  => 'Visiting Professor & Researcher, University of Bremen, Germany.',
                'full_desc'    => 'Dr. Bibhuti Roy conducts advanced research in renewable clean energy, sustainable technologies, ecological transitions, and solar engineering implementations across developing delta regions.',
                'serial'       => 12,
                'show_home'    => 1,
            ],

            // =========================================================================
            // 4. Session Chairs & Co-located Organizations (For Schedule assignments)
            // =========================================================================
            [
                'name'         => 'Conference Secretariat & Organizing Committee',
                'type_id'      => $chair->id,
                'track_id'     => null,
                'focus'        => 'Conference Operations & Ceremonies',
                'affiliation'  => 'Daffodil International University',
                'country'      => 'Bangladesh',
                'description'  => 'Presiding over Inaugural, Luncheon, and Valedictory Ceremonies.',
                'full_desc'    => 'The Conference Organizing Committee oversees overall program logistics, keynote sessions, ceremonial activities, and delegate support.',
                'serial'       => 20,
                'show_home'    => 0,
            ],
            [
                'name'         => 'Track Chairs & Co-Chairs',
                'type_id'      => $chair->id,
                'track_id'     => null,
                'focus'        => 'Parallel Technical Paper Sessions',
                'affiliation'  => 'Daffodil International University',
                'country'      => 'Bangladesh',
                'description'  => 'Chairs presiding over Parallel Technical Paper Sessions across all 8 tracks.',
                'full_desc'    => 'Senior faculty deans and track chairs evaluating and moderating oral paper presentations across Tracks 1 through 8.',
                'serial'       => 21,
                'show_home'    => 0,
            ],
            [
                'name'         => 'DIU Research Labs & Industry Partners',
                'type_id'      => $chair->id,
                'track_id'     => $t(1),
                'focus'        => 'Applied Deep Learning & Generative AI Tutorials',
                'affiliation'  => 'DIU Research Labs & Industry Partners',
                'country'      => 'Bangladesh',
                'description'  => 'Hands-on workshop instructors & laboratory domain experts.',
                'full_desc'    => 'Industry practitioners and research lab fellows delivering intensive hands-on tutorials in generative AI and deep learning deployment.',
                'serial'       => 22,
                'show_home'    => 0,
            ],
            [
                'name'         => 'Innovation & Entrepreneurship Lab / WIE',
                'type_id'      => $chair->id,
                'track_id'     => null,
                'focus'        => 'Exhibition, Hackathon & Women in STEM',
                'affiliation'  => 'DIU Innovation Lab & WIE Affinity Group',
                'country'      => 'Bangladesh',
                'description'  => 'Coordinators for Student Innovation Challenge, Posters, and Women in STEM.',
                'full_desc'    => 'Mentoring sessions, prototype project exhibitions, hackathons, and panels championing women leadership across multidisciplinary STEM fields.',
                'serial'       => 23,
                'show_home'    => 0,
            ],
            [
                'name'         => 'International Advisory Committee',
                'type_id'      => $chair->id,
                'track_id'     => null,
                'focus'        => 'Doctoral Consortium & Research Mentorship',
                'affiliation'  => 'International Advisory Committee',
                'country'      => 'International',
                'description'  => 'Mentoring panel for Ph.D. scholars and early-career researchers.',
                'full_desc'    => 'Senior international professors providing critical feedback, publication strategies, and research guidance to doctoral candidates.',
                'serial'       => 24,
                'show_home'    => 0,
            ],
        ];

        $defaultImg = public_path('img/default-speaker.jpg');

        foreach ($speakers as $s) {
            $speaker = Speaker::updateOrCreate(
                ['name' => $s['name']],
                [
                    'slug'             => Str::slug($s['name']) ?: null,
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
                ]
            );

            // Clean previous random faker media and use the uniform default dummy picture
            if (file_exists($defaultImg)) {
                $speaker->clearMediaCollection('photo');
                try {
                    $speaker->addMedia($defaultImg)->preservingOriginal()->toMediaCollection('photo');
                } catch (\Exception $e) {
                    // Fail silently if media already attached
                }
            }
        }
    }
}
