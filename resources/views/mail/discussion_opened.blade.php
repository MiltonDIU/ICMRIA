<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discussion Opened</title>
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
                            Thank you for your evaluation of <strong>{{ $paper->submission_id }}</strong>,
                            &ldquo;{{ $paper->title }}&rdquo;. The reviewers' assessments of this paper differ, so the
                            track chair has opened an internal discussion to help the committee reach a sound decision.
                        </p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            You can now read the other evaluations and add your view. Reviewers appear to one another by
                            number only, and nothing in the discussion is sent to the authors.
                        </p>

                        <p style="margin:0 0 20px;">
                            <a href="{{ route('admin.reviews.show', $assignment->id) }}"
                               style="display:inline-block; background:#1D4ED8; color:#ffffff; text-decoration:none; padding:10px 18px; border-radius:6px; font-size:14px; font-weight:bold;">
                                Join the discussion
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
