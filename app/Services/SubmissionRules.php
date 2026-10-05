<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Abstract and keyword limits from the requirement document's Author Guidelines:
 * abstracts of 200-250 words, 4-6 keywords.
 *
 * These numbers used to be written out by hand in three validators and five Blade
 * templates, and every copy had drifted to the wrong figures. They live in the
 * settings table now so the organisers can adjust them, and everything reads them
 * from here so the form and the validator cannot disagree.
 */
class SubmissionRules
{
    public static function abstractMinWords(): int
    {
        return (int) (Setting::where('key', 'abstract_min_words')->value('value') ?: 200);
    }

    public static function abstractMaxWords(): int
    {
        return (int) (Setting::where('key', 'abstract_max_words')->value('value') ?: 250);
    }

    public static function keywordsMin(): int
    {
        return (int) (Setting::where('key', 'keywords_min')->value('value') ?: 4);
    }

    public static function keywordsMax(): int
    {
        return (int) (Setting::where('key', 'keywords_max')->value('value') ?: 6);
    }

    /** File types the conference accepts for a manuscript (document, Phase 1). */
    public const MANUSCRIPT_MIMES = ['pdf', 'doc', 'docx'];

    public const MANUSCRIPT_MAX_KB = 20480;   // 20 MB

    public static function manuscriptWindowOpensAt(): ?Carbon
    {
        return self::parseSetting('manuscript_submission_start');
    }

    public static function manuscriptWindowClosesAt(): ?Carbon
    {
        return self::parseSetting('manuscript_submission_end');
    }

    /**
     * Whether authors may upload or replace a manuscript right now. Both ends of the
     * window have to be configured; a missing date leaves the window shut rather than
     * silently open.
     */
    public static function manuscriptWindowIsOpen(): bool
    {
        $opens = self::manuscriptWindowOpensAt();
        $closes = self::manuscriptWindowClosesAt();

        if (!$opens || !$closes) {
            return false;
        }

        return Carbon::now()->betweenIncluded($opens, $closes);
    }

    /**
     * 'double' hides author identities from reviewers, 'single' does not.
     * Defaults to double, the stricter of the two.
     */
    public static function blindReviewMode(): string
    {
        $mode = Setting::where('key', 'blind_review_mode')->value('value');

        return $mode === 'single' ? 'single' : 'double';
    }

    public static function isDoubleBlind(): bool
    {
        return self::blindReviewMode() === 'double';
    }

    /**
     * Whether a paper needs its full manuscript before reviewers can be assigned to it.
     * Setting review_requires_manuscript; on unless set to 'false'.
     */
    public static function reviewRequiresManuscript(): bool
    {
        return Setting::where('key', 'review_requires_manuscript')->value('value') !== 'false';
    }

    /**
     * Whether a Track or Sub-Track Chair may be assigned as a reviewer in a track they
     * chair, for when reviewers are short. Setting chairs_can_review_own_track; off unless
     * set to 'true'. A chair who reviews a paper never decides on it.
     */
    public static function chairsMayReviewOwnTrack(): bool
    {
        return Setting::where('key', 'chairs_can_review_own_track')->value('value') === 'true';
    }

    /**
     * Whether the TPC Chair may set a decision themselves (Accept, Minor Revisions or
     * Reject) and approve it, instead of only approving or returning the chair's. Setting
     * tpc_can_override_decision; off unless set to 'true'.
     */
    public static function tpcMayOverrideDecision(): bool
    {
        return Setting::where('key', 'tpc_can_override_decision')->value('value') === 'true';
    }

    /**
     * Whether a chair who reviewed a paper may still decide on it (and act on its revision).
     * Setting reviewer_chair_can_decide; off unless set to 'true', so by default another
     * chair decides and nobody rules on their own evaluation.
     */
    public static function reviewerChairMayDecide(): bool
    {
        return Setting::where('key', 'reviewer_chair_can_decide')->value('value') === 'true';
    }

    /**
     * The shortest "feedback for authors" a reviewer may submit. Setting
     * review_feedback_min_chars; 0 (the default) only requires it not to be empty, so a
     * one-line comment such as "Accept" is enough.
     */
    public static function reviewFeedbackMinChars(): int
    {
        return max(0, (int) Setting::where('key', 'review_feedback_min_chars')->value('value'));
    }

