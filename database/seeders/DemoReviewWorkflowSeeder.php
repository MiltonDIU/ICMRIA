<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Paper;
use App\Models\PaperEvaluation;
use App\Models\PaperManuscriptVersion;
use App\Models\PaperReviewerAssignment;
use App\Models\Price;
use App\Models\Profile;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\IdGeneratorService;
use App\Services\PricingService;
use App\Services\ReviewerMatcher;
use App\Services\SubmissionRules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Thirty papers part-way through review, so the reviewer, chair and TPC screens can be
 * seen with realistic volume (requirement document, Phases 3 to 5).
 *
 * Every paper has an approved abstract and an anonymised manuscript, and none is paid.
 * The papers are created in a random order, so submission IDs do not follow the tracks,
 * and how far review has got is dealt out at random while the totals stay fixed:
 *
 *   - 10 complete: all three reviewers have submitted.
 *   - 5 half done: four reviewers, two submitted, the other two not yet.
 *   - 15 not started: three reviewers, none submitted.
 *
 * Reviewers are drawn at random from those ReviewerMatcher allows for the paper, so the
 * track's pool, authorship, declared conflicts and workload limits all still hold; a
 * closer keyword match only makes a reviewer likelier to be picked. Recommendations,
 * scores, feedback, answers to the invitation and dates are random as well, so every
 * run gives a different mix, disagreements included. Set DEMO_SEED to repeat a run.
 *
 * No decisions are seeded; those are the chairs' to make on top of this data.
 *
 * Authors sit on @demo.icmria.com and sign in with "password". Needs the tracks, prices
 * and a reviewer pool (ReviewerSeeder or ReviewerPoolSeeder) first. Re-running adds
 * nothing twice, and the seeder refuses to run in production.
 */
class DemoReviewWorkflowSeeder extends Seeder
{
    private const DEMO_DOMAIN = 'demo.icmria.com';
    private const ROLE_AUTHOR = 3;

    /** Scores that go with each recommendation, before the per-reviewer variation. */
    private const BASE_SCORE = ['strong_accept' => 5, 'accept' => 4, 'borderline' => 3, 'reject' => 2, 'strong_reject' => 1];

    /** How often each recommendation is drawn: most submissions are reasonable, few are dismissed outright. */
    private const RECOMMENDATION_WEIGHTS = ['strong_accept' => 15, 'accept' => 35, 'borderline' => 25, 'reject' => 18, 'strong_reject' => 7];

