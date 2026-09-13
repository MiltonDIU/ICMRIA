<?php

namespace Database\Seeders;

use App\Models\ConferenceMessage;
use App\Models\ConferenceMessageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leadership and dignitary messages for ICMRIA 2027.
 * Source: Requirement document, section 1 navigation ("Messages" menu) & section 6.
 */
class ConferenceMessageSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('conference_messages')->truncate();
        Schema::enableForeignKeyConstraints();

        $categoryMap = ConferenceMessageCategory::pluck('id', 'name')->toArray();

        $messages = [
            [
                'category_name' => 'Chief Patron & Patron',
                'variant'       => 'Chief Patron',
                'person_name'   => 'Dr. Md. Sabur Khan',
                'designation'   => 'Chairman, Board of Trustees',
                'affiliation'   => 'Daffodil International University (DIU)',
                'message'       => 'On behalf of Daffodil International University, it is my distinct privilege to welcome distinguished researchers, academicians, industry leaders, and participants to the International Conference on Multidisciplinary Research, Innovation and Applications 2027 (ICMRIA 2027). DIU has always been committed to fostering innovation, research excellence, and sustainable solutions that bridge academia and society. I wish all delegates an inspiring and fruitful conference.',
                'sort_order'    => 1,
                'is_published'  => false,
            ],
            [
                'category_name' => 'Chief Patron & Patron',
                'variant'       => 'Patron',
                'person_name'   => 'Prof. Dr. M. Lutfar Rahman',
                'designation'   => 'Vice-Chancellor',
                'affiliation'   => 'Daffodil International University (DIU)',
                'message'       => 'I am delighted to invite the global academic and scientific community to ICMRIA 2027 at Daffodil Smart City. In an era of rapid technological advancement and complex global challenges, multidisciplinary collaboration is essential. We look forward to meaningful scholarly discussions and impactful innovations.',
                'sort_order'    => 2,
                'is_published'  => false,
            ],
            [
                'category_name' => 'Organizing Chair',
                'variant'       => 'Convener',
                'person_name'   => 'Professor Mohammad Masum Iqbal, PhD.',
                'designation'   => 'Pro-Vice-Chancellor & Convener, ICMRIA 2027',
                'affiliation'   => 'Daffodil International University (DIU)',
                'message'       => 'Welcome to ICMRIA 2027. Our organizing committee has curated 8 vibrant tracks, keynote talks from globally renowned scholars, and rich co-located programs. We look forward to your active participation in making this conference a grand success.',
                'sort_order'    => 3,
                'is_published'  => false,
            ],
            [
                'category_name' => 'General Chair',
                'variant'       => null,
                'person_name'   => 'To be announced',
                'designation'   => 'General Chair, ICMRIA 2027',
                'affiliation'   => 'Daffodil International University',
                'message'       => 'Message text to be updated by the organising committee.',
                'sort_order'    => 4,
                'is_published'  => false,
            ],
            [
                'category_name' => 'TPC Chair',
                'variant'       => null,
                'person_name'   => 'To be announced',
                'designation'   => 'Technical Program Committee (TPC) Chair',
                'affiliation'   => 'Daffodil International University',
                'message'       => 'Message text to be updated by the organising committee.',
                'sort_order'    => 5,
                'is_published'  => false,
            ],
            [
                'category_name' => 'Chief Guest',
                'variant'       => 'Opening',
                'person_name'   => 'To be announced',
                'designation'   => 'Chief Guest (Inaugural Ceremony)',
                'affiliation'   => null,
                'message'       => 'Opening address to be published.',
                'sort_order'    => 6,
                'is_published'  => false,
            ],
            [
                'category_name' => 'Chief Guest',
                'variant'       => 'Closing',
                'person_name'   => 'To be announced',
                'designation'   => 'Chief Guest (Valedictory Ceremony)',
                'affiliation'   => null,
                'message'       => 'Closing address to be published.',
                'sort_order'    => 7,
                'is_published'  => false,
            ],
        ];

        foreach ($messages as $msg) {
            $catId = $categoryMap[$msg['category_name']] ?? null;
            unset($msg['category_name']);
            $msg['conference_message_category_id'] = $catId;
            ConferenceMessage::create($msg);
        }
    }
}