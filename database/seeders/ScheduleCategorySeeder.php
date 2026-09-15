<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\ScheduleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Schedule Session Categories based strictly on the Master Document (ICMRI 2027.docx):
 * - Section 7: Speakers & Co-located Events
 *   - Table 1: Keynote Speakers
 *   - Table 2: Invited Talks & Technical Sessions (Column: "Session Category")
 *   - Table 3: Co-Located Events & Special Programs (Column: "Program Category")
 * - Core Ceremonies, Networking, and Parallel Track Presentation Sessions
 */
class ScheduleCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Clean any old redundant placeholder names if they have no schedules
        $redundant = [
            'Plenary Session',
            'Workshop & Tutorials',
            'Ceremony & Protocol',
            'Special Event & Exhibition',
        ];
        ScheduleCategory::whereIn('name', $redundant)->whereDoesntHave('schedules')->delete();

        $categories = [
            [
                'name'        => 'Keynote Session',
                'description' => 'Plenary keynote addresses delivered by world-renowned international scholars.',
                'color'       => '#00396B', // Signature Navy
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Invited Plenary Talk',
                'description' => 'Executive plenary address on Industry 4.0, Technology Transfer, and Future of Work.',
                'color'       => '#6B21A8', // Royal Purple
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Invited Technical Talk',
                'description' => 'Invited specialized lectures by international scholars on frontier technologies.',
                'color'       => '#0284C7', // Sky Blue
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Technical Session',
                'description' => 'Peer-reviewed research paper presentations across Tracks 1 through 8.',
                'color'       => '#0055A0', // Ocean Blue
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Regular Session',
                'description' => 'Standard conference paper presentation and academic discussion sessions.',
                'color'       => '#4A5568', // Slate Gray
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Hands-On Workshop',
                'description' => 'Intensive technical tutorials on applied deep learning, generative AI, and deployment.',
                'color'       => '#D97706', // Amber
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Special Event',
                'description' => 'Student Innovation Challenge hackathon, research posters, and working prototype exhibitions.',
                'color'       => '#DC2626', // Crimson Red
                'sort_order'  => 7,
            ],
            [
                'name'        => 'Co-located Program',
                'description' => 'Doctoral Consortium, Industry & Startup Forum, and Women in Engineering & STEM (WIE).',
                'color'       => '#7C3AED', // Violet
                'sort_order'  => 8,
            ],
            [
                'name'        => 'Inaugural & Closing Ceremony',
                'description' => 'Official opening and valedictory ceremonies, Chief Guest speeches, and Best Paper Awards.',
                'color'       => '#059669', // Emerald Green
                'sort_order'  => 9,
            ],
            [
                'name'        => 'Networking & Break',
                'description' => 'Delegate check-in & kits, executive networking luncheons, and prayer breaks.',
                'color'       => '#475569', // Muted Slate
                'sort_order'  => 10,
            ],
        ];

        $categoryMap = [];
        foreach ($categories as $cat) {
            $created = ScheduleCategory::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'slug'        => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'color'       => $cat['color'],
                    'sort_order'  => $cat['sort_order'],
                    'is_active'   => true,
                ]
            );
            $categoryMap[$cat['name']] = $created->id;
        }

        // Exact document mapping for all 17 schedules
        $mapping = [
            1  => 'Networking & Break',             // Registration & Welcome Kit Distribution
            2  => 'Inaugural & Closing Ceremony',    // Inaugural & Opening Ceremony
            3  => 'Keynote Session',                // Keynote 1: AI, Data Science (Washizaki)
            4  => 'Keynote Session',                // Keynote 2: Health Sciences (Chakraborty)
            5  => 'Invited Plenary Talk',           // Invited Plenary Talk: Industry 4.0 (Senior Executive)
            6  => 'Networking & Break',             // Networking Lunch & Prayer Break Day 1
            7  => 'Technical Session',              // Parallel Technical Paper Sessions (Tracks 1 to 4)
            8  => 'Special Event',                  // Poster & Project Exhibition & Student Innovation Challenge
            9  => 'Keynote Session',                // Keynote 3: Sustainable Development (Piuri)
            10 => 'Keynote Session',                // Keynote 4: Health Sciences & Digital Health (Moni)
            11 => 'Invited Technical Talk',         // Invited Technical Talk 1: Machine Learning, IoT (Ramayah)
            12 => 'Invited Technical Talk',         // Invited Technical Talk 2: Green Tech (Roy)
            13 => 'Hands-On Workshop',              // Hands-On Workshop: Applied Deep Learning (DIU Labs)
            14 => 'Networking & Break',             // Networking Lunch & Prayer Break Day 2
            15 => 'Technical Session',              // Parallel Technical Paper Sessions (Tracks 5 to 8)
            16 => 'Co-located Program',             // Doctoral Consortium, Industry Forum & Women in STEM
            17 => 'Inaugural & Closing Ceremony',    // Valedictory & Closing Ceremony
        ];

        foreach ($mapping as $scheduleId => $catName) {
            if (isset($categoryMap[$catName])) {
                Schedule::where('id', $scheduleId)->update([
                    'schedule_category_id' => $categoryMap[$catName]
                ]);
            }
        }
    }
}
