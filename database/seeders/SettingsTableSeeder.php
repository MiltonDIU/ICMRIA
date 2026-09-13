<?php
namespace Database\Seeders;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // ---------------------------------------------------------------
            // Identity / Branding
            // ---------------------------------------------------------------
            [
                'key'   => 'title',
                'value' => 'International Conference on<br><span>Multidisciplinary Research, Innovation and Applications</span> 2027',
            ],
            [
                'key'   => 'subtitle',
                'value' => '9&ndash;10 January 2027 &middot; Daffodil Smart City, Dhaka, Bangladesh',
            ],
            [
                'key'   => 'theme',
                'value' => 'Connecting Knowledge, Innovation and Society for a Sustainable and Intelligent Future',
            ],
            [
                'key'   => 'youtube_link',
                'value' => '',
            ],

            // ---------------------------------------------------------------
            // About section
            // ---------------------------------------------------------------
            [
                // Verbatim from requirement doc, section 2 (Homepage Overview & Themes).
                'key'   => 'about_description',
                'value' => 'The International Conference on Multidisciplinary Research, Innovation and Applications 2027 (ICMRIA 2027) is an international forum, sponsored by Daffodil International University (DIU), Bangladesh, that provides a platform for the international academic community, researchers, industry practitioners, policymakers and students from all over the world. The conference provides a common forum for exchanging ideas, original research, and networking with colleagues from other disciplines to inform a sustainable and intelligent future. Given ICMRIA 2027\'s focus on bringing together the best minds to address today\'s most pressing challenges, the conference welcomes contributions that connect knowledge, innovation, and society across technology, environment, business, health, humanities, and governance. Selected papers will be considered for publication in Scopus-indexed Q2 journals.',
            ],
            [
                // Verbatim from requirement doc, section 3 (Conference Themes).
                'key'   => 'theme_description',
                'value' => 'This theme aligns with the conference\'s focus on creating a space for discussion on the interaction of academic knowledge, technological innovation and the needs of society and is intended to stimulate research that will make a positive contribution to sustainable development and intelligent, future-oriented solutions for communities around the world.',
            ],
            [
                'key'   => 'about_where',
                'value' => 'Daffodil International University, Daffodil Smart City, Birulia, Savar, Dhaka-1216',
            ],
            [
                'key'   => 'about_when',
                'value' => '9&ndash;10 January 2027',
            ],

            // ---------------------------------------------------------------
            // Contact  (TODO: add official ICMRIA 2027 contact phone number)
            // ---------------------------------------------------------------
            [
                'key'   => 'contact_address',
                'value' => 'Daffodil International University, Daffodil Smart City, Birulia, Savar, Dhaka-1216, Bangladesh',
            ],
            [
                // DIU Engineering events office (WhatsApp) — carried over from the
                // live database; confirm/replace with the official ICMRIA 2027 number.
                'key'   => 'contact_phone',
                'value' => '+8801715102634 (WhatsApp)',
            ],
            [
                'key'   => 'contact_email',
                'value' => 'events.eng@diu.edu.bd',
            ],

            // ---------------------------------------------------------------
            // Footer
            // ---------------------------------------------------------------
            [
                'key'   => 'footer_description',
                'value' => 'ICMRIA 2027 &mdash; an international forum connecting knowledge, innovation and society across technology, environment, business, health, humanities and governance.',
            ],
            [
                'key'   => 'footer_address',
                'value' => 'Daffodil International University<br>Daffodil Smart City (DSC),<br>Birulia, Savar, Dhaka-1216',
            ],
            ['key' => 'footer_twitter',    'value' => '#'],
            ['key' => 'footer_facebook',   'value' => '#'],
            ['key' => 'footer_instagram',  'value' => '#'],
            ['key' => 'footer_googleplus', 'value' => '#'],
            ['key' => 'footer_linkedin',   'value' => '#'],

            // ---------------------------------------------------------------
            // Key dates  (source: ICMRIA 2027 requirement document)
            //   registration_start_date / early_registration_last_date are
            //   ASSUMPTIONS - not given in the doc; confirm with organisers.
            // ---------------------------------------------------------------
            ['key' => 'registration_start_date',       'value' => '2026-09-01 00:00:00'], // ASSUMED
            ['key' => 'abstract_submission_deadline',  'value' => '2026-10-30 23:59:00'],
            ['key' => 'manuscript_submission_start',   'value' => '2026-11-15 00:00:00'],
            ['key' => 'manuscript_submission_end',     'value' => '2026-11-30 23:59:00'],
            ['key' => 'early_registration_last_date',  'value' => '2026-12-10 23:59:00'], // ASSUMED
            ['key' => 'registration_close_date',       'value' => '2026-12-26 23:59:00'],
            ['key' => 'payment_last_date',             'value' => '2026-12-26 23:59:00'],
            ['key' => 'event_date',                    'value' => '2027-01-09'],
            ['key' => 'event_end_date',                'value' => '2027-01-10'],

            // ---------------------------------------------------------------
            // Abstract submission rules
            // ---------------------------------------------------------------
            ['key' => 'is_abstract_submission_open',   'value' => 'true'],
            ['key' => 'maximum_abstract_submission',   'value' => '3'],
            ['key' => 'seat_is_full',                  'value' => 'false'],
            ['key' => 'is_payment_enabled',            'value' => 'true'],

            // ---------------------------------------------------------------
            // Registration fees
            //   NOTE: the current PricingService keys fees by country->currency
            //   prefix (bdt/usd/inr/eur) + stage (earlybird/regular). The doc
            //   instead defines 5 CATEGORIES (International / SAARC / Student /
            //   Industry-R&D / Academic). A pricing-model rework is still
            //   needed; values below are the closest fit to the doc's table.
            //     International : early US$150 / late US$175
            //     SAARC        : early US$100 / late US$75
            //     Student      : early BDT 4,000 / late BDT 5,000
            //     Industry/R&D : early BDT 6,500 / late BDT 7,500
            //     Academic     : early BDT 6,000 / late BDT 7,000
            // ---------------------------------------------------------------
            ['key' => 'usd_earlybird_price',    'value' => '150'],
            ['key' => 'usd_regular_price',      'value' => '175'],
            ['key' => 'usd_participant_price',  'value' => '150'],

            ['key' => 'eur_earlybird_price',    'value' => '150'],
            ['key' => 'eur_regular_price',      'value' => '175'],
            ['key' => 'eur_participant_price',  'value' => '150'],

            ['key' => 'inr_earlybird_price',    'value' => '100'], // SAARC (USD-equivalent)
            ['key' => 'inr_regular_price',      'value' => '75'],
            ['key' => 'inr_participant_price',  'value' => '100'],

            ['key' => 'bdt_earlybird_price',    'value' => '6000'], // Academic (default local)
            ['key' => 'bdt_regular_price',      'value' => '7000'],
            ['key' => 'bdt_participant_price',  'value' => '6000'],

            ['key' => 'bdt_student_earlybird_price', 'value' => '4000'],
            ['key' => 'bdt_student_regular_price',   'value' => '5000'],

            // Legacy keys kept for backward compatibility
            ['key' => 'event_price',                  'value' => '7000'],
            ['key' => 'early_registration_event_price', 'value' => '6000'],
            ['key' => 'selected_domain_discount',     'value' => '6000'],
            ['key' => 'special_discount_is_true',     'value' => 'false'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
