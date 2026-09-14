<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manuscript Received</title>
</head>
<body style="margin:0; padding:0; background:#F1F5F9; font-family: Arial, Helvetica, sans-serif; color:#1F2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F5F9; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="max-width:600px; width:100%; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #E2E8F0;">

                <tr>
                    <td style="background:#003366; padding:24px; text-align:center;">
                        <div style="color:#ffffff; font-size:18px; font-weight:bold; line-height:1.4;">
                            ICMRIA 2027
                        </div>
                        <div style="color:#CBD5E1; font-size:13px; margin-top:4px;">
                            International Conference on Multidisciplinary Research, Innovation and Applications
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 24px;">
                        <p style="margin:0 0 16px; font-size:15px;">
                            Dear {{ $paper->user?->profile?->first_name ?? $paper->user->name }},
                        </p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            @if($paper->manuscript_status === 'revised')
                                Your revised manuscript has been received and now replaces the file previously on record.
                            @else
                                Your full manuscript has been received.
                            @endif
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; margin:0 0 20px;">
                            <tr>
                                <td style="padding:14px 16px; font-size:14px;">
                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Paper ID</div>
                                    <div style="font-weight:bold; margin-bottom:10px;">{{ $paper->submission_id }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Title</div>
                                    <div style="margin-bottom:10px;">{{ $paper->title }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">File</div>
                                    <div style="margin-bottom:10px;">{{ $paper->manuscript_original_name }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Track</div>
                                    <div>{{ $paper->track->name ?? '—' }}@if($paper->subTrack) &mdash; {{ $paper->subTrack->name }}@endif</div>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            You may replace this file at any time while the manuscript submission window is open.
                            Peer review begins once it closes.
                        </p>

                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            Kind regards,<br>
                            <strong>ICMRIA 2027 Technical Programme Committee</strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#F8FAFC; padding:16px 24px; border-top:1px solid #E2E8F0; text-align:center;">
                        <div style="color:#64748B; font-size:12px; line-height:1.6;">
                            Daffodil International University, Daffodil Smart City, Birulia, Savar, Dhaka
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
