<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Frequently Asked Questions for ICMRIA 2027.
 * Answers kept <= 240 chars due to VARCHAR(250) column length.
 * Source: Requirement document, section 2, 3, 4, 5, and 7.
 */
class FaqsTableSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('event_faq')) {
            DB::table('event_faq')->truncate();
        }
        DB::table('faqs')->truncate();
        Schema::enableForeignKeyConstraints();

        $faqs = [
            [
                'question' => 'What is the theme and scope of ICMRIA 2027?',
                'answer'   => 'Theme: "Connecting Knowledge, Innovation and Society for a Sustainable and Intelligent Future" across 8 multidisciplinary tracks including AI, Sustainability, Engineering, Health, Business, and Agriculture.',
            ],
            [
                'question' => 'What are the important deadlines for ICMRIA 2027?',
                'answer'   => 'Abstracts: 30 Oct 2026. Full manuscripts: 15–30 Nov 2026. Registration: 26 Dec 2026. Conference dates: 9–10 January 2027 at Daffodil Smart City, Dhaka.',
            ],
            [
                'question' => 'What are the manuscript formatting guidelines?',
                'answer'   => 'Abstracts should be 200–250 words. Full papers must follow standard IEEE conference formatting guidelines and be submitted electronically via the conference portal.',
            ],
            [
                'question' => 'Will accepted papers be published in indexed journals?',
                'answer'   => 'All accepted & presented papers will appear in the conference proceedings. Selected high-quality papers will be considered for publication in Scopus-indexed Q2 journals.',
            ],
            [
                'question' => 'Can international participants attend or present online?',
                'answer'   => 'Yes. ICMRIA 2027 runs in Blended Mode (Onsite at Daffodil Smart City, Dhaka + Online via interactive sessions) to welcome participants globally.',
            ],
            [
                'question' => 'What are the conference registration fees?',
                'answer'   => 'Students: ৳4,000 (Late: ৳5,000); Academics: ৳6,000 (Late: ৳7,000); Industry/R&D: ৳6,500; SAARC: US$ 100; International: US$ 150 (Late: US$ 175).',
            ],
            [
                'question' => 'What co-located events and programs are organized?',
                'answer'   => 'Poster & Project Exhibition, Student Innovation Challenge (hackathon), Doctoral Consortium for PhDs, Industry Startup Forum, and Women in STEM (WIE) sessions.',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}