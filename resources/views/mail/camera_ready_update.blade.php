<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Camera-Ready Update</title>
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
                        <div style="color:#CBD5E1; font-size:13px; margin-top:4px;">Publication &amp; Proceedings</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 24px;">
                        <p style="margin:0 0 16px; font-size:15px;">Dear {{ $paper->user->name ?? 'Author' }},</p>

                        @if($kind === 'confirmed')
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Your paper <strong>{{ $paper->submission_id }}</strong>, &ldquo;{{ $paper->title }}&rdquo;, is now
                                <strong style="color:#15803D;">Confirmed for Proceedings</strong>. We have your camera-ready
                                manuscript, the signed copyright transfer form and your registration fee.
                            </p>
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Your presentation session will be shown on your paper page once the programme is scheduled.
                            </p>
                        @else
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                We have reviewed the camera-ready files for <strong>{{ $paper->submission_id }}</strong>,
                                &ldquo;{{ $paper->title }}&rdquo;, and need a few changes before the paper can go into the proceedings.
                            </p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#FEF2F2; border:1px solid #FECACA; border-radius:6px; margin:0 0 20px;">
                                <tr>
                                    <td style="padding:14px 16px; font-size:14px; line-height:1.6;">
                                        {!! nl2br(e($paper->cameraReady->admin_note ?? '')) !!}
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Please upload the corrected files from your paper page.
                            </p>
                        @endif

                        <p style="margin:0 0 20px;">
                            <a href="{{ route('papers.show', $paper->id) }}"
                               style="display:inline-block; background:#1D4ED8; color:#ffffff; text-decoration:none; padding:10px 18px; border-radius:6px; font-size:14px; font-weight:bold;">
                                Open your paper
                            </a>
                        </p>

                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            With best regards,<br>
                            <strong>ICMRIA 2027 Organising Committee</strong>
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
