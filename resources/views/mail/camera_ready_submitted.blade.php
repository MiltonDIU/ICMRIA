<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Camera-Ready Files to Check</title>
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
                        <p style="margin:0 0 16px; font-size:15px;">Dear {{ $chair->name }},</p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            The authors of an accepted paper have uploaded the camera-ready manuscript and the signed copyright form.
                            Please check that the manuscript follows the conference template and carries every author's name and
                            affiliation, and that the form is signed. Then approve the files or send them back with a note. The
                            authors can pay the registration fee only after the files are approved.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; margin:0 0 20px;">
                            <tr>
                                <td style="padding:14px 16px; font-size:14px;">
                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Paper</div>
                                    <div style="font-weight:bold; margin-bottom:10px;">{{ $paper->submission_id }} &mdash; {{ $paper->title }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Track</div>
                                    <div>{{ $paper->track->name ?? '—' }}@if($paper->subTrack)<br>{{ $paper->subTrack->name }}@endif</div>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 20px;">
                            <a href="{{ route('admin.camera-ready-checks.index') }}"
                               style="display:inline-block; background:#1D4ED8; color:#ffffff; text-decoration:none; padding:10px 18px; border-radius:6px; font-size:14px; font-weight:bold;">
                                Open Camera-Ready Check
                            </a>
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
