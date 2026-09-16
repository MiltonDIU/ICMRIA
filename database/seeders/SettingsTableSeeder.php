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
                'value' => '+8801946704373 (WhatsApp)',
            ],
            [
                'key'   => 'contact_email',
                'value' => 'fahadhossain.swe@diu.edu.bd',
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
            // Camera-ready manuscript and copyright form (document, Phase 6).
            ['key' => 'camera_ready_deadline',         'value' => '2026-12-26 23:59:00'],
            // Revised manuscript for papers accepted with minor revisions (document, Phase 5).
            ['key' => 'revision_deadline',             'value' => '2026-12-10 23:59:00'],
            // Full manuscript length, IEEE conference format (Author Guidelines).
            ['key' => 'manuscript_min_pages',          'value' => '6'],
            ['key' => 'manuscript_max_pages',          'value' => '8'],
            // Research keywords a reviewer gives about themselves.
            ['key' => 'reviewer_keywords_min',         'value' => '3'],
            ['key' => 'reviewer_keywords_max',         'value' => '5'],
            ['key' => 'event_date',                    'value' => '2027-01-09'],
            ['key' => 'event_end_date',                'value' => '2027-01-10'],

            // ---------------------------------------------------------------
            // Abstract submission rules
            // ---------------------------------------------------------------
            ['key' => 'is_abstract_submission_open',   'value' => 'true'],
            ['key' => 'maximum_abstract_submission',   'value' => '3'],
            ['key' => 'seat_is_full',                  'value' => 'false'],
            ['key' => 'is_payment_enabled',            'value' => 'true'],

            // Requirement document, "Author Guidelines": abstracts of 200-250 words
            // and 4-6 keywords. Read through App\Services\SubmissionRules so the
            // forms and the validators can never drift apart.
            ['key' => 'abstract_min_words',            'value' => '200'],
            ['key' => 'abstract_max_words',            'value' => '250'],
            ['key' => 'keywords_min',                  'value' => '4'],
            ['key' => 'keywords_max',                  'value' => '6'],

            // Review workload. These are the conference-wide figures; any track
            // needing different ones overrides them on its own row (tracks table),
            // and a blank override falls back to the value here.
            // "at least 2 to 3 independent reviewers per paper" (document, Phase 3).
            // 'double' hides author identities from reviewers, 'single' does not
            // (document, Phase 1: "blind review settings").
            ['key' => 'blind_review_mode',             'value' => 'double'],

            ['key' => 'reviewers_per_paper',           'value' => '3'],
            ['key' => 'min_reviewers_per_paper',       'value' => '2'],
            ['key' => 'max_papers_per_reviewer',       'value' => '10'],

            // Paper bidding is optional in the document (Phase 3); 'false' switches it off.
            ['key' => 'bidding_enabled',               'value' => 'true'],

            // ---------------------------------------------------------------
            // Registration fees live in the prices table, one row per delegate category
            // carrying both the early-bird and the regular amount, and PricingService
            // reads them from there. The per-currency keys that used to sit here were
            // read by nothing, so they were removed rather than left to be edited in
            // Settings by someone expecting the site to charge them.
            //
            // What stays below is the domain discount, which PricingService does read.
            // ---------------------------------------------------------------
            ['key' => 'selected_domain_discount',     'value' => '6000'],
            ['key' => 'special_discount_is_true',     'value' => 'false'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