    /**
     * Whether reviewers wait until the manuscript window has closed, so every reviewer reads
     * the final version the author could still replace until then. Setting
     * review_waits_for_manuscript_deadline; on unless set to 'false'.
     */
    public static function reviewWaitsForManuscriptDeadline(): bool
    {
        return Setting::where('key', 'review_waits_for_manuscript_deadline')->value('value') !== 'false';
    }

    /**
     * Whether an author's manuscript is frozen once a reviewer holds the paper, so the file
     * a reviewer reads cannot change under them. Setting manuscript_locks_on_review; on
     * unless set to 'false'. Someone with paper_manuscript_manage may still replace it.
     */
    public static function manuscriptLocksOnReview(): bool
    {
        return Setting::where('key', 'manuscript_locks_on_review')->value('value') !== 'false';
    }

    /**
     * Why the author may not edit the submission itself (title, abstract, keywords, track,
     * authors) now, or null when they may. Open only while the abstract is pending, the
     * abstract window is open and no reviewer or decision has touched the paper; after
     * acceptance only the camera-ready files and attendance change, and nothing after paying.
     */
    public static function submissionLockedReason(\App\Models\Paper $paper): ?string
    {
        // Said in terms of where the paper stands, most advanced first, so an accepted
        // author is told what they can still do rather than that the paper is "under review".
        if (ProceedingsRules::isPaid($paper)) {
            return 'Your paper has been accepted and the registration fee is paid, so it can no longer be changed. Contact the conference team if something needs correcting.';
        }

        if (ProceedingsRules::isAccepted($paper)) {
            return 'Your paper has been accepted. You can now only update who will attend, upload the camera-ready manuscript and the signed copyright form, and pay the registration fee.';
        }

        if (ProceedingsRules::isRejected($paper) || $paper->status === 'rejected') {
            return 'Your paper was not accepted, so it can no longer be edited.';
        }

        if (self::reviewHasStarted($paper)) {
            return 'Your paper is under review, so its title, abstract and authors can no longer be edited.';
        }

        if ($paper->status === 'approved') {
            return 'The abstract has been screened, so the submission can no longer be edited.';
        }

        if (!self::abstractWindowIsOpen()) {
            return 'Abstract submission is closed, so the submission can no longer be edited.';
        }

        return null;
    }

    /** A reviewer holds the paper (one who declined does not count), or a decision exists. */
    public static function reviewHasStarted(\App\Models\Paper $paper): bool
    {
        return $paper->decision()->exists()
            || $paper->reviewerAssignments()->where('status', '!=', 'declined')->exists();
    }

    /** Why the author may not replace this paper's manuscript now, or null when they may. */
    public static function manuscriptLockedReason(\App\Models\Paper $paper): ?string
    {
        if (!self::manuscriptWindowIsOpen()) {
            return 'The manuscript submission window is closed.';
        }

        // Decided, whatever the settings: later changes go through revision or camera-ready.
        if ($paper->decision()->exists()) {
            return 'A decision has been made on your paper, so the review manuscript can no longer be replaced. Changes go through the revised manuscript or the camera-ready version.';
        }

        // Declined assignments do not count: nobody is reading the file for them.
        $underReview = self::manuscriptLocksOnReview()
            && $paper->reviewerAssignments()->where('status', '!=', 'declined')->exists();

        if ($underReview) {
            return 'Your manuscript is under review, so it can no longer be replaced. Changes after the decision go through the revised manuscript or the camera-ready version.';
        }

        return null;
    }

    /** Why reviewers cannot be assigned to this paper yet, or null when they can. */
    public static function assignmentBlockedReason(\App\Models\Paper $paper): ?string
    {
        if (self::reviewRequiresManuscript() && !$paper->manuscript_path) {
            return 'The author has not uploaded the full manuscript yet, so reviewers cannot be assigned.';
        }

        $closes = self::manuscriptWindowClosesAt();
        if (self::reviewWaitsForManuscriptDeadline() && $closes && Carbon::now()->lte($closes)) {
            return 'Reviewers can be assigned once the manuscript deadline has passed (' . $closes->format('j M Y, g:i a')
                . '), so every reviewer reads the final version.';
        }

        return null;
    }

    /**
     * Whether reviewers may bid on papers. Bidding is optional in the document, so the
     * organisers can switch it off; a missing setting leaves it on.
     */
    public static function biddingIsOpen(): bool
    {
        return Setting::where('key', 'bidding_enabled')->value('value') !== 'false';
    }

