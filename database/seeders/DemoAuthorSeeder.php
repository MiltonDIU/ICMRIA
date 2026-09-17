<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Paper;
use App\Models\PaperConflict;
use App\Models\Price;
use App\Models\Profile;
use App\Models\Profile as ProfileModel;
use App\Models\SubTrack;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\IdGeneratorService;
use App\Services\PricingService;
use App\Services\SubmissionRules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Five authors and their submissions, so the whole picture can be seen at once:
 * every delegate category, papers in four different tracks, manuscripts uploaded and
 * still missing, a declared conflict of interest, and keywords that genuinely overlap
 * the seeded reviewers' expertise.
 *
 * This is demonstration data. Every account sits on @demo.icmria.com so it can be
 * found and removed in one query, and the seeder refuses to run in production.
 */
class DemoAuthorSeeder extends Seeder
{
    private const DEMO_DOMAIN = 'demo.icmria.com';
    private const ROLE_AUTHOR = 3;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoAuthorSeeder skipped: never seed demonstration authors in production.');
            return;
        }

        if (Track::count() === 0 || Price::count() === 0) {
            $this->command?->warn('Run the track and price seeders first.');
            return;
        }

        $created = 0;

        foreach ($this->submissions() as $entry) {
            DB::transaction(function () use ($entry, &$created) {
                $created += $this->build($entry) ? 1 : 0;
            });
        }

        $papers = Paper::whereHas('user', fn ($q) => $q->where('email', 'like', '%@' . self::DEMO_DOMAIN))->count();

        $this->command?->info("Demonstration data: {$created} author(s) added, {$papers} paper(s) on file.");
        $this->command?->info('All of it sits on @' . self::DEMO_DOMAIN . ' and can be removed with one query.');
    }

    /** @return bool whether a new author was created */
    private function build(array $entry): bool
    {
        $country = Country::whereRaw('LOWER(name) = ?', [Str::lower($entry['country'])])->first();
        $price = Price::where('category', $entry['category'])->first();
        $subTrack = SubTrack::where('name', 'like', $entry['sub_track'] . '%')->first();

        if (!$country || !$price || !$subTrack) {
            $this->command?->warn("Skipping {$entry['name']}: track, country or price not found.");
            return false;
        }

        $email = Str::slug($entry['name']) . '@' . self::DEMO_DOMAIN;
        $existing = User::where('email', $email)->first();

        $user = $existing ?: User::create([
            'name' => $entry['name'],
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->roles()->syncWithoutDetaching([self::ROLE_AUTHOR]);

        Profile::updateOrCreate(['user_id' => $user->id], [
            'first_name' => Str::before($entry['name'], ' '),
            'last_name' => Str::afterLast($entry['name'], ' '),
            'designation' => $entry['designation'],
            'department' => $entry['department'],
            'institution' => $entry['institution'],
            'country_id' => $country->id,
            'price_id' => $price->id,
            'registration_id' => ProfileModel::where('user_id', $user->id)->value('registration_id')
                ?? IdGeneratorService::generateRegistrationId(),
            'whatsapp_number' => $entry['phone'],
            'is_author' => 1,
            'participation_mode' => $entry['mode'],
            'payment_status' => $entry['paid'] ? '1' : '0',
        ]);

        $paper = Paper::firstOrNew(['user_id' => $user->id, 'title' => $entry['title']]);

        if (!$paper->exists) {
            $paper->fill([
                'submission_id' => IdGeneratorService::generateSubmissionId(),
                'abstract' => $entry['abstract'],
                'keywords' => SubmissionRules::splitKeywords($entry['keywords']),
                'track_id' => $subTrack->track_id,
                'sub_track_id' => $subTrack->id,
                'mode_of_participation' => $entry['mode'],
                'is_corresponding_author' => 1,
                'has_multiple_authors' => count($entry['co_authors']) > 0,
                'status' => $entry['status'],
            ])->save();

            $this->addAuthors($paper, $user, $entry, $country, $price);
        }

        if ($entry['manuscript'] && !$paper->hasManuscript()) {
            $this->attachManuscript($paper, $entry);
        }

        if ($entry['conflict'] && $paper->conflicts()->doesntExist()) {
            $this->declareConflict($paper, $user, $entry['conflict']);
        }

        PricingService::updateProfileTotalDue($user->profile->fresh());

        return !$existing;
    }

    private function addAuthors(Paper $paper, User $user, array $entry, Country $country, Price $price): void
    {
        $paper->authors()->create([
            'name' => $entry['name'],
            'email' => $user->email,
            'designation' => $entry['designation'],
            'department' => $entry['department'],
            'institution' => $entry['institution'],
            'country_id' => $country->id,
            'price_id' => $price->id,
            'is_student' => false,
            'is_presenting_author' => true,
            'author_order' => 1,
        ]);

        $order = 2;
        foreach ($entry['co_authors'] as $co) {
            $coCountry = Country::whereRaw('LOWER(name) = ?', [Str::lower($co['country'])])->first();
            $coPrice = Price::where('category', $co['category'])->first();

            $paper->authors()->create([
                'name' => $co['name'],
                'email' => Str::slug($co['name']) . '@' . self::DEMO_DOMAIN,
                'designation' => $co['designation'],
                'department' => $entry['department'],
                'institution' => $co['institution'],
                'country_id' => $coCountry?->id ?? $country->id,
                'price_id' => $coPrice?->id ?? $price->id,
                'is_student' => $co['student'],
                'is_presenting_author' => false,
                'author_order' => $order++,
            ]);
        }
    }

    private function attachManuscript(Paper $paper, array $entry): void
    {
        $name = Str::slug($entry['title']) . '.pdf';
        $path = 'manuscripts/' . $paper->id . '/' . Str::random(40) . '.pdf';

        Storage::put($path, $this->stubPdf($entry['title']));

        $paper->update([
            'manuscript_path' => $path,
            'manuscript_original_name' => $name,
            'manuscript_uploaded_at' => now()->subDays(rand(1, 10)),
            'manuscript_status' => 'submitted',
        ]);
    }

    private function declareConflict(Paper $paper, User $user, array $conflict): void
    {
        $against = null;

        if (!empty($conflict['chair_of_track'])) {
            $against = TrackAssignment::where('track_id', $paper->track_id)
                ->where('role', 'chair')
                ->value('user_id');
        }

        PaperConflict::create([
            'paper_id' => $paper->id,
            'declared_by_user_id' => $user->id,
            'conflicted_user_id' => $against,
            'note' => $conflict['note'],
        ]);
    }

    /** A small but openable PDF, so the download button does something real. */
    private function stubPdf(string $title): string
    {
        $text = 'ICMRIA 2027 - demonstration manuscript';
        $body = "BT /F1 13 Tf 62 720 Td ({$text}) Tj ET";

        $objects = [
            "<</Type/Catalog/Pages 2 0 R>>",
            "<</Type/Pages/Kids[3 0 R]/Count 1>>",
            "<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>",
            "<</Length " . strlen($body) . ">>\nstream\n{$body}\nendstream",
            "<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<</Size " . (count($objects) + 1) . "/Root 1 0 R>>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    /** @return array<int, array<string, mixed>> */
    private function submissions(): array
    {
        return [
            [
                'name' => 'Tanvir Ahmed Khan',
                'designation' => 'Associate Professor', 'department' => 'Computer Science and Engineering',
                'institution' => 'Daffodil International University', 'country' => 'Bangladesh',
                'category' => 'academic', 'phone' => '01710000001', 'mode' => 'onsite',
                'paid' => true, 'status' => 'approved', 'manuscript' => true, 'conflict' => null,
                'sub_track' => 'Machine Learning, Deep Learning',
                'title' => 'Attention-Guided Deep Networks for Bangla Handwritten Character Recognition',
                'keywords' => 'deep learning, machine learning, computer vision, Bangla OCR',
                'abstract' => $this->abstract('bangla_ocr'),
                'co_authors' => [
                    ['name' => 'Sumaiya Rahman', 'designation' => 'MSc Student', 'institution' => 'Daffodil International University',
                     'country' => 'Bangladesh', 'category' => 'student', 'student' => true],
                    ['name' => 'Nazmul Hasan', 'designation' => 'Lecturer', 'institution' => 'Daffodil International University',
                     'country' => 'Bangladesh', 'category' => 'academic', 'student' => false],
                ],
            ],
            [
                'name' => 'Rumana Akter',
                'designation' => 'MPhil Student', 'department' => 'Public Health',
                'institution' => 'Daffodil International University', 'country' => 'Bangladesh',
                'category' => 'student', 'phone' => '01710000002', 'mode' => 'onsite',
                'paid' => false, 'status' => 'pending', 'manuscript' => false, 'conflict' => null,
                'sub_track' => 'Public Health Interventions',
                'title' => 'Community Health Worker Outreach and Childhood Immunisation Coverage in Rural Bangladesh',
                'keywords' => 'public health, epidemiology, immunisation, healthcare systems',
                'abstract' => $this->abstract('immunisation'),
                'co_authors' => [],
            ],
            [
                'name' => 'Shahriar Kabir',
                'designation' => 'R&D Engineer', 'department' => 'Electrical and Electronic Engineering',
                'institution' => 'Energypac Engineering Ltd.', 'country' => 'Bangladesh',
                'category' => 'industry', 'phone' => '01710000003', 'mode' => 'onsite',
                'paid' => false, 'status' => 'pending', 'manuscript' => true,
                'conflict' => ['chair_of_track' => true, 'note' => 'Former doctoral supervisor'],
                'sub_track' => 'Electrical & Electronic Engineering',
                'title' => 'Adaptive Droop Control for Islanded Microgrids with High Renewable Penetration',
                'keywords' => 'smart grids, power systems, renewable energy, robotics',
                'abstract' => $this->abstract('microgrid'),
                'co_authors' => [
                    ['name' => 'Farhana Islam', 'designation' => 'Senior Engineer', 'institution' => 'Energypac Engineering Ltd.',
                     'country' => 'Bangladesh', 'category' => 'industry', 'student' => false],
                ],
            ],
            [
                'name' => 'Arindam Chatterjee',
                'designation' => 'Assistant Professor', 'department' => 'Environmental Science',
                'institution' => 'Jadavpur University', 'country' => 'India',
                'category' => 'saarc', 'phone' => '01710000004', 'mode' => 'online',
                'paid' => true, 'status' => 'approved', 'manuscript' => true, 'conflict' => null,
                'sub_track' => 'Climate Change Mitigation',
                'title' => 'Coastal Salinity Intrusion and Adaptive Cropping Patterns in the Bengal Delta',
                'keywords' => 'climate change mitigation, adaptation, water resources, environmental dynamics',
                'abstract' => $this->abstract('salinity'),
                'co_authors' => [
                    ['name' => 'Priya Sengupta', 'designation' => 'Research Fellow', 'institution' => 'Jadavpur University',
                     'country' => 'India', 'category' => 'saarc', 'student' => false],
                ],
            ],
            [
                'name' => 'Lukas Brandt',
                'designation' => 'Professor', 'department' => 'Business Administration',
                'institution' => 'University of Bremen', 'country' => 'Germany',
                'category' => 'international', 'phone' => '01710000005', 'mode' => 'online',
                'paid' => false, 'status' => 'pending', 'manuscript' => false, 'conflict' => null,
                'sub_track' => 'Digital Transformation',
                'title' => 'Platform Adoption Among Small Manufacturers: Evidence from Two European Regions',
                'keywords' => 'digital transformation, e-commerce, entrepreneurship, supply chain',
                'abstract' => $this->abstract('platform'),
                'co_authors' => [
                    ['name' => 'Marta Nowak', 'designation' => 'Postdoctoral Researcher', 'institution' => 'University of Bremen',
                     'country' => 'Germany', 'category' => 'international', 'student' => false],
                    ['name' => 'Imran Chowdhury', 'designation' => 'Visiting Scholar', 'institution' => 'University of Bremen',
                     'country' => 'Bangladesh', 'category' => 'academic', 'student' => false],
                ],
            ],
        ];
    }

    /** Abstracts sized to the 200-250 word rule the conference sets. */
    private function abstract(string $key): string
    {
        $texts = [
            'bangla_ocr' => 'Handwritten character recognition for Bangla remains considerably harder than for Latin
                scripts, because the alphabet is large, compound characters are frequent, and the same grapheme varies
                widely between writers. This paper presents an attention-guided convolutional architecture that learns
                to weight the discriminative regions of a character image rather than treating every pixel equally. We
                assembled a corpus of ninety-two thousand handwritten samples collected from four hundred writers across
                six districts, deliberately including elderly and semi-literate contributors whose handwriting is poorly
                represented in existing datasets. The proposed network couples a lightweight residual backbone with a
                spatial attention module, and is trained with a curriculum that introduces compound characters only once
                basic graphemes are learned reliably. On a held-out set of eleven thousand samples the model reaches
                ninety-six point four percent accuracy, improving on a comparable residual baseline by three point one
                points, and the gain is largest precisely on the compound characters that dominate recognition errors in
                practice. Attention maps show the network concentrating on the junction strokes that distinguish visually
                similar pairs, which offers a degree of interpretability that pure classification scores do not. We also
                report inference timings on a mid-range mobile processor, since the intended application is offline form
                digitisation in rural administrative offices where connectivity cannot be assumed. Remaining errors
                cluster around a small set of genuinely ambiguous shapes, suggesting that further progress depends more
                on corpus design than on architectural change.',

            'immunisation' => 'Childhood immunisation coverage in Bangladesh has improved markedly over three decades,
                yet district-level figures conceal pockets where completion of the full schedule remains well below the
                national average. This study examines whether the frequency and form of community health worker contact
                explains that variation, drawing on a survey of one thousand four hundred households across nine rural
                unions. Caregivers were asked about each contact with a health worker during the child first year,
                distinguishing scheduled home visits, opportunistic contact at satellite clinics, and telephone reminders.
                Immunisation records were verified against retained cards wherever possible rather than relying on recall
                alone, and unverified responses are reported separately. Completion was strongly associated with the
                number of home visits, but the relationship flattened after three visits, suggesting that additional
                contact adds little once a household is already engaged. Telephone reminders showed a smaller but
                independent association, and were notably more effective among households where the mother had completed
                secondary education. Distance to the nearest satellite clinic mattered less than expected once visit
                frequency was controlled for. We interpret these findings as an argument for redistributing worker time
                towards households that have received no visit at all, rather than increasing contact uniformly. The
                study is limited by its cross-sectional design and by seasonal timing, since fieldwork concluded before
                the monsoon period when access difficulties are greatest.',

            'microgrid' => 'Islanded microgrids with a high share of solar and wind generation suffer voltage and
                frequency excursions that conventional fixed droop control handles poorly, because the droop
                coefficients that stabilise the system under one generation mix are unsuitable under another. This
                paper proposes an adaptive droop scheme in which coefficients are adjusted continuously from a local
                estimate of grid stiffness, requiring no communication between inverters. The estimator uses only
                measurements already available at the inverter terminals, which keeps the scheme deployable on existing
                hardware through a firmware change rather than replacement. We derive stability conditions for the
                adaptive law and show that the equilibrium remains locally stable across the full range of coefficients
                the estimator can produce. The controller was evaluated first in simulation against a fixed-droop
                baseline and a centralised secondary controller, then on a laboratory microgrid comprising three
                inverters, a programmable solar emulator and a resistive-inductive load bank. Under a step loss of
                forty percent of generation the adaptive scheme restored frequency to within acceptable limits in less
                than half the time taken by the fixed baseline, and without the communication link the centralised
                controller depends on. Voltage overshoot was reduced correspondingly. We discuss the sensitivity of the
                estimator to measurement noise, which sets a practical lower bound on how aggressively the coefficients
                can be adapted in field conditions.',

            'salinity' => 'Salinity intrusion along the Bengal delta has advanced inland over the past two decades,
                driven by reduced dry-season flow, embankment failure and rising sea level. This paper examines how
                farming households in three coastal blocks have adjusted their cropping patterns in response, and
                whether those adjustments amount to durable adaptation or to a slower form of retreat. We combine soil
                salinity measurements taken at the same one hundred and twenty sampling points across four years with
                household interviews covering crop choice, input use and income. Salinity rose at most points, but the
                rate varied sharply with distance from breached embankment sections rather than with distance from the
                coast, which complicates the assumption that intrusion advances as a smooth front. Households responded
                mainly by shifting from traditional rice to saline-tolerant varieties and, where water retention allowed,
                by adding a brackish aquaculture cycle. Both responses sustained income in the short term, but
                aquaculture was associated with further soil deterioration on adjacent plots, producing a pattern in
                which one household adaptation raises the burden on its neighbours. We argue that interventions framed
                at the household level will underperform where this externality operates, and that embankment repair
                and coordinated water management deserve priority over varietal distribution alone. Measurement
                limitations arising from seasonal sampling are discussed.',

            'platform' => 'Digital platforms are widely expected to lower the cost at which small manufacturers reach
                distant customers, yet adoption among firms below fifty employees remains uneven even in regions with
                good connectivity. This paper asks what distinguishes the small manufacturers that adopt platform
                channels from those that do not, using survey and interview evidence from two European regions with
                comparable infrastructure but different industrial histories. We surveyed three hundred and ten firms
                and conducted follow-up interviews with twenty-eight. Adoption was only weakly related to firm size or
                to the owner familiarity with digital tools, both of which feature prominently in existing accounts.
                The stronger association was with whether the firm already sold to customers it had never met in
                person, since those firms had established the quality assurance and dispute handling practices that
                platform selling demands. Firms whose existing trade depended on long-standing personal relationships
                found platform channels not merely unfamiliar but actively corrosive of the trust their business rested
                on, and several had adopted and then withdrawn. We argue that adoption is better understood as a change
                in how a firm establishes trust than as a technology decision, which has implications for support
                programmes that concentrate on training and subsidised onboarding. The two-region design limits
                generalisation, and we identify the sectoral composition differences that most plausibly condition the
                findings.',
        ];

        return preg_replace('/\s+/', ' ', trim($texts[$key]));
    }
}
