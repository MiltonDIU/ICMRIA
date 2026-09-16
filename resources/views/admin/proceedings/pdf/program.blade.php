<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Programme – {{ $data['conference']['short_name'] }}</title>
    <style>
        @page { margin: 70px 55px 65px 55px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1F2937; }
        footer { position: fixed; bottom: -42px; left: 0; right: 0; height: 20px; font-size: 8.5px; color: #64748B;
                 border-top: 1px solid #E2E8F0; padding-top: 5px; }
        footer .left { float: left; }
        footer .right { float: right; }
        .pagenum:before { content: counter(page); }

        .header { text-align: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 3px solid #0055A0; }
        .header .short { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: #0055A0; }
        .header h1 { font-size: 22px; color: #003366; margin: 6px 0 4px; }
        .header .meta { font-size: 10.5px; color: #475569; margin-top: 2px; }

        .day { margin-bottom: 18px; }
        .day.next { page-break-before: always; }
        .day-title { font-size: 14px; color: #ffffff; background: #003366; padding: 7px 10px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        tr { page-break-inside: avoid; }
        td { border-bottom: 1px solid #E2E8F0; padding: 8px 6px; vertical-align: top; }
        td.time { width: 60px; font-weight: bold; color: #0055A0; font-size: 11px; }
        .session-title { font-weight: bold; font-size: 11.5px; color: #0F172A; }
        .session-subtitle { color: #64748B; margin-top: 2px; }
        ol { margin: 6px 0 0 16px; padding: 0; }
        li { margin-bottom: 4px; }
        .paper-meta { color: #64748B; font-size: 8.5px; }
        .unscheduled { page-break-before: always; }
    </style>
</head>
<body>
<footer>
    <span class="left">{{ $data['conference']['short_name'] }} &middot; Conference Programme</span>
    <span class="right">Page <span class="pagenum"></span></span>
</footer>

<div class="header">
    <div class="short">{{ $data['conference']['short_name'] }}</div>
    <h1>Conference Programme</h1>
    <div class="meta">{{ $data['conference']['name'] }}</div>
    <div class="meta">
        @if(!empty($data['conference']['dates'])){{ $data['conference']['dates'] }} &middot; @endif{{ $data['conference']['venue'] }}
    </div>
</div>

@forelse(collect($data['sessions'])->groupBy('day') as $day => $sessions)
    <div class="day {{ $loop->first ? '' : 'next' }}">
        <h2 class="day-title">
            Day {{ $day }}
            @if(!empty($data['conference']['day_dates'][$day])) &middot; {{ $data['conference']['day_dates'][$day] }} @endif
        </h2>
        <table>
            @foreach($sessions as $session)
                <tr>
                    <td class="time">{{ $session['start_time'] }}</td>
                    <td>
                        <div class="session-title">{{ $session['title'] }}</div>
                        @if($session['subtitle'])
                            <div class="session-subtitle">{{ $session['subtitle'] }}</div>
                        @endif
                        @if($session['papers'])
                            <ol>
                                @foreach($session['papers'] as $paper)
                                    <li>
                                        {{ $paper['title'] }}
                                        <div class="paper-meta">{{ $paper['submission_id'] }}{{ $paper['presenting_author'] ? ' · presented by ' . $paper['presenting_author'] : '' }}</div>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <p>No sessions have been scheduled.</p>
@endforelse

@if($data['unscheduled'])
    <div class="unscheduled">
        <h2 class="day-title">Confirmed papers not yet placed in a session</h2>
        <table>
            @foreach($data['unscheduled'] as $paper)
                <tr><td class="time">{{ $paper['submission_id'] }}</td><td>{{ $paper['title'] }}</td></tr>
            @endforeach
        </table>
    </div>
@endif
</body>
</html>
