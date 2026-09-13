<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\ScheduleCategory;
use App\Models\Speaker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ICMRIA 2027 Conference Schedule for Day 1 and Day 2 (9–10 January 2027).
 * Source: Master Requirement Document (ICMRI 2027.docx), section 1, 2, 4, and 7 ("Speakers & Co-located Events").
 */
class SchedulesTableSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('schedule_speaker')) {
            DB::table('schedule_speaker')->truncate();
        }
        if (Schema::hasTable('schedule_user')) {
            DB::table('schedule_user')->truncate();
        }
        DB::table('schedules')->truncate();
        Schema::enableForeignKeyConstraints();

        // Resolve keynote & invited speakers and session chairs
        $washizaki     = Speaker::where('name', 'like', '%Washizaki%')->first();
        $chakraborty   = Speaker::where('name', 'like', '%Chakraborty%')->first();
        $piuri         = Speaker::where('name', 'like', '%Piuri%')->first();
        $moni          = Speaker::where('name', 'like', '%Moni%')->first();
        $executive     = Speaker::where('name', 'like', '%Executive%')->first();
        $ramayah       = Speaker::where('name', 'like', '%Ramayah%')->first();
        $roy           = Speaker::where('name', 'like', '%Roy%')->first();
        $secretariat   = Speaker::where('name', 'like', '%Secretariat%')->first() ?? $executive;
        $trackChairs   = Speaker::where('name', 'like', '%Track Chairs%')->first() ?? $executive;
        $researchLabs  = Speaker::where('name', 'like', '%Research Labs%')->first() ?? $executive;
        $innovationLab = Speaker::where('name', 'like', '%Innovation%')->first() ?? $executive;
        $intlAdvisory  = Speaker::where('name', 'like', '%International Advisory%')->first() ?? $innovationLab;

        // Resolve Categories helper
        $cat = fn($name) => ScheduleCategory::where('name', $name)->value('id');

        $schedules = [
            // ==========================================
            // DAY 1: 9 January 2027 (Saturday)
            // ==========================================
            [
                'day_number'            => 1,
                'start_time'            => '08:30:00',
                'title'                 => 'Registration & Welcome Kit Distribution',
                'subtitle'              => 'Delegate check-in, conference kit, badge & program book distribution.',
                'speaker_id'            => $secretariat->id ?? null,
                'schedule_category_id'  => $cat('Networking & Break'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '09:30:00',
                'title'                 => 'Inaugural & Opening Ceremony',
                'subtitle'              => 'Welcome address by Chief Guest, Chief Patron & General Chair; conference orientation.',
                'speaker_id'            => $secretariat->id ?? null,
                'schedule_category_id'  => $cat('Inaugural & Closing Ceremony'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '10:30:00',
                'title'                 => 'Keynote Session 1: AI, Data Science & Smart Systems',
                'subtitle'              => 'Architectures, safety, and foundational models for next-generation intelligence.',
                'speaker_id'            => $washizaki->id ?? null,
                'schedule_category_id'  => $cat('Keynote Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '11:15:00',
                'title'                 => 'Keynote Session 2: Health Sciences, Biostatistics & Bioinformatics',
                'subtitle'              => 'Multidisciplinary data science in health innovations and clinical trials.',
                'speaker_id'            => $chakraborty->id ?? null,
                'schedule_category_id'  => $cat('Keynote Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '12:00:00',
                'title'                 => 'Invited Plenary Talk: Industry 4.0, Tech Transfer & Future Work',
                'subtitle'              => 'Bridging academia and global tech industry: commercialization and venture incubation.',
                'speaker_id'            => $executive->id ?? null,
                'schedule_category_id'  => $cat('Invited Plenary Talk'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '13:00:00',
                'title'                 => 'Networking Lunch & Prayer Break',
                'subtitle'              => 'Executive luncheon, inter-disciplinary delegate networking, and poster viewing.',
                'speaker_id'            => $secretariat->id ?? null,
                'schedule_category_id'  => $cat('Networking & Break'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '14:00:00',
                'title'                 => 'Parallel Technical Paper Sessions (Tracks 1 to 4)',
                'subtitle'              => 'Track 1 (AI & Data Science), Track 2 (Sustainability & Climate), Track 3 (Business & FinTech), Track 4 (Core Engineering).',
                'speaker_id'            => $trackChairs->id ?? null,
                'schedule_category_id'  => $cat('Technical Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 1,
                'start_time'            => '16:00:00',
                'title'                 => 'Special Event: Poster & Project Exhibition & Student Innovation Challenge',
                'subtitle'              => 'Showcase of working prototypes, research posters, and student multidisciplinary hackathon solutions.',
                'speaker_id'            => $innovationLab->id ?? null,
                'schedule_category_id'  => $cat('Special Event'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],

            // ==========================================
            // DAY 2: 10 January 2027 (Sunday)
            // ==========================================
            [
                'day_number'            => 2,
                'start_time'            => '09:00:00',
                'title'                 => 'Keynote Session 3: Sustainable Development & Intelligent Computing',
                'subtitle'              => 'Computational intelligence driving environmental resilience and smart systems.',
                'speaker_id'            => $piuri->id ?? null,
                'schedule_category_id'  => $cat('Keynote Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '09:45:00',
                'title'                 => 'Keynote Session 4: Health Sciences, Biotech & Digital Health',
                'subtitle'              => 'Genomics, molecular biology, and AI-enabled digital health interventions.',
                'speaker_id'            => $moni->id ?? null,
                'schedule_category_id'  => $cat('Keynote Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '10:30:00',
                'title'                 => 'Invited Technical Talk 1: Machine Learning, IoT & Edge Computing',
                'subtitle'              => 'Next-generation interconnected smart ecosystems and edge analytics.',
                'speaker_id'            => $ramayah->id ?? null,
                'schedule_category_id'  => $cat('Invited Technical Talk'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '11:15:00',
                'title'                 => 'Invited Technical Talk 2: Green Tech & Renewable Energy Systems',
                'subtitle'              => 'Innovative energy policy, green transition, and clean environmental technologies.',
                'speaker_id'            => $roy->id ?? null,
                'schedule_category_id'  => $cat('Invited Technical Talk'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '12:00:00',
                'title'                 => 'Hands-On Workshop: Applied Deep Learning & Generative AI Tutorials',
                'subtitle'              => 'Practical coding and model deployment session conducted by DIU Research Labs & industry domain experts.',
                'speaker_id'            => $researchLabs->id ?? null,
                'schedule_category_id'  => $cat('Hands-On Workshop'),
                'is_active'             => '1',
                'is_workshop'           => '1',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '13:30:00',
                'title'                 => 'Networking Lunch & Prayer Break',
                'subtitle'              => 'Lunch break and inter-faculty research collaboration networking.',
                'speaker_id'            => $secretariat->id ?? null,
                'schedule_category_id'  => $cat('Networking & Break'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '14:30:00',
                'title'                 => 'Parallel Technical Paper Sessions (Tracks 5 to 8)',
                'subtitle'              => 'Track 5 (Social Sciences & Law), Track 6 (Health & Pharma), Track 7 (Education & Media), Track 8 (Agriculture & Food Security).',
                'speaker_id'            => $trackChairs->id ?? null,
                'schedule_category_id'  => $cat('Technical Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '16:00:00',
                'title'                 => 'Co-located Programs: Doctoral Consortium, Industry Forum & Women in STEM (WIE)',
                'subtitle'              => 'Mentoring for Ph.D. scholars, academia-industry commercialization panels, and women leadership in STEM.',
                'speaker_id'            => $intlAdvisory->id ?? ($innovationLab->id ?? null),
                'schedule_category_id'  => $cat('Co-located Program'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '17:00:00',
                'title'                 => 'Valedictory & Closing Ceremony',
                'subtitle'              => 'Best Paper Awards, Certificate distribution, and closing remarks by the Organizing Committee.',
                'speaker_id'            => $secretariat->id ?? null,
                'schedule_category_id'  => $cat('Inaugural & Closing Ceremony'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
        ];

        foreach ($schedules as $item) {
            $schedule = Schedule::create($item);
            if (!empty($item['speaker_id'])) {
                $schedule->speakers()->sync([$item['speaker_id']]);
            }
        }
    }
}
