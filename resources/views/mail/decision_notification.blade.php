<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Decision</title>
</head>
@php
    $decisionColours = ['accept' => '#15803D', 'minor_revisions' => '#1D4ED8', 'reject' => '#B91C1C'];
    $colour = $decisionColours[$decision->decision] ?? '#1F2937';
@endphp
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
                        <p style="margin:0 0 16px; font-size:15px;">Dear {{ $paper->user->name ?? 'Author' }},</p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                            Thank you for submitting your work to ICMRIA 2027. The review of your paper is complete,
                            and the Technical Programme Committee has reached its decision.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; margin:0 0 20px;">
                            <tr>
                                <td style="padding:14px 16px; font-size:14px;">
                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Paper ID</div>
                                    <div style="font-weight:bold; margin-bottom:10px;">{{ $paper->submission_id }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Title</div>
                                    <div style="margin-bottom:10px;">{{ $paper->title }}</div>

                                    <div style="color:#64748B; font-size:12px; text-transform:uppercase; letter-spacing:.4px;">Decision</div>
                                    <div style="font-size:17px; font-weight:bold; color:{{ $colour }};">{{ $decision->label() }}</div>
                                </td>
                            </tr>
                        </table>

                        @if($decision->note_to_authors)
                            <p style="margin:0 0 6px; font-size:13px; color:#64748B; text-transform:uppercase; letter-spacing:.4px;">From the track chair</p>
                            <p style="margin:0 0 20px; font-size:15px; line-height:1.6;">{!! nl2br(e($decision->note_to_authors)) !!}</p>
                        @endif

                        @if($review->submitted()->isNotEmpty())
                            <p style="margin:0 0 10px; font-size:16px; font-weight:bold;">Reviewer comments</p>
                            @foreach($review->submitted() as $row)
                                @php $evaluation = $row['evaluation']; @endphp
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                       style="border:1px solid #E2E8F0; border-radius:6px; margin:0 0 14px;">
                                    <tr>
                                        <td style="padding:14px 16px; font-size:14px; line-height:1.6;">
                                            <div style="font-weight:bold; margin-bottom:6px;">Reviewer {{ $loop->iteration }}</div>
                                            <div style="color:#475569; font-size:13px; margin-bottom:8px;">
                                                @foreach(\App\Models\PaperEvaluation::CRITERIA as $field => $label)
                                                    {{ $label }}: {{ $evaluation->$field }}/5 @if(!$loop->last) &middot; @endif
                                                @endforeach
                                            </div>
                                            <div>{!! nl2br(e($evaluation->feedback_for_authors)) !!}</div>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        @endif

                        <p style="margin:6px 0 16px; font-size:15px; line-height:1.6;">
                            @if($decision->decision === 'accept')
                                Congratulations. Please prepare your camera-ready manuscript following the Camera-Ready
                                Guidelines, taking the reviewers' comments into account, and complete your registration.
                            @elseif($decision->decision === 'minor_revisions')
                                @php $revisionDeadline = \App\Services\ProceedingsRules::revisionDeadline(); @endphp
                                Congratulations. Your paper is accepted on the condition that the revisions the reviewers
                                ask for are made. From your paper page, please upload a revised manuscript that addresses each
                                comment{{ $revisionDeadline ? ' by ' . $revisionDeadline->format('j F Y') : '' }}, then the
                                camera-ready version, and complete your registration.
                            @else
                                We are sorry that we cannot include your paper in the programme this year. We hope the
                                reviewers' comments are useful to you, and that we will see your work at a future edition.
                            @endif
                        </p>

                        <p style="margin:0 0 20px;">
                            <a href="{{ route('papers.show', $paper->id) }}"
                               style="display:inline-block; background:#1D4ED8; color:#ffffff; text-decoration:none; padding:10px 18px; border-radius:6px; font-size:14px; font-weight:bold;">
                                View your paper
                            </a>
                        </p>

                        <p style="margin:0 0 16px; font-size:13px; line-height:1.6; color:#64748B;">
                            Reviews at ICMRIA 2027 are anonymous, so reviewers' names are not shared.
                        </p>

                        <p style="margin:0; font-size:15px; line-height:1.6;">
                            With best regards,<br>
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
