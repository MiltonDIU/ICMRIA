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

            // SubTracks & Tracks
            \App\Models\SubTrack::pluck('name')->each(fn ($name) => $suggested->push(trim($name)));
            \App\Models\Track::pluck('name')->each(fn ($name) => $suggested->push(trim($name)));

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
