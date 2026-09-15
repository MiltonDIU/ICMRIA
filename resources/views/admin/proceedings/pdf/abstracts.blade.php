<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Book of Abstracts – {{ $data['conference']['short_name'] }}</title>
    <style>
        @page { margin: 60px 55px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; color: #1F2937; }
        h1 { font-size: 20px; text-align: center; margin: 0 0 4px; color: #003366; }
        .subtitle { text-align: center; color: #64748B; margin-bottom: 24px; }
        h2 { font-size: 14px; color: #003366; border-bottom: 1px solid #CBD5E1; padding-bottom: 4px; margin: 26px 0 8px; }
        .paper { margin: 12px 0 18px; page-break-inside: avoid; }
        .meta { color: #64748B; font-size: 9px; }
        .title { font-size: 12.5px; font-weight: bold; margin: 2px 0 3px; }
        .authors { font-style: italic; margin-bottom: 6px; }
        .abstract { text-align: justify; line-height: 1.45; }
        .keywords { margin-top: 4px; color: #475569; }
    </style>
</head>
<body>
    <h1>{{ $data['conference']['name'] }}</h1>
    <div class="subtitle">Book of Abstracts &middot; {{ count($data['papers']) }} papers</div>

    @forelse(collect($data['papers'])->groupBy('track') as $track => $papers)
        <h2>{{ $track }}</h2>
        @foreach($papers as $paper)
            <div class="paper">
                <div class="meta">{{ $paper['submission_id'] }} &middot; {{ $paper['sub_track'] }}</div>
                <div class="title">{{ $paper['title'] }}</div>
                <div class="authors">
                    @foreach($paper['authors'] as $author)
                        {{ $author['name'] }}{{ $author['institution'] ? ' (' . $author['institution'] . ($author['country'] ? ', ' . $author['country'] : '') . ')' : '' }}{{ $loop->last ? '' : ';' }}
                    @endforeach
                </div>
                <div class="abstract">{{ $paper['abstract'] }}</div>
                @if($paper['keywords'])
                    <div class="keywords"><strong>Keywords:</strong> {{ implode(', ', $paper['keywords']) }}</div>
                @endif
            </div>
        @endforeach
    @empty
        <p>No papers to include yet.</p>
    @endforelse
</body>
</html>