    /**
     * Whether new abstracts are accepted right now, by either route: the registration
     * form or Papers > Submit. The organisers' switch has to be on, and today has to fall
     * between the opening date and the abstract deadline, so a forgotten switch cannot
     * keep submissions open past the deadline on one route but not the other.
     */
    /**
     * Whether abstract submission is accepted on the public registration page (/book-ticket).
     * Controlled independently by the 'is_registration_abstract_submission_open' setting
     * so organisers can turn off abstracts during registration while keeping submissions
     * open on the internal author portal (papers/submit).
     */
    public static function registrationAbstractIsOpen(): bool
    {
        if (Setting::where('key', 'is_registration_abstract_submission_open')->value('value') === 'false') {
            return false;
        }

        return self::abstractWindowIsOpen();
    }

    public static function abstractWindowIsOpen(): bool
    {
        if (Setting::where('key', 'is_abstract_submission_open')->value('value') === 'false') {
            return false;
        }

        $opens = self::parseSetting('registration_start_date');
        $closes = self::parseSetting('abstract_submission_deadline') ?? self::parseSetting('registration_close_date');
        $now = Carbon::now();

        return (!$opens || $now->gte($opens)) && (!$closes || $now->lte($closes));
    }

    /** @return array{0: int, 1: int} the page range a full manuscript must fall within */
    public static function pageLimits(): array
    {
        return [
            (int) (Setting::where('key', 'manuscript_min_pages')->value('value') ?: 6),
            (int) (Setting::where('key', 'manuscript_max_pages')->value('value') ?: 8),
        ];
    }

    /** How many research keywords a reviewer gives about themselves. */
    public static function reviewerKeywordsMin(): int
    {
        return (int) (Setting::where('key', 'reviewer_keywords_min')->value('value') ?: 1);
    }

    public static function reviewerKeywordsMax(): int
    {
        return (int) (Setting::where('key', 'reviewer_keywords_max')->value('value') ?: 20);
    }

    /** @return array<int, mixed> */
    public static function reviewerKeywordRules(): array
    {
        $min = self::reviewerKeywordsMin();
        $max = self::reviewerKeywordsMax();

        return ['required', function ($attribute, $value, $fail) use ($min, $max) {
            $keywords = self::splitKeywords($value);
            $count = count($keywords);
            if ($count < $min) {
                $fail("Please provide at least {$min} research area" . ($min > 1 ? 's.' : '.'));
            } elseif ($count > $max) {
                $fail("You can provide at most {$max} research areas. (Current count: {$count})");
            }

            foreach ($keywords as $kw) {
                if (mb_strlen($kw) > 100) {
                    $fail("Each research area must be 100 characters or fewer.");
                    break;
                }
            }
        }];
    }

    /**
     * Known research areas / topics across tracks, sub-tracks, assignments, and papers
     * to offer as selectable suggestions for reviewers.
     *
     * @return array<int, string>
     */
    public static function suggestedResearchAreas(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('suggested_research_areas', 3600, function () {
            $suggested = collect();

            // TrackAssignment expertise
            \App\Models\TrackAssignment::whereNotNull('expertise')->get(['expertise'])->each(function ($ta) use (&$suggested) {
                if (!empty($ta->expertise)) {
                    foreach (self::splitKeywords($ta->expertise) as $item) {
                        $suggested->push($item);
                    }
                }
            });

            // SubTracks & Tracks, as the topics their titles name. A whole title such as
            // "Sustainable Finance, Banking, Accounting & Fintech Innovations" would be one
            // keyword no paper ever uses, so it could never produce a match.
            \App\Models\SubTrack::pluck('name')->each(fn ($name) => $suggested->push(...self::topicsFrom($name)));
            \App\Models\Track::pluck('name')->each(fn ($name) => $suggested->push(...self::topicsFrom($name)));

            // Paper keywords
            \App\Models\Paper::whereNotNull('keywords')->get(['keywords'])->each(function ($p) use (&$suggested) {
                if (!empty($p->keywords)) {
                    foreach (self::splitKeywords($p->keywords) as $item) {
                        $suggested->push($item);
                    }
                }
            });

            // User research keywords
            \App\Models\User::whereNotNull('research_keywords')->get(['research_keywords'])->each(function ($u) use (&$suggested) {
                if (!empty($u->research_keywords)) {
                    foreach (self::splitKeywords($u->research_keywords) as $item) {
                        $suggested->push($item);
                    }
                }
            });

            $all = self::splitKeywords($suggested->all());
            natcasesort($all);

            return array_values($all);
        });
    }

