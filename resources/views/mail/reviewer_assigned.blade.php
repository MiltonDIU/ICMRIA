<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Request</title>
</head>
<body style="margin:0; padding:0; background:#F1F5F9; font-family: Arial, Helvetica, sans-serif; color:#1F2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F5F9; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="max-width:600px; width:100%; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #E2E8F0;">

                <tr>
                    <td style="background:#003366; padding:24px; text-align:center;">
                        <div style="color:#ffffff; font-size:18px; font-weight:bold;">ICMRIA 2027</div>
                        <div style="color:#CBD5E1; font-size:13px; margin-top:4px;">Technical Programme Committee</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 24px;">
                        <p style="margin:0 0 16px; font-size:15px;">Dear {{ $reviewer->name }},</p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            You have been asked to review a submission for ICMRIA 2027. The paper falls within the
                            sub-track you are listed under.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; margin:0 0 20px;">
                            <tr>
                                <td style="padding:14px 16px; font-size:14px;">
                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Paper ID</div>
                                    <div style="font-weight:bold; margin-bottom:10px;">{{ $paper->submission_id }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Title</div>
                                    <div style="margin-bottom:10px;">{{ $paper->title }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Track</div>
                                    <div>{{ $paper->track->name ?? '—' }}@if($paper->subTrack)<br>{{ $paper->subTrack->name }}@endif</div>
                                </td>
                            </tr>
                        </table>

                        @if(\App\Services\SubmissionRules::isDoubleBlind())
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#92400E; background:#FEF3C7; border:1px solid #FDE68A; border-radius:6px; padding:12px 14px;">
                                Review for this conference is <strong>double-blind</strong>. The manuscript has been
                                submitted without author names. If you recognise the work and feel unable to judge it
                                impartially, please say so rather than continuing.
                            </p>
                        @endif

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            Sign in to read the manuscript and record your evaluation. If you are unable to take this
                            paper, decline it there so it can be passed to someone else promptly.
                        </p>

                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            With thanks,<br>
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
