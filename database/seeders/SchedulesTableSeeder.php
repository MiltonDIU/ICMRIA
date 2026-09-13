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
 * Source: Master Requirement Document (ICMRI 2027.docx), section 1, 2, 4, and 7.
 * Initial 6 sample draft sessions (3 for Day 1 and 3 for Day 2).
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

        // Resolve keynote & invited speakers
        $washizaki   = Speaker::where('name', 'like', '%Washizaki%')->first();
        $chakraborty = Speaker::where('name', 'like', '%Chakraborty%')->first();
        $piuri       = Speaker::where('name', 'like', '%Piuri%')->first();
        $moni        = Speaker::where('name', 'like', '%Moni%')->first();
        $roy         = Speaker::where('name', 'like', '%Roy%')->first();

        // Resolve Categories helper
        $cat = fn($name) => ScheduleCategory::where('name', $name)->value('id') 
            ?? ScheduleCategory::where('slug', 'like', '%' . \Illuminate\Support\Str::slug($name) . '%')->value('id');

        $schedules = [
            // ==========================================
            // DAY 1: 9 January 2027 (Saturday)
            // ==========================================
            [
                'day_number'            => 1,
                'start_time'            => '09:30:00',
                'title'                 => 'Inaugural & Opening Ceremony',
                'subtitle'              => 'Welcome address by Chief Guest, Chief Patron & General Chair; conference orientation.',
                'speaker_id'            => $washizaki->id ?? null,
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
                'start_time'            => '14:00:00',
                'title'                 => 'Parallel Technical Paper Sessions (Tracks 1 to 4)',
                'subtitle'              => 'Track 1 (AI & Data Science), Track 2 (Sustainability & Climate), Track 3 (Business & FinTech), Track 4 (Core Engineering).',
                'speaker_id'            => $chakraborty->id ?? null,
                'schedule_category_id'  => $cat('Technical Session'),
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
                'start_time'            => '14:30:00',
                'title'                 => 'Parallel Technical Paper Sessions (Tracks 5 to 8)',
                'subtitle'              => 'Track 5 (Social Sciences & Law), Track 6 (Health & Pharma), Track 7 (Education & Media), Track 8 (Agriculture & Food Security).',
                'speaker_id'            => $moni->id ?? null,
                'schedule_category_id'  => $cat('Technical Session'),
                'is_active'             => '1',
                'is_workshop'           => '0',
            ],
            [
                'day_number'            => 2,
                'start_time'            => '17:00:00',
                'title'                 => 'Valedictory & Closing Ceremony',
                'subtitle'              => 'Best Paper Awards, Certificate distribution, and closing remarks by the Organizing Committee.',
                'speaker_id'            => $roy->id ?? null,
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