    private static function parseSetting(string $key): ?Carbon
    {
        $value = Setting::where('key', $key)->value('value');

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            \Log::warning("SubmissionRules: setting '{$key}' is not a date: '{$value}'.");
            return null;
        }
    }

    public static function countWords(?string $text): int
    {
        $text = trim((string) $text);

        return $text === '' ? 0 : preg_match_all('/\s+/', $text) + 1;
    }

    /**
     * The keywords a value holds, whatever shape it arrives in. Forms post a
     * comma-joined string; the models hold an array.
     *
     * @param string|array|null $value
     * @return array<int, string>
     */
    public static function splitKeywords($value): array
    {
        $parts = is_array($value)
            ? $value
            : explode(',', (string) $value);

        $parts = array_map('trim', $parts);
        $parts = array_filter($parts, fn ($k) => $k !== '');

        // Drop repeats that differ only in spelling or case.
        $seen = [];
        foreach ($parts as $keyword) {
            $seen[self::normaliseKeyword($keyword)] ??= $keyword;
        }

        return array_values($seen);
    }

    /**
     * The comparable form of a keyword. Matching a paper to a reviewer compares these,
     * so "Machine Learning", "machine learning" and "Machine-Learning" all meet.
     */
    public static function normaliseKeyword(string $keyword): string
    {
        return Str::slug($keyword) ?: Str::lower(trim($keyword));
    }

    /**
     * How many keywords two lists share, once normalised. This is the signal the
     * automatic reviewer assignment ranks candidates on.
     *
     * @param string|array|null $a
     * @param string|array|null $b
     */
    public static function keywordOverlap($a, $b): int
    {
        $left = array_map([self::class, 'normaliseKeyword'], self::splitKeywords($a));
        $right = array_map([self::class, 'normaliseKeyword'], self::splitKeywords($b));

        return count(array_intersect($left, $right));
    }

    /**
     * Turns a track or sub-track title into the topics it covers.
     *
     * The titles are headings, not keyword lists &mdash; "Civil Engineering: Structural,
     * Geotechnical, Transportation & Infrastructure Resilience" names one discipline and
     * four topics under it. Splitting on the separators the titles actually use gives
     * terms worth matching keywords against; the "Track 7:" numbering is dropped
     * because it says nothing about the subject.
     *
     * @return array<int, string>
     */
    public static function topicsFrom(string $title): array
    {
        $title = preg_replace('/^\s*Track\s*\d+\s*:\s*/i', '', $title);

        $parts = preg_split('/[,:&]+/', $title);
        $parts = array_map('trim', $parts ?: []);
        // One- and two-letter fragments are separator debris, not subjects.
        $parts = array_filter($parts, fn ($p) => mb_strlen($p) > 2);

        return self::splitKeywords(array_values($parts));
    }

    /**
     * Validation rules for the abstract body. Pass the extra rules the caller
     * needs (the PHP-tag guard, for instance) and they are kept in front.
     *
     * @param array<int, mixed> $extra
     * @return array<int, mixed>
     */
    public static function abstractRules(array $extra = []): array
    {
        $min = self::abstractMinWords();
        $max = self::abstractMaxWords();

        return array_merge(['required', 'string'], $extra, [
            function ($attribute, $value, $fail) use ($min, $max) {
                $count = self::countWords($value);
                if ($count < $min || $count > $max) {
                    $fail("The abstract must be between {$min} and {$max} words. (Current count: {$count})");
                }
            },
        ]);
    }

    /**
     * @param array<int, mixed> $extra
     * @return array<int, mixed>
     */
    public static function keywordRules(array $extra = []): array
    {
        $min = self::keywordsMin();
        $max = self::keywordsMax();

        return array_merge(['required', 'string', 'max:255'], $extra, [
            function ($attribute, $value, $fail) use ($min, $max) {
                $count = count(self::splitKeywords($value));
                if ($count < $min || $count > $max) {
                    $fail("Please provide between {$min} and {$max} keywords. (Current count: {$count})");
                }
            },
        ]);
    }
}
