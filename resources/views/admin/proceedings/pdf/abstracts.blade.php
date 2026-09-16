<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Book of Abstracts – {{ $data['conference']['short_name'] }}</title>
    <style>
        @page { margin: 70px 60px 70px 60px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1F2937; line-height: 1.45; }
        footer { position: fixed; bottom: -45px; left: 0; right: 0; height: 20px; font-size: 8.5px; color: #64748B;
                 border-top: 1px solid #E2E8F0; padding-top: 5px; }
        footer .left { float: left; }
        footer .right { float: right; }
        .pagenum:before { content: counter(page); }

        .cover { text-align: center; padding-top: 150px; page-break-after: always; }
        .cover .kicker { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: #0055A0; }
        .cover h1 { font-size: 24px; color: #003366; margin: 18px 30px 10px; line-height: 1.25; }
        .cover .short { font-size: 17px; color: #0055A0; font-weight: bold; }
        .cover .rule { width: 120px; height: 3px; background: #0055A0; margin: 28px auto; }
        .cover .title { font-size: 30px; color: #003366; font-weight: bold; letter-spacing: 1px; }
        .cover .meta { font-size: 12px; color: #334155; margin-top: 10px; }
        .cover .count { font-size: 10px; color: #64748B; margin-top: 130px; }

        h2.section { font-size: 16px; color: #003366; border-bottom: 2px solid #0055A0; padding-bottom: 5px; margin: 0 0 14px; }
        .toc-track { font-weight: bold; color: #003366; margin: 12px 0 4px; font-size: 11px; }
        .toc-item { margin: 0 0 3px 12px; }
        .toc-id { color: #64748B; font-size: 9px; }

        .track { page-break-before: always; }
        .track-title { font-size: 14px; color: #ffffff; background: #003366; padding: 8px 10px; margin: 0 0 14px; }
        .paper { margin: 0 0 18px; padding-bottom: 12px; border-bottom: 1px solid #E2E8F0; page-break-inside: avoid; }
        .meta-line { color: #64748B; font-size: 8.5px; text-transform: uppercase; letter-spacing: .4px; }
        .paper-title { font-size: 12.5px; font-weight: bold; color: #0F172A; margin: 3px 0 5px; }
        .authors { font-size: 10px; margin-bottom: 2px; }
        .presenting { text-decoration: underline; }
        .affiliations { font-size: 8.5px; color: #475569; font-style: italic; margin-bottom: 7px; }
        sup { font-size: 7px; }
        .abstract { text-align: justify; }
        .keywords { margin-top: 5px; font-size: 9px; color: #334155; }

        .index { page-break-before: always; }
        .index-row { margin: 0 0 3px; }
        .index-ids { color: #64748B; font-size: 9px; }
        .note { font-size: 8.5px; color: #64748B; margin-bottom: 10px; }
    </style>
</head>
<body>
@php
    $papers = collect($data['papers']);
    $byTrack = $papers->groupBy('track');
    $affiliationOf = fn ($author) => trim(($author['institution'] ?? '') . (!empty($author['country']) ? ', ' . $author['country'] : ''), ', ');
    $authorIndex = $papers
        ->flatMap(fn ($paper) => collect($paper['authors'])->map(fn ($author) => ['name' => $author['name'], 'id' => $paper['submission_id']]))
        ->filter(fn ($row) => filled($row['name']))
        ->groupBy('name')
        ->map(fn ($rows) => $rows->pluck('id')->unique()->implode(', '))
        ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);
@endphp

<footer>
    <span class="left">{{ $data['conference']['short_name'] }} &middot; Book of Abstracts</span>
    <span class="right">Page <span class="pagenum"></span></span>
</footer>

<div class="cover">
    <div class="kicker">Conference Proceedings Companion</div>
    <h1>{{ $data['conference']['name'] }}</h1>
    <div class="short">{{ $data['conference']['short_name'] }}</div>
    <div class="rule"></div>
    <div class="title">Book of Abstracts</div>
    @if(!empty($data['conference']['dates']))
        <div class="meta">{{ $data['conference']['dates'] }}</div>
    @endif
    <div class="meta">{{ $data['conference']['venue'] }}</div>
    <div class="count">
        {{ $papers->count() }} papers &middot; {{ $byTrack->count() }} tracks &middot;
        prepared {{ \Carbon\Carbon::parse($data['conference']['generated_at'])->format('j F Y') }}
    </div>
</div>

<h2 class="section">Contents</h2>
@forelse($byTrack as $track => $trackPapers)
    <div class="toc-track">{{ $track }}</div>
    @foreach($trackPapers as $paper)
        <div class="toc-item"><span class="toc-id">{{ $paper['submission_id'] }}</span> &nbsp; {{ $paper['title'] }}</div>
    @endforeach
@empty
    <p>No papers to include yet.</p>
@endforelse

@foreach($byTrack as $track => $trackPapers)
    <div class="track">
        <div class="track-title">{{ $track }}</div>
        @foreach($trackPapers as $paper)
            @php
                $institutions = collect($paper['authors'])->map($affiliationOf)->filter()->unique()->values();
            @endphp
            <div class="paper">
                <div class="meta-line">{{ $paper['submission_id'] }} &middot; {{ $paper['sub_track'] }}</div>
                <div class="paper-title">{{ $paper['title'] }}</div>
                <div class="authors">
                    @foreach($paper['authors'] as $author)
                        @php $affiliation = $affiliationOf($author); @endphp
                        <span class="{{ $author['presenting'] ? 'presenting' : '' }}">{{ $author['name'] }}</span>@if($affiliation !== '')<sup>{{ $institutions->search($affiliation) + 1 }}</sup>@endif{{ $loop->last ? '' : ',' }}
                    @endforeach
                </div>
                @if($institutions->isNotEmpty())
                    <div class="affiliations">
                        @foreach($institutions as $i => $institution)<sup>{{ $i + 1 }}</sup>{{ $institution }}{{ $loop->last ? '' : '; ' }}@endforeach
                    </div>
                @endif
                <div class="abstract">{{ $paper['abstract'] }}</div>
                @if($paper['keywords'])
                    <div class="keywords"><strong>Keywords:</strong> {{ implode(', ', $paper['keywords']) }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endforeach

@if($authorIndex->isNotEmpty())
    <div class="index">
        <h2 class="section">Author Index</h2>
        <div class="note">Presenting authors are underlined in the abstracts.</div>
        @foreach($authorIndex as $name => $ids)
            <div class="index-row">{{ $name }} &nbsp;<span class="index-ids">{{ $ids }}</span></div>
        @endforeach
    </div>
@endif
</body>
</html>
