<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Programme – {{ $data['conference']['short_name'] }}</title>
    <style>
        @page { margin: 55px 50px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; color: #1F2937; }
        h1 { font-size: 20px; text-align: center; margin: 0 0 4px; color: #003366; }
        .subtitle { text-align: center; color: #64748B; margin-bottom: 22px; }
        h2 { font-size: 14px; color: #ffffff; background: #003366; padding: 5px 8px; margin: 22px 0 0; }
        table { width: 100%; border-collapse: collapse; }
        td { border-bottom: 1px solid #E2E8F0; padding: 7px 6px; vertical-align: top; }
        td.time { width: 55px; font-weight: bold; color: #003366; }
        .session-title { font-weight: bold; font-size: 11.5px; }
        .session-subtitle { color: #64748B; margin-top: 2px; }
        ol { margin: 6px 0 0 16px; padding: 0; }
        li { margin-bottom: 3px; }
        .paper-id { color: #64748B; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $data['conference']['name'] }}</h1>
    <div class="subtitle">Conference Programme</div>

    @forelse(collect($data['sessions'])->groupBy('day') as $day => $sessions)
        <h2>Day {{ $day }}</h2>
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
                                        <span class="paper-id">({{ $paper['submission_id'] }}{{ $paper['presenting_author'] ? ' – ' . $paper['presenting_author'] : '' }})</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @empty
        <p>No sessions have been scheduled.</p>
    @endforelse

    @if($data['unscheduled'])
        <h2>Confirmed papers not yet placed in a session</h2>
        <table>
            @foreach($data['unscheduled'] as $paper)
                <tr><td class="time">{{ $paper['submission_id'] }}</td><td>{{ $paper['title'] }}</td></tr>
            @endforeach
        </table>
    @endif
</body>
</html>
