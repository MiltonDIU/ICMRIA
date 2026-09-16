<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperDecision;
use App\Models\Schedule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Accepted papers, their authors and the session programme, in the shapes the
 * requirement document asks for (Phase 6: "exports accepted paper data, author details,
 * and session schedules into XML/JSON or PDF").
 *
 * toArray() is the single source: JSON is it verbatim, XML mirrors it, and the PDFs
 * are rendered from it, so every format carries the same papers in the same order.
 */
class ProceedingsExport
{
    private bool $confirmedOnly;
    private ?Collection $papers = null;

    /** @param bool $confirmedOnly false includes accepted papers not yet confirmed */
    public function __construct(bool $confirmedOnly = true)
    {
        $this->confirmedOnly = $confirmedOnly;
    }

    public function confirmedOnly(): bool
    {
        return $this->confirmedOnly;
    }

    public function papers(): Collection
    {
        return $this->papers ??= Paper::accepted()
            ->when($this->confirmedOnly, fn ($query) => $query->whereHas('cameraReady', fn ($q) => $q->where('status', 'confirmed')))
            ->with(['track', 'subTrack', 'authors.country', 'decision', 'cameraReady.schedule', 'user.profile.country'])
            ->orderBy('track_id')
            ->orderBy('id')
            ->get();
    }

    public function toArray(): array
    {
        $papers = $this->papers();

        $bySession = $papers
            ->filter(fn ($paper) => $paper->cameraReady?->schedule_id)
            ->groupBy(fn ($paper) => $paper->cameraReady->schedule_id);

        $sessions = Schedule::where('is_active', '1')
            ->orderBy('day_number')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'day' => (int) $session->day_number,
                'start_time' => substr((string) $session->start_time, 0, 5),
                'title' => $session->title,
                'subtitle' => $session->subtitle,
                'papers' => ($bySession[$session->id] ?? collect())
                    ->sortBy(fn ($paper) => $paper->cameraReady->presentation_order ?? PHP_INT_MAX)
                    ->map(fn ($paper) => [
                        'submission_id' => $paper->submission_id,
                        'order' => $paper->cameraReady->presentation_order,
                        'title' => $paper->title,
                        'presenting_author' => $this->presentingAuthor($paper),
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();

        return [
            'conference' => [
                'name' => 'International Conference on Multidisciplinary Research, Innovation and Applications 2027',
                'short_name' => 'ICMRIA 2027',
                'dates' => $this->conferenceDates(),
                'day_dates' => $this->dayDates(),
                'venue' => 'Daffodil International University, Daffodil Smart City, Birulia, Savar, Dhaka, Bangladesh',
                'generated_at' => now()->toIso8601String(),
                'scope' => $this->confirmedOnly ? 'confirmed_for_proceedings' : 'all_accepted',
            ],
            'papers' => $papers->map(fn ($paper) => $this->paperData($paper))->values()->all(),
            'sessions' => $sessions,
            'unscheduled' => $papers
                ->filter(fn ($paper) => $paper->cameraReady?->isConfirmed() && !$paper->cameraReady->schedule_id)
                ->map(fn ($paper) => ['submission_id' => $paper->submission_id, 'title' => $paper->title])
                ->values()
                ->all(),
        ];
    }

    public function toXml(): string
    {
        $data = $this->toArray();
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $root = $doc->appendChild($doc->createElement('proceedings'));
        $root->setAttribute('conference', $data['conference']['short_name']);
        $root->setAttribute('generated_at', $data['conference']['generated_at']);
        $root->setAttribute('scope', $data['conference']['scope']);
        $this->text($doc, $root, 'name', $data['conference']['name']);

        $papersNode = $root->appendChild($doc->createElement('papers'));
        foreach ($data['papers'] as $paper) {
            $node = $papersNode->appendChild($doc->createElement('paper'));
            $node->setAttribute('id', $paper['submission_id']);
            $node->setAttribute('status', $paper['status']);

            foreach (['title', 'abstract', 'track', 'sub_track', 'decision', 'presentation_mode'] as $field) {
                $this->text($doc, $node, $field, $paper[$field]);
            }

            $keywords = $node->appendChild($doc->createElement('keywords'));
            foreach ($paper['keywords'] as $keyword) {
                $this->text($doc, $keywords, 'keyword', $keyword);
            }

            $authors = $node->appendChild($doc->createElement('authors'));
            foreach ($paper['authors'] as $author) {
                $authorNode = $authors->appendChild($doc->createElement('author'));
                $authorNode->setAttribute('order', (string) $author['order']);
                $authorNode->setAttribute('presenting', $author['presenting'] ? 'true' : 'false');
                foreach (['name', 'designation', 'department', 'institution', 'country', 'email'] as $field) {
                    $this->text($doc, $authorNode, $field, $author[$field]);
                }
            }

            if ($paper['session']) {
                $session = $node->appendChild($doc->createElement('session'));
                $session->setAttribute('ref', (string) $paper['session']['id']);
                $session->setAttribute('day', (string) $paper['session']['day']);
                $session->setAttribute('start_time', $paper['session']['start_time']);
                $session->setAttribute('order', (string) ($paper['session']['order'] ?? ''));
                $session->appendChild($doc->createTextNode($paper['session']['title']));
            }
        }

        $schedule = $root->appendChild($doc->createElement('schedule'));
        foreach ($data['sessions'] as $session) {
            $node = $schedule->appendChild($doc->createElement('session'));
            $node->setAttribute('id', (string) $session['id']);
            $node->setAttribute('day', (string) $session['day']);
            $node->setAttribute('start_time', $session['start_time']);
            $this->text($doc, $node, 'title', $session['title']);
            $this->text($doc, $node, 'subtitle', $session['subtitle']);

            $papers = $node->appendChild($doc->createElement('papers'));
            foreach ($session['papers'] as $paper) {
                $ref = $papers->appendChild($doc->createElement('paper'));
                $ref->setAttribute('ref', $paper['submission_id']);
                $ref->setAttribute('order', (string) ($paper['order'] ?? ''));
            }
        }

        return $doc->saveXML();
    }

    /** "9–10 January 2027", from the event dates in the settings. */
    private function conferenceDates(): string
    {
        $start = Setting::where('key', 'event_date')->value('value');
        $end = Setting::where('key', 'event_end_date')->value('value');

        if (!$start) {
            return '';
        }

        $start = Carbon::parse($start);
        $end = $end ? Carbon::parse($end) : $start;

        if ($start->isSameDay($end)) {
            return $start->format('j F Y');
        }

        return $start->isSameMonth($end)
            ? $start->format('j') . '–' . $end->format('j F Y')
            : $start->format('j F') . ' – ' . $end->format('j F Y');
    }

    /** @return array<int, string> programme day number => its calendar date */
    private function dayDates(): array
    {
        $start = Setting::where('key', 'event_date')->value('value');

        if (!$start) {
            return [];
        }

        $start = Carbon::parse($start);

        return Schedule::where('is_active', '1')->distinct()->pluck('day_number')
            ->mapWithKeys(fn ($day) => [(int) $day => $start->copy()->addDays((int) $day - 1)->format('l, j F Y')])
            ->all();
    }

    private function paperData(Paper $paper): array
    {
        $final = $paper->cameraReady;
        $session = $final?->schedule;

        return [
            'submission_id' => $paper->submission_id,
            'title' => $paper->title,
            'abstract' => $paper->abstract,
            'keywords' => SubmissionRules::splitKeywords($paper->keywords),
            'track' => $paper->track->name ?? null,
            'sub_track' => $paper->subTrack->name ?? null,
            'decision' => PaperDecision::DECISIONS[$paper->decision->decision] ?? null,
            'status' => $final?->isConfirmed() ? 'confirmed_for_proceedings' : 'accepted',
            'presentation_mode' => $paper->mode_of_participation,
            'session' => $session ? [
                'id' => $session->id,
                'day' => (int) $session->day_number,
                'start_time' => substr((string) $session->start_time, 0, 5),
                'title' => $session->title,
                'order' => $final->presentation_order,
            ] : null,
            'authors' => $this->authors($paper),
        ];
    }

    /** The paper's authors in order; the submitter alone when no author rows exist. */
    private function authors(Paper $paper): array
    {
        if ($paper->authors->isNotEmpty()) {
            return $paper->authors->values()->map(fn ($author, $index) => [
                'order' => $author->author_order ?? $index + 1,
                'name' => $author->name,
                'designation' => $author->designation,
                'department' => $author->department,
                'institution' => $author->institution,
                'country' => $author->country->name ?? null,
                'email' => $author->email,
                'presenting' => (bool) $author->is_presenting_author,
            ])->all();
        }

        $profile = $paper->user?->profile;

        return [[
            'order' => 1,
            'name' => $paper->user->name ?? null,
            'designation' => $profile->designation ?? null,
            'department' => $profile->department ?? null,
            'institution' => $profile->institution ?? null,
            'country' => $profile->country->name ?? null,
            'email' => $paper->user->email ?? null,
            'presenting' => true,
        ]];
    }

    private function presentingAuthor(Paper $paper): ?string
    {
        $authors = $this->authors($paper);
        $presenting = collect($authors)->firstWhere('presenting', true) ?? ($authors[0] ?? null);

        return $presenting['name'] ?? null;
    }

    private function text(\DOMDocument $doc, \DOMNode $parent, string $name, $value): void
    {
        $node = $doc->createElement($name);
        $node->appendChild($doc->createTextNode((string) ($value ?? '')));
        $parent->appendChild($node);
    }
}