    /** How the review states are dealt out across the papers. */
    private const STATE_COUNTS = ['complete' => 10, 'half' => 5, 'none' => 15];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoReviewWorkflowSeeder skipped: never seed demonstration papers in production.');
            return;
        }

        if (Track::count() === 0 || Price::count() === 0) {
            $this->command?->warn('Run the track and price seeders first.');
            return;
        }

        if (!TrackAssignment::where('role', 'reviewer')->exists()) {
            $this->command?->warn('No reviewer pool. Run ReviewerSeeder or ReviewerPoolSeeder first.');
            return;
        }

        if ($seed = env('DEMO_SEED')) {
            mt_srand((int) $seed);
        }

        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get()->values();
        $password = Hash::make('password');
        $matcher = app(ReviewerMatcher::class);
        $created = 0;
        $assigned = 0;
        $short = [];

        $papers = $this->shuffled($this->papers());
        $states = $this->shuffled($this->states(count($papers)));

        foreach ($papers as $index => $entry) {
            $entry['state'] = $states[$index];
            $track = $tracks[$entry['at'][0] - 1] ?? null;
            $subTrack = $track?->subTracks[$entry['at'][1] - 1] ?? null;

            if (!$subTrack) {
                $this->command?->warn("Skipping \"{$entry['title']}\": track {$entry['at'][0]}, sub-track {$entry['at'][1]} not found.");
                continue;
            }

            DB::transaction(function () use ($entry, $subTrack, $password, $matcher, &$created, &$assigned, &$short) {
                [$paper, $isNew] = $this->paperFor($entry, $subTrack, $password);
                $created += $isNew ? 1 : 0;

                if ($paper->reviewerAssignments()->exists()) {
                    return;
                }

                $added = $this->review($paper, $entry, $matcher);
                $assigned += $added;

                if ($added < $this->reviewersFor($entry)) {
                    $short[] = $paper->submission_id;
                }
            });
        }

        $this->command?->info("Review workflow demo: {$created} paper(s) created, {$assigned} reviewer assignment(s) made.");

        if ($short) {
            $this->command?->warn('The reviewer pool could not fully staff: ' . implode(', ', $short));
        }
    }

    /** @return array{0: Paper, 1: bool} the paper and whether it was created now */
    private function paperFor(array $entry, $subTrack, string $password): array
    {
        $country = Country::whereRaw('LOWER(name) = ?', [Str::lower($entry['country'])])->first();
        $price = Price::where('category', $entry['category'])->first();

        $user = User::firstOrCreate(['email' => Str::slug($entry['name']) . '@' . self::DEMO_DOMAIN], [
            'name' => $entry['name'],
            'password' => $password,
            'email_verified_at' => now(),
        ]);
        $user->roles()->syncWithoutDetaching([self::ROLE_AUTHOR]);

        $profile = Profile::firstOrNew(['user_id' => $user->id]);
        if (!$profile->exists) {
            $profile->fill([
                'first_name' => Str::beforeLast($entry['name'], ' '),
                'last_name' => Str::afterLast($entry['name'], ' '),
                'designation' => $entry['designation'],
                'department' => $entry['department'],
                'institution' => $entry['institution'],
                'country_id' => $country?->id,
                'price_id' => $price?->id,
                'registration_id' => IdGeneratorService::generateRegistrationId(),
                'whatsapp_number' => '0171' . str_pad((string) (crc32($entry['name']) % 10000000), 7, '0', STR_PAD_LEFT),
                'is_author' => 1,
                'participation_mode' => $entry['mode'],
                'payment_status' => '0',
            ])->save();
        }

        $paper = Paper::where('user_id', $user->id)->where('title', $entry['title'])->first();

        if ($paper) {
            return [$paper, false];
        }

        $keywords = SubmissionRules::splitKeywords($entry['keywords']);

        $paper = Paper::create([
            'user_id' => $user->id,
            'submission_id' => IdGeneratorService::generateSubmissionId(),
            'title' => $entry['title'],
            'abstract' => $this->abstractFor($entry, $keywords),
            'keywords' => $keywords,
            'track_id' => $subTrack->track_id,
            'sub_track_id' => $subTrack->id,
            'mode_of_participation' => $entry['mode'],
            'is_corresponding_author' => 1,
            'has_multiple_authors' => count($entry['co']) > 0,
            'status' => 'approved',
            'payment_status' => '0',
        ]);

        $paper->authors()->create([
            'name' => $entry['name'],
            'email' => $user->email,
            'designation' => $entry['designation'],
            'department' => $entry['department'],
            'institution' => $entry['institution'],
            'country_id' => $country?->id,
            'price_id' => $price?->id,
            'is_student' => $entry['category'] === 'student',
            'is_presenting_author' => true,
            'author_order' => 1,
        ]);

        foreach ($entry['co'] as $order => [$name, $designation, $institution, $coCountry, $category]) {
            $paper->authors()->create([
                'name' => $name,
                'email' => Str::slug($name) . '@' . self::DEMO_DOMAIN,
                'designation' => $designation,
                'department' => $entry['department'],
                'institution' => $institution,
                'country_id' => Country::whereRaw('LOWER(name) = ?', [Str::lower($coCountry)])->value('id') ?? $country?->id,
                'price_id' => Price::where('category', $category)->value('id') ?? $price?->id,
                'is_student' => $category === 'student',
                'is_presenting_author' => false,
                'author_order' => $order + 2,
            ]);
        }

        $this->attachManuscript($paper, $user);
        PricingService::updateProfileTotalDue($profile->fresh());

        return [$paper, true];
    }

    private function reviewersFor(array $entry): int
    {
        return $entry['state'] === 'half' ? 4 : 3;
    }

    /**
     * Assigns reviewers and sets how far each has got: all of them submitted for a
     * complete paper, half of them for a half-done one, none otherwise. Those who have
     * not submitted are left invited, accepted or part-way through a draft, at random.
     *
     * @return int how many reviewers were assigned
     */
    private function review(Paper $paper, array $entry, ReviewerMatcher $matcher): int
    {
        $paper->load(['track', 'authors', 'conflicts', 'bids', 'reviewerAssignments']);

        $picked = $this->pickReviewers($matcher->eligibleFor($paper), $this->reviewersFor($entry));
        $keywords = SubmissionRules::splitKeywords($paper->keywords);
        $version = PaperManuscriptVersion::where('paper_id', $paper->id)->max('version');

        $submitters = [
            'complete' => $picked->count(),
            'half' => intdiv($picked->count(), 2),
            'none' => 0,
        ][$entry['state']];

        foreach ($picked as $slot => $candidate) {
            $status = $slot < $submitters
                ? 'completed'
                : $this->weighted(['invited' => 40, 'accepted' => 30, 'in_progress' => 30]);
            $assignedAt = now()->subDays(mt_rand(10, 21))->subHours(mt_rand(0, 23));

            $assignment = PaperReviewerAssignment::create([
                'paper_id' => $paper->id,
                'reviewer_id' => $candidate['reviewer']->id,
                'assigned_by' => null,
                'assignment_source' => 'auto',
                'status' => $status,
                'match_score' => $candidate['score'],
                'assigned_at' => $assignedAt,
                'responded_at' => $status === 'invited' ? null : $assignedAt->copy()->addDays(mt_rand(1, 4)),
            ]);

            if ($status === 'completed') {
                $this->submitEvaluation($assignment, $this->weighted(self::RECOMMENDATION_WEIGHTS), $keywords, $version);
            } elseif ($status === 'in_progress') {
                $this->draftEvaluation($assignment, $keywords);
            }
        }

        return $picked->count();
    }

    /**
     * Draws reviewers at random, without replacement, from those the matcher allows.
     * Each is weighted by keyword match plus one, so a close match is likelier but no
     * eligible reviewer is ruled out.
     */
    private function pickReviewers(Collection $eligible, int $howMany): Collection
    {
        $pool = $eligible->values()->all();
        $picked = [];

        while (count($picked) < $howMany && $pool) {
            $roll = mt_rand(1, array_sum(array_map(fn ($candidate) => $candidate['score'] + 1, $pool)));

            foreach ($pool as $i => $candidate) {
                $roll -= $candidate['score'] + 1;
                if ($roll <= 0) {
                    $picked[] = $candidate;
                    array_splice($pool, $i, 1);
                    break;
                }
            }
        }

        return collect($picked);
    }

    /** @param array<string, int> $weights value => relative weight */
    private function weighted(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $value => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $value;
            }
        }

        return array_key_last($weights);
    }

    /** @return array<int, string> one review state per paper, in the proportions STATE_COUNTS sets */
    private function states(int $total): array
    {
        $states = [];
        foreach (self::STATE_COUNTS as $state => $count) {
            array_push($states, ...array_fill(0, $count, $state));
        }

        return array_slice(array_pad($states, $total, 'none'), 0, $total);
    }

    /** Fisher-Yates on mt_rand, so DEMO_SEED makes the order repeatable. */
    private function shuffled(array $items): array
    {
        $items = array_values($items);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }

    private function submitEvaluation(PaperReviewerAssignment $assignment, string $recommendation, array $keywords, ?int $version): void
    {
        $base = self::BASE_SCORE[$recommendation];
        // Scores sit around the recommendation, but a reviewer rarely rates every criterion alike.
        $score = fn (int $lift = 0) => max(1, min(5, $base + $lift + mt_rand(-1, 1)));

        PaperEvaluation::create([
            'assignment_id' => $assignment->id,
            'paper_id' => $assignment->paper_id,
            'reviewer_id' => $assignment->reviewer_id,
            'originality' => $score(),
            'soundness' => $score(),
            // Papers were routed to a matching sub-track, so relevance tends to run higher.
            'relevance' => $score(1),
            'recommendation' => $recommendation,
            'feedback_for_authors' => $this->feedback($recommendation, $keywords),
            'confidential_comments' => $this->confidential($recommendation, $keywords),
            'manuscript_version' => $version,
            'submitted_at' => now()->subDays(mt_rand(0, 5))->subHours(mt_rand(0, 12)),
        ]);
    }

    /** An unfinished draft: a score or two and a first note, not submitted. */
    private function draftEvaluation(PaperReviewerAssignment $assignment, array $keywords): void
    {
        PaperEvaluation::create([
            'assignment_id' => $assignment->id,
            'paper_id' => $assignment->paper_id,
            'reviewer_id' => $assignment->reviewer_id,
            'originality' => mt_rand(2, 5),
            'soundness' => mt_rand(0, 1) ? mt_rand(2, 5) : null,
            'feedback_for_authors' => 'Initial notes: the treatment of ' . ($keywords[0] ?? 'the topic') . ' looks promising; the evaluation section still needs a careful read.',
        ]);
    }

    private function feedback(string $recommendation, array $keywords): string
    {
        [$first, $second] = [$keywords[0] ?? 'the topic', $keywords[1] ?? 'related areas'];

        $variants = [
            'strong_accept' => [
                "The paper makes a clear, well-evidenced contribution on {$first}. The method is described in enough detail to replicate, and the discussion of limitations is candid. Minor points only: tighten the related-work section and make the figure captions consistent.",
                "An excellent paper. The problem is well motivated, the analysis of {$first} is rigorous, and the findings are useful beyond the immediate setting. I would only ask the authors to state the limitations of the data more prominently and to share the analysis scripts if they can.",
            ],
            'accept' => [
                "A solid study of {$first} with a sensible design, and the results support the main claims. The comparison with prior work on {$second} could be sharper. Please explain how the sample was selected and report confidence intervals for the main results.",
                "The paper is well organised and the contribution on {$first} is clear. The discussion would benefit from engaging more directly with recent studies on {$second}, and a short robustness check would make the central result more convincing. These are revisions rather than obstacles.",
            ],
            'borderline' => [
                "The topic is relevant and the data are interesting, but the contribution beyond existing work on {$first} is not yet clearly argued. The evaluation rests on a single setting, so how far the findings generalise is uncertain. A stronger baseline and an explicit statement of novelty would help.",
                "There is a useful idea here, but the manuscript reads more like a project report than a research paper. The link between the results and the claims about {$first} needs to be made explicit, and the methods section should justify the main design choices. With that work it could be a reasonable contribution.",
            ],
            'reject' => [
                "The question is worthwhile, but the manuscript does not yet support its conclusions. The evaluation of {$first} lacks an appropriate baseline, key parameters are not reported, and several claims in the discussion go beyond what the results show. A substantial revision would be needed.",
                "I could not follow how the results on {$first} were obtained: the data preparation is only sketched, there is no comparison with existing approaches to {$second}, and the conclusions are stated more strongly than the evidence allows. A thorough reworking is needed before this is ready.",
            ],
            'strong_reject' => [
                "The conclusions are not supported by the evidence presented. The approach to {$first} is not described in enough detail to assess, the dataset is too small for the claims made, and much of the material overlaps with earlier published work. The paper is not ready for the conference.",
                "The paper does not engage with the substantial existing literature on {$first}, and the main result appears to follow from how the evaluation was set up rather than from the proposed approach. I do not see a contribution the conference could accept in its current form.",
            ],
        ][$recommendation];

        return $variants[mt_rand(0, count($variants) - 1)];
    }

    /** A note for the chairs now and then, more often when the reviewer is recommending rejection. */
    private function confidential(string $recommendation, array $keywords): ?string
    {
        $chance = in_array($recommendation, ['reject', 'strong_reject'], true) ? 45 : 25;

        if (mt_rand(1, 100) > $chance) {
            return null;
        }

        $notes = [
            'I am less familiar with ' . ($keywords[1] ?? 'this area') . ', so my confidence in the methodological assessment is moderate.',
            'Parts of the experimental section closely resemble a preprint I have seen; the chairs may wish to check for overlap.',
            'The writing would benefit from careful language editing before publication.',
            'I would be glad to look at a revised version if the chairs think that useful.',
        ];

        return $notes[mt_rand(0, count($notes) - 1)];
    }

    private function attachManuscript(Paper $paper, User $user): void
    {
        $content = $this->stubPdf($paper->submission_id, $paper->title);
        $path = 'manuscripts/' . $paper->id . '/' . Str::random(40) . '.pdf';
        $name = Str::slug(Str::limit($paper->title, 60, '')) . '.pdf';
        $uploadedAt = now()->subDays(mt_rand(22, 30));

        Storage::put($path, $content);

        $paper->update([
            'manuscript_path' => $path,
            'manuscript_original_name' => $name,
            'manuscript_uploaded_at' => $uploadedAt,
            'manuscript_status' => 'submitted',
        ]);

        PaperManuscriptVersion::create([
            'paper_id' => $paper->id,
            'uploaded_by' => $user->id,
            'version' => 1,
            'path' => $path,
            'original_name' => $name,
            'size' => strlen($content),
            'anonymity_confirmed' => true,
        ]);
    }

    /** A small but openable, anonymised PDF, so the download buttons do something real. */
    private function stubPdf(string $submissionId, string $title): string
    {
        $escape = fn (string $text) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $lines = array_merge(
            ['ICMRIA 2027 - anonymised manuscript (demonstration)', $submissionId, ''],
            explode("\n", wordwrap($title, 70))
        );

        $body = "BT /F1 12 Tf 62 760 Td 16 TL\n";
        foreach ($lines as $line) {
            $body .= '(' . $escape($line) . ") Tj T*\n";
        }
        $body .= 'ET';

        $objects = [
            '<</Type/Catalog/Pages 2 0 R>>',
            '<</Type/Pages/Kids[3 0 R]/Count 1>>',
            '<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>',
            '<</Length ' . strlen($body) . ">>\nstream\n{$body}\nendstream",
            '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>',
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

        return $pdf . "trailer\n<</Size " . (count($objects) + 1) . "/Root 1 0 R>>\nstartxref\n{$xref}\n%%EOF";
    }

    /**
     * A 200-250 word abstract in the conference's structure: problem, gap, approach,
     * data, findings, implications. Built from the paper's own details so each reads
     * differently, and topped up to the conference minimum where it falls short.
     */
    private function abstractFor(array $entry, array $keywords): string
    {
        [$k1, $k2, $k3, $k4] = array_pad($keywords, 4, 'the topic');

        $sentences = [
            ucfirst($entry['context']) . ' is a pressing concern, yet the evidence available to practitioners and policy makers remains fragmented.',
            "Existing work on {$k1} and {$k2} reports encouraging results in controlled settings, but evidence from real deployments in low- and middle-income contexts is thin, and the conditions under which reported gains hold are rarely examined.",
            "This paper addresses that gap by combining {$entry['method']} with a structured comparison against established baselines.",
            "The study draws on {$entry['data']}, and the data collection, preprocessing and evaluation protocol are documented in enough detail to allow replication.",
            "The results show that {$entry['finding']}.",
            "The analysis further indicates that outcomes depend strongly on {$k3}, and that ignoring this dependency overstates the expected benefit by a considerable margin.",
            "We discuss the practical implications for {$k4}, set out the limitations that follow from the scope of the data, and identify further work that would test whether the findings transfer to other settings.",
        ];

        $padding = [
            'The contribution is positioned against recent literature, and the assumptions on which the conclusions rest are stated explicitly so that readers can judge where they apply.',
            'Robustness checks using alternative specifications and subsamples lead to the same substantive conclusions, which strengthens confidence in the main results.',
            'Taken together, the findings offer a grounded basis for decisions that are currently made with little local evidence.',
        ];

        $abstract = implode(' ', $sentences);
        foreach ($padding as $sentence) {
            if (SubmissionRules::countWords($abstract) >= SubmissionRules::abstractMinWords()) {
                break;
            }
            $abstract .= ' ' . $sentence;
        }

        return $abstract;
    }

    /**
     * The thirty papers, before shuffling. 'at' is [track number, sub-track position];
     * 'co' lists co-authors as [name, designation, institution, country, fee category].
     *
     * @return array<int, array<string, mixed>>
     */
    private function papers(): array
    {
        $diu = 'Daffodil International University';

        return [
            // Track 1: Artificial Intelligence, Data Science & Computing
            ['at' => [1, 1],
             'name' => 'Farzana Haque', 'designation' => 'Assistant Professor', 'department' => 'Computer Science and Engineering',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite',
             'co' => [['Rakibul Islam', 'MSc Student', $diu, 'Bangladesh', 'student']],
             'title' => 'Lightweight Transformer Models for Crop Disease Detection on Low-Cost Smartphones',
             'keywords' => 'deep learning, machine learning, model compression, mobile inference',
             'context' => 'early detection of crop disease by smallholder farmers', 'method' => 'knowledge distillation and post-training quantisation of vision transformers',
             'data' => 'eighteen thousand field photographs of rice and potato leaves collected across four districts', 'finding' => 'a compressed model under ten megabytes retains ninety-four percent of the full model accuracy while running offline on entry-level phones'],

            ['at' => [1, 1],
             'name' => 'Imran Hossain', 'designation' => 'Lecturer', 'department' => 'Software Engineering',
             'institution' => 'United International University', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Generative AI Assistance and Novice Programmer Error Patterns: A Controlled Classroom Study',
             'keywords' => 'generative ai, machine learning, programming education, learning analytics',
             'context' => 'the growing use of AI coding assistants by first-year students', 'method' => 'a randomised classroom experiment with automated analysis of compiler errors',
             'data' => 'two hundred and forty students across two introductory programming courses', 'finding' => 'assisted students fix syntax errors faster but repeat logical errors more often in unassisted follow-up tasks'],

            ['at' => [1, 2],
             'name' => 'Nusrat Sultana', 'designation' => 'Senior Data Scientist', 'department' => 'Data Science',
             'institution' => 'Dhaka Fintech Research Centre', 'country' => 'Bangladesh', 'category' => 'industry', 'mode' => 'onsite', 'co' => [],
             'title' => 'Mining Mobile Financial Service Transaction Logs for Early Fraud Signals',
             'keywords' => 'data mining, big data analytics, anomaly detection, mobile financial services',
             'context' => 'fraud in mobile financial services used by low-income customers', 'method' => 'sequential pattern mining combined with graph-based anomaly scores',
             'data' => 'eleven million anonymised transactions spanning six months', 'finding' => 'agent-level transaction bursts precede confirmed fraud cases by an average of nine days'],

            ['at' => [1, 3],
             'name' => 'Mahmudul Hasan', 'designation' => 'Associate Professor', 'department' => 'Computer Science and Engineering',
             'institution' => 'Khulna University of Engineering & Technology', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite',
             'co' => [['Sadia Afroz', 'Lecturer', 'Khulna University of Engineering & Technology', 'Bangladesh', 'academic']],
             'title' => 'Bangla Sign Language Recognition from Video Using Spatio-Temporal Pattern Recognition',
             'keywords' => 'computer vision, pattern recognition, sign language, video classification',
             'context' => 'communication access for deaf people who use Bangla Sign Language', 'method' => 'spatio-temporal graph networks over hand and body keypoints',
             'data' => 'a new corpus of four thousand video clips from thirty-two signers', 'finding' => 'keypoint-based models generalise to unseen signers far better than models trained on raw frames'],

            ['at' => [1, 3],
             'name' => 'Anika Tabassum', 'designation' => 'MSc Student', 'department' => 'Computer Science and Engineering',
             'institution' => 'BRAC University', 'country' => 'Bangladesh', 'category' => 'student', 'mode' => 'online', 'co' => [],
             'title' => 'Code-Mixed Bangla-English Sentiment Analysis for Customer Feedback',
             'keywords' => 'natural language processing, sentiment analysis, code-mixing, text classification',
             'context' => 'customer feedback written in a mix of Bangla and English', 'method' => 'multilingual transformer fine-tuning with transliteration-aware tokenisation',
             'data' => 'twenty-six thousand annotated reviews from e-commerce and telecom platforms', 'finding' => 'transliteration-aware tokenisation improves macro F1 by six points over standard multilingual baselines'],

            ['at' => [1, 4],
             'name' => 'Rajesh Kumar Sharma', 'designation' => 'Assistant Professor', 'department' => 'Computer Engineering',
             'institution' => 'Tribhuvan University', 'country' => 'Nepal', 'category' => 'saarc', 'mode' => 'online', 'co' => [],
             'title' => 'A Blockchain Ledger for Securing Firmware Updates in Edge IoT Devices',
             'keywords' => 'internet of things (iot), edge computing, cybersecurity, blockchain',
             'context' => 'tampering with firmware updates on low-power connected devices', 'method' => 'a permissioned ledger that records signed update manifests',
             'data' => 'a testbed of forty microcontroller nodes under simulated attack', 'finding' => 'the ledger detects all tampered updates in the testbed at the cost of a noticeable delay in update rollout'],

            // Track 2: Environment, Climate & Sustainability
            ['at' => [2, 1],
             'name' => 'Sabrina Chowdhury', 'designation' => 'Research Associate', 'department' => 'Environmental Science',
             'institution' => 'Independent University, Bangladesh', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Heat Stress Exposure of Informal Outdoor Workers in Dhaka Under Recent Warming Trends',
             'keywords' => 'climate change mitigation, adaptation, urban heat, occupational health',
             'context' => 'heat exposure among rickshaw pullers, vendors and construction labourers', 'method' => 'wearable temperature loggers paired with daily health diaries',
             'data' => 'one hundred and twenty workers followed through two pre-monsoon seasons', 'finding' => 'wet-bulb globe temperatures exceed safe work limits for more than five hours on a typical working day'],

            ['at' => [2, 2],
             'name' => 'Tahmid Rahman', 'designation' => 'Energy Consultant', 'department' => 'Sustainability Advisory',
             'institution' => 'Bangladesh Clean Energy Forum', 'country' => 'Bangladesh', 'category' => 'industry', 'mode' => 'onsite', 'co' => [],
             'title' => 'Rooftop Solar Adoption Among Garment Factories: Barriers Beyond Capital Cost',
             'keywords' => 'renewable energy systems, energy policy, green tech, industrial energy',
             'context' => 'slow rooftop solar uptake in export garment factories', 'method' => 'a mixed-methods study combining a factory survey with regulatory document analysis',
             'data' => 'one hundred and four factories and interviews with eighteen facility managers', 'finding' => 'net-metering uncertainty and building ownership arrangements deter adoption more than upfront cost'],

            ['at' => [2, 3],
             'name' => 'Dilruba Yasmin', 'designation' => 'Assistant Professor', 'department' => 'Environmental Science and Disaster Management',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite',
             'co' => [['Arif Mahmud', 'Lecturer', $diu, 'Bangladesh', 'academic']],
             'title' => 'Microplastic Loads in Peri-Urban Wetlands Receiving Untreated Wastewater',
             'keywords' => 'environmental risk assessment, water resources, waste management, microplastics',
             'context' => 'microplastic contamination of wetlands that support fisheries', 'method' => 'seasonal sediment and water sampling with spectroscopic polymer identification',
             'data' => 'sixty sampling points across three wetlands on the edge of Dhaka', 'finding' => 'microplastic concentrations near outfalls are roughly four times those at upstream reference sites'],

            ['at' => [2, 4],
             'name' => 'Kamrul Ahsan', 'designation' => 'Lecturer', 'department' => 'Urban and Regional Planning',
             'institution' => 'Chittagong University of Engineering & Technology', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Street Tree Diversity and Land Surface Temperature Across Chattogram Wards',
             'keywords' => 'sustainable urbanization, biodiversity conservation, smart cities, remote sensing',
             'context' => 'uneven tree cover and urban heat across city wards', 'method' => 'satellite-derived land surface temperature combined with ground tree inventories',
             'data' => 'forty-one wards surveyed over two summers', 'finding' => 'wards with higher species diversity, not only higher canopy cover, show the largest cooling effect'],

            // Track 3: Business, Management & Economics
            ['at' => [3, 1],
             'name' => 'Shirin Akhter', 'designation' => 'Associate Professor', 'department' => 'Business Administration',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Facebook Commerce and Women-Led Microenterprises in Bangladesh',
             'keywords' => 'e-commerce, entrepreneurship, digital transformation, social commerce',
             'context' => 'the rise of women-led businesses selling through social media', 'method' => 'a panel survey with in-depth interviews on growth and constraints',
             'data' => 'three hundred and twelve women entrepreneurs surveyed twice, one year apart', 'finding' => 'revenue growth is concentrated among sellers who move early from informal chat orders to structured payment and delivery arrangements'],

            ['at' => [3, 2],
             'name' => 'Harsha Wijesinghe', 'designation' => 'Senior Lecturer', 'department' => 'Finance',
             'institution' => 'University of Colombo', 'country' => 'Sri Lanka', 'category' => 'saarc', 'mode' => 'online', 'co' => [],
             'title' => 'Green Bond Pricing in South Asian Markets: Evidence of a Greenium?',
             'keywords' => 'sustainable finance, banking, green bonds, capital markets',
             'context' => 'whether investors in South Asia accept lower yields on green bonds', 'method' => 'matched-pair yield comparisons with issuer fixed effects',
             'data' => 'one hundred and thirty-six bonds issued in five South Asian markets', 'finding' => 'a small but significant premium appears only for bonds with independent external review'],

            ['at' => [3, 3],
             'name' => 'Mahbub Alam', 'designation' => 'Assistant Professor', 'department' => 'Management',
             'institution' => 'University of Dhaka', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite',
             'co' => [['Tasnim Ferdous', 'Research Assistant', 'University of Dhaka', 'Bangladesh', 'student']],
             'title' => 'Cold Chain Disruptions and Post-Harvest Losses in the Bangladesh Fish Supply Chain',
             'keywords' => 'supply chain, logistics, operations, cold chain',
             'context' => 'post-harvest losses of fish between landing sites and urban markets', 'method' => 'shipment tracking with temperature loggers and loss accounting at each handover',
             'data' => 'two hundred and eighty consignments traced from three landing centres', 'finding' => 'most losses occur during unrefrigerated waiting at wholesale transfer points rather than in transit'],

            ['at' => [3, 4],
             'name' => 'Fahim Shahriar', 'designation' => 'Lecturer', 'department' => 'Marketing',
             'institution' => 'North South University', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Online Reviews and Apartment Purchase Decisions Among First-Time Urban Buyers',
             'keywords' => 'consumer behavior, marketing analytics, real estate management, online reviews',
             'context' => 'how first-time buyers use online reviews when choosing apartments', 'method' => 'a discrete choice experiment with eye-tracking of listing pages',
             'data' => 'four hundred prospective buyers in Dhaka', 'finding' => 'negative reviews about developer delays outweigh favourable price information'],

            // Track 4: Engineering & Built Environment
            ['at' => [4, 1],
             'name' => 'Sanjida Afrin', 'designation' => 'Assistant Professor', 'department' => 'Electrical and Electronic Engineering',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Model Predictive Control of Grid-Tied Inverters Under Weak Grid Conditions',
             'keywords' => 'power systems, smart grids, inverter control, automation',
             'context' => 'instability of grid-tied inverters on weak rural distribution feeders', 'method' => 'finite-control-set model predictive control with online impedance estimation',
             'data' => 'hardware-in-the-loop experiments across a range of grid strengths', 'finding' => 'the controller keeps harmonic distortion below limits where conventional control becomes unstable'],

            ['at' => [4, 2],
             'name' => 'Rezaul Karim', 'designation' => 'Senior Manager, Research & Development', 'department' => 'Textile Engineering',
             'institution' => 'Meghna Denim Mills Ltd.', 'country' => 'Bangladesh', 'category' => 'industry', 'mode' => 'onsite', 'co' => [],
             'title' => 'Enzymatic Denim Washing to Reduce Water Use in Textile Finishing',
             'keywords' => 'textile chemical processing, sustainable apparel, water efficiency, denim',
             'context' => 'the heavy water footprint of denim finishing', 'method' => 'factorial trials of enzyme formulations against conventional stone washing',
             'data' => 'pilot production runs totalling nine thousand garments', 'finding' => 'enzymatic washing cuts water use by about forty percent with comparable fading and fabric strength'],

            ['at' => [4, 3],
             'name' => 'Aminul Islam', 'designation' => 'Associate Professor', 'department' => 'Civil Engineering',
             'institution' => 'Bangladesh University of Engineering and Technology', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Performance of Recycled Aggregate Concrete in Coastal Exposure Conditions',
             'keywords' => 'structural, construction management, infrastructure resilience, recycled aggregate',
             'context' => 'durability of concrete made with demolition waste in saline environments', 'method' => 'accelerated chloride exposure tests alongside field-exposed specimens',
             'data' => 'three hundred specimens across five replacement ratios', 'finding' => 'replacement up to thirty percent performs comparably to natural aggregate when supplementary binders are used'],

            ['at' => [4, 4],
             'name' => 'Nabila Rahman', 'designation' => 'Lecturer', 'department' => 'Architecture',
             'institution' => 'Ahsanullah University of Science and Technology', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Passive Cooling Strategies in Low-Income Housing: A Post-Occupancy Evaluation',
             'keywords' => 'sustainable building design, architecture, urban dynamics, thermal comfort',
             'context' => 'indoor overheating in low-income resettlement housing', 'method' => 'post-occupancy evaluation with indoor climate monitoring and resident interviews',
             'data' => 'sixty dwellings in two resettlement projects', 'finding' => 'cross-ventilation and roof shading reduce peak indoor temperatures more than wall insulation does'],

            ['at' => [4, 5],
             'name' => 'Wei Zhang', 'designation' => 'Associate Professor', 'department' => 'Electronic Engineering',
             'institution' => 'Nanjing University of Posts and Telecommunications', 'country' => 'China', 'category' => 'international', 'mode' => 'online', 'co' => [],
             'title' => 'Reconfigurable Intelligent Surfaces for Indoor 5G Coverage: A Measurement Study',
             'keywords' => 'wireless tech, telecommunications, signal processing, 5g',
             'context' => 'poor indoor coverage of millimetre-wave 5G signals', 'method' => 'prototype reconfigurable surfaces with measured channel responses',
             'data' => 'measurements in office, corridor and lecture hall environments', 'finding' => 'the surfaces raise received power in shadowed areas by up to twelve decibels'],

            // Track 5: Law, Humanities & Social Sciences
            ['at' => [5, 1],
             'name' => 'Tahsin Kabir', 'designation' => 'Assistant Professor', 'department' => 'Law',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Data Protection Rights of Gig Workers Under the Draft Personal Data Protection Framework',
             'keywords' => 'legal frameworks, human rights, governance, data protection',
             'context' => 'algorithmic management of platform-based gig workers', 'method' => 'doctrinal analysis of the draft framework against comparative case law',
             'data' => 'legislation and judgments from four jurisdictions and platform terms of service', 'finding' => 'the draft leaves automated work allocation decisions largely outside meaningful review'],

            ['at' => [5, 3],
             'name' => 'Ayesha Siddiqa', 'designation' => 'Research Fellow', 'department' => 'International Relations',
             'institution' => 'University of Peshawar', 'country' => 'Pakistan', 'category' => 'saarc', 'mode' => 'online', 'co' => [],
             'title' => 'Water Diplomacy on Shared Rivers: Revisiting Transboundary Negotiations in South Asia',
             'keywords' => 'international relations, public policy, conflict resolution, transboundary water',
             'context' => 'stalled negotiations over shared river waters', 'method' => 'process tracing of negotiation rounds using diplomatic records',
             'data' => 'official statements, treaty drafts and interviews with former negotiators', 'finding' => 'agreements advance when data-sharing arrangements are separated from allocation disputes'],

            ['at' => [5, 4],
             'name' => 'Rafiqul Bari', 'designation' => 'Deputy Director', 'department' => 'Rural Development',
             'institution' => 'Centre for Rural Livelihood Studies', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Social Safety Net Targeting Errors in Char Areas of Northern Bangladesh',
             'keywords' => 'social welfare, community development, public administration, poverty targeting',
             'context' => 'exclusion of eligible households from safety net programmes', 'method' => 'comparison of beneficiary lists against an independent household census',
             'data' => 'two thousand households in eleven char unions', 'finding' => 'exclusion errors are highest among households that migrate seasonally'],

            // Track 6: Health & Life Sciences
            ['at' => [6, 1],
             'name' => 'Sharmin Nahar', 'designation' => 'Assistant Professor', 'department' => 'Pharmacy',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'In Silico Screening of Medicinal Plant Compounds Against Dengue NS5 Polymerase',
             'keywords' => 'drug discovery, pharmaceutical sciences, molecular docking, dengue',
             'context' => 'the lack of approved antiviral treatment for dengue', 'method' => 'virtual screening with molecular docking and dynamics simulations',
             'data' => 'a library of one thousand four hundred phytochemicals', 'finding' => 'three flavonoids show stable binding at the polymerase active site over extended simulations'],

            ['at' => [6, 2],
             'name' => 'Tanzila Hoque', 'designation' => 'Research Officer', 'department' => 'Genetic Engineering and Biotechnology',
             'institution' => 'Jahangirnagar University', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Whole-Genome Sequencing of Multidrug-Resistant Klebsiella Isolates from Dhaka Hospitals',
             'keywords' => 'genomics, bioinformatics, antimicrobial resistance, molecular biology',
             'context' => 'the spread of multidrug-resistant hospital infections', 'method' => 'whole-genome sequencing with phylogenetic and resistance gene analysis',
             'data' => 'ninety-two clinical isolates from four tertiary hospitals', 'finding' => 'a single high-risk clone accounts for most carbapenem-resistant isolates across hospitals'],

            ['at' => [6, 4],
             'name' => 'Emily Carter', 'designation' => 'Lecturer', 'department' => 'Health Informatics',
             'institution' => 'University of Leeds', 'country' => 'United Kingdom', 'category' => 'international', 'mode' => 'online', 'co' => [],
             'title' => 'Uptake of Telemedicine Consultations Among Older Adults After the Pandemic',
             'keywords' => 'telemedicine, health technology, healthcare systems, older adults',
             'context' => 'continued use of remote consultations by older patients', 'method' => 'analysis of appointment records with a follow-up patient survey',
             'data' => 'fifty-eight thousand appointments across twelve primary care practices', 'finding' => 'uptake persists mainly for medication reviews and falls sharply for new symptoms'],

            // Track 7: Education, Language & Media
            ['at' => [7, 1],
             'name' => 'Shamima Nasrin', 'designation' => 'Associate Professor', 'department' => 'General Educational Development',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Outcome-Based Education Rubrics and Assessment Consistency in Engineering Capstone Projects',
             'keywords' => 'outcome-based education (obe), smart pedagogy, e-learning, assessment',
             'context' => 'inconsistent grading of capstone projects across supervisors', 'method' => 'inter-rater reliability analysis before and after rubric calibration',
             'data' => 'one hundred and forty capstone reports marked by twenty-two assessors', 'finding' => 'calibration workshops raise agreement substantially, but only for outcomes with observable evidence'],

            ['at' => [7, 2],
             'name' => 'Rukhsana Parvin', 'designation' => 'Lecturer', 'department' => 'English',
             'institution' => 'East West University', 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Teacher Code-Switching and Student Participation in Tertiary ELT Classrooms',
             'keywords' => 'applied linguistics, language teaching (elt), code-switching, classroom discourse',
             'context' => 'the use of Bangla by teachers in English language classrooms', 'method' => 'classroom discourse analysis of recorded lessons',
             'data' => 'forty-eight hours of lessons from six instructors', 'finding' => 'brief switches to Bangla for task instructions increase student turns in English'],

            ['at' => [7, 4],
             'name' => 'Jubayer Ahmed', 'designation' => 'Lecturer', 'department' => 'Journalism, Media and Communication',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Fact-Checking Reach on Social Media During Flood Emergencies in Sylhet',
             'keywords' => 'digital journalism, mass media, misinformation, strategic communication',
             'context' => 'rumours spreading on social media during floods', 'method' => 'engagement analysis of matched rumours and fact-checks',
             'data' => 'nine hundred posts collected during two flood events', 'finding' => 'fact-checks shared by local community pages reach far more people than those from national outlets'],

            // Track 8: Agriculture & Food Security
            ['at' => [8, 1],
             'name' => 'Hasibul Hasan', 'designation' => 'Assistant Professor', 'department' => 'Agricultural Science',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'academic', 'mode' => 'onsite', 'co' => [],
             'title' => 'Soil Moisture Sensor Networks for Irrigation Scheduling in Boro Rice',
             'keywords' => 'precision farming, iot in farming, smart agriculture, irrigation',
             'context' => 'groundwater over-extraction for dry-season rice', 'method' => 'low-cost sensor networks driving alternate wetting and drying schedules',
             'data' => 'thirty farmer fields over two Boro seasons', 'finding' => 'sensor-guided irrigation saves about a quarter of pumped water without loss of yield'],

            ['at' => [8, 2],
             'name' => 'Lamia Rahman', 'designation' => 'MSc Student', 'department' => 'Nutrition and Food Engineering',
             'institution' => $diu, 'country' => 'Bangladesh', 'category' => 'student', 'mode' => 'onsite', 'co' => [],
             'title' => 'Formalin and Pesticide Residues in Retail Vegetables Across Dhaka Markets',
             'keywords' => 'food safety, quality assurance, food security, pesticide residues',
             'context' => 'chemical residues in vegetables sold in city markets', 'method' => 'chromatographic residue analysis of market samples',
             'data' => 'four hundred and twenty samples from fourteen markets', 'finding' => 'pesticide residues above permitted limits are more common than the formalin contamination that dominates public concern'],
        ];
    }
}
