@extends('layouts.admin')

@section('styles')
@parent
<style>
    /* The conference palette, so the portal reads as the same site as icmria.com. */
    .ad {
        --brand-blue: #0055A0;
        --brand-green: #10BB43;
        --brand-navy: #00396B;
        --brand-grey: #54585B;
        --brand-ink: #1E2430;
        --brand-tint: #F4F7FA;
        color: var(--brand-ink);
    }
    .ad .ad-hero {
        background: linear-gradient(135deg, var(--brand-navy) 0%, var(--brand-blue) 100%);
        color: #fff;
        border-radius: 12px;
        padding: 26px 30px;
        margin-bottom: 24px;
    }
    .ad .ad-hero h2 { font-size: 1.5rem; font-weight: 700; margin: 0 0 4px; }
    .ad .ad-chip {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin: 6px 6px 0 0;
    }
    .ad .ad-chip-green { background: var(--brand-green); border-color: var(--brand-green); }
    .ad .ad-regid { background: rgba(0, 0, 0, 0.18); border-radius: 10px; padding: 12px 18px; text-align: center; }
    .ad .ad-regid small { display: block; text-transform: uppercase; letter-spacing: 1px; font-size: .68rem; opacity: .8; }
    .ad .ad-regid strong { font-size: 1.1rem; letter-spacing: 1px; }

    .ad .ad-card {
        background: #fff;
        border: 1px solid #E3E9F0;
        border-radius: 10px;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .ad .ad-card-head {
        background: var(--brand-tint);
        border-bottom: 1px solid #E3E9F0;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .ad .ad-card-head h5 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--brand-navy); }
    .ad .ad-card-body { padding: 20px; }

    /* Timeline */
    .ad .ad-timeline { position: relative; padding-left: 28px; }
    .ad .ad-timeline::before {
        content: ''; position: absolute; left: 9px; top: 6px; bottom: 6px;
        width: 2px; background: #E3E9F0;
    }
    .ad .ad-step { position: relative; padding: 0 0 20px; }
    .ad .ad-step:last-child { padding-bottom: 0; }
    .ad .ad-step::before {
        content: ''; position: absolute; left: -24px; top: 4px;
        width: 12px; height: 12px; border-radius: 50%;
        background: #fff; border: 2px solid #C9D6E4;
    }
    .ad .ad-step.is-open::before { border-color: var(--brand-green); background: var(--brand-green); }
    .ad .ad-step.is-passed::before { border-color: #C9D6E4; background: #C9D6E4; }
    .ad .ad-step.is-upcoming::before { border-color: var(--brand-blue); }
    .ad .ad-step.is-passed .ad-step-label { color: var(--brand-grey); text-decoration: line-through; }
    .ad .ad-step-label { font-weight: 600; color: var(--brand-navy); }
    .ad .ad-step-date { font-size: .875rem; color: var(--brand-grey); }
    .ad .ad-step-note {
        display: inline-block; margin-left: 8px; padding: 1px 9px;
        border-radius: 999px; font-size: .72rem; font-weight: 700;
    }
    .ad .ad-step.is-open .ad-step-note { background: #E7F8EC; color: #0B7A2C; }
    .ad .ad-step.is-upcoming .ad-step-note { background: #E8F1FA; color: var(--brand-blue); }
    .ad .ad-step.is-passed .ad-step-note { background: #EEF1F4; color: var(--brand-grey); }

    /* To-do */
    .ad .ad-todo { border-left: 4px solid var(--brand-blue); background: var(--brand-tint); border-radius: 6px; padding: 14px 18px; margin-bottom: 12px; }
    .ad .ad-todo.tone-warning { border-left-color: #E8A33D; background: #FDF6EA; }
    .ad .ad-todo.tone-danger { border-left-color: #C0392B; background: #FBEDEB; }
    .ad .ad-todo.tone-info { border-left-color: var(--brand-green); background: #EDF9F1; }
    .ad .ad-todo strong { display: block; color: var(--brand-navy); margin-bottom: 3px; }
    .ad .ad-todo p { margin: 0; font-size: .875rem; color: var(--brand-grey); }

    /* Fees */
    .ad .ad-fees th { background: var(--brand-tint); color: var(--brand-navy); font-size: .76rem; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #E3E9F0; }
    .ad .ad-fees .is-stage { background: #F0F7FF; font-weight: 700; color: var(--brand-navy); }
    .ad .ad-fees tr.is-mine { background: #EDF9F1; }
    .ad .ad-fees tr.is-mine td:first-child { box-shadow: inset 3px 0 0 var(--brand-green); }

    /* Guidelines */
    .ad .ad-rule { display: flex; padding: 10px 0; border-bottom: 1px dashed #E3E9F0; }
    .ad .ad-rule:last-child { border-bottom: 0; }
    .ad .ad-rule i { color: var(--brand-green); margin-right: 12px; margin-top: 3px; }
    .ad .ad-rule strong { color: var(--brand-navy); }
    .ad .ad-rule p { margin: 2px 0 0; font-size: .86rem; color: var(--brand-grey); }

    /* Programme schedule, one tab per day */
    .ad .ad-day-tabs {
        border-bottom: 1px solid #E3E9F0;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        margin-bottom: 16px;
    }
    .ad .ad-day-tabs .nav-link {
        white-space: nowrap;
        border: 0;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 8px 14px;
        font-weight: 600;
        font-size: .875rem;
        color: var(--brand-grey);
    }
    .ad .ad-day-tabs .nav-link:hover { border-bottom-color: #C9D6E4; color: var(--brand-navy); }
    .ad .ad-day-tabs .nav-link.active {
        color: var(--brand-navy);
        background: transparent;
        border-bottom-color: var(--brand-blue);
    }
    .ad .ad-day-count {
        display: inline-block;
        min-width: 20px;
        margin-left: 6px;
        padding: 0 6px;
        border-radius: 999px;
        background: #E8F1FA;
        color: var(--brand-blue);
        font-size: .72rem;
        text-align: center;
    }
    .ad .ad-day-tabs .nav-link.active .ad-day-count { background: var(--brand-blue); color: #fff; }
    /* A single heavy day scrolls inside the card rather than stretching the page. */
    .ad .ad-day-panes { max-height: 420px; overflow-y: auto; }

    .ad .btn-brand { background: var(--brand-blue); border-color: var(--brand-blue); color: #fff; }
    .ad .btn-brand:hover { background: var(--brand-navy); border-color: var(--brand-navy); color: #fff; }
    .ad .btn-brand-green { background: var(--brand-green); border-color: var(--brand-green); color: #fff; }
    .ad .btn-brand-green:hover { background: #0e9e39; border-color: #0e9e39; color: #fff; }
    .ad .btn-brand-outline { border-color: var(--brand-blue); color: var(--brand-blue); background: transparent; }
    .ad .btn-brand-outline:hover { background: var(--brand-blue); color: #fff; }
</style>
@endsection

@section('content')
@php
    $paid = $profile && $profile->payment_status == '1';
    $currency = $profile->currency ?? 'BDT';

    // Read through the same services the submission form and its validators use, so the
    // dashboard cannot advertise a rule the portal does not enforce.
    [$minPages, $maxPages] = \App\Services\SubmissionRules::pageLimits();
    $abstractMin = \App\Services\SubmissionRules::abstractMinWords();
    $abstractMax = \App\Services\SubmissionRules::abstractMaxWords();
    $keywordsMin = \App\Services\SubmissionRules::keywordsMin();
    $keywordsMax = \App\Services\SubmissionRules::keywordsMax();
    $doubleBlind = \App\Services\SubmissionRules::isDoubleBlind();
    $formats = strtoupper(implode(', ', \App\Services\SubmissionRules::MANUSCRIPT_MIMES));

    $approved = $papers->where('status', 'approved')->count();
    $pending = $papers->filter(fn ($p) => $p->status === null || $p->status === 'pending')->count();

    $statusStyles = [
        'approved' => ['Approved', 'success'],
        'pending'  => ['Under screening', 'warning'],
        'rejected' => ['Not accepted', 'secondary'],
    ];
@endphp

<div class="ad">

    <div class="ad-hero">
        <div class="row align-items-center">
            <div class="col">
                <h2>Welcome, {{ $user->name }}</h2>
                <div style="opacity: .85; font-size: .95rem;">
                    {{ $settings['site_title'] ?? 'ICMRIA 2027' }} &mdash; author portal
                </div>
                <div>
                    @if($profile)
                        <span class="ad-chip">{{ $profile->is_author ? 'Author' : 'Participant' }}</span>
                        @if($profile->price)
                            <span class="ad-chip">{{ $profile->price->name }}</span>
                        @endif
                        <span class="ad-chip {{ $paid ? 'ad-chip-green' : '' }}">
                            {{ $paid ? 'Registration paid' : $currency . ' ' . number_format($profile->pay_amount ?? 0, 2) . ' due' }}
                        </span>
                    @else
                        <span class="ad-chip">Not registered yet</span>
                    @endif
                </div>
            </div>
            @if($profile)
                <div class="col-md-auto mt-3 mt-md-0">
                    <div class="ad-regid">
                        <small>Registration ID</small>
                        <strong>{{ $profile->registration_id ?: 'Not issued yet' }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- What is actually waiting on them, before anything they merely need to read. --}}
    @if(count($todo))
        <div class="ad-card">
            <div class="ad-card-head">
                <h5><i class="fas fa-tasks mr-2"></i> What needs your attention</h5>
            </div>
            <div class="ad-card-body">
                @foreach($todo as $item)
                    <div class="ad-todo tone-{{ $item['tone'] }}">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['body'] }}</p>
                        @if($item['url'] && $item['action'])
                            <a href="{{ $item['url'] }}" class="btn btn-sm btn-brand-outline mt-2">
                                {{ $item['action'] }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                @endforeach
                @if($paymentBlockReason)
                    <div class="ad-todo tone-danger">
                        <strong>Online payment is closed</strong>
                        <p>{{ $paymentBlockReason }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Important dates, from the settings that actually gate each step. --}}
        <div class="col-lg-6">
            <div class="ad-card">
                <div class="ad-card-head">
                    <h5><i class="far fa-calendar-alt mr-2"></i> Important dates</h5>
                </div>
                <div class="ad-card-body">
                    @if(empty($milestones))
                        <p class="text-muted mb-0">The conference timeline has not been published yet.</p>
                    @else
                        <div class="ad-timeline">
                            @foreach($milestones as $step)
                                <div class="ad-step is-{{ $step['state'] }}">
                                    <div class="ad-step-label">
                                        <i class="fas {{ $step['icon'] }} mr-1" style="opacity: .5;"></i>
                                        {{ $step['label'] }}
                                    </div>
                                    <div class="ad-step-date">
                                        @if($step['from'] && !$step['from']->isSameDay($step['to']))
                                            {{ $step['from']->isSameMonth($step['to'])
                                                ? $step['from']->format('j') . '–' . $step['to']->format('j M Y')
                                                : $step['from']->format('j M') . ' – ' . $step['to']->format('j M Y') }}
                                        @else
                                            {{ $step['to']->format('j M Y') }}
                                        @endif
                                        <span class="ad-step-note">{{ $step['note'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Their own submissions. --}}
        <div class="col-lg-6">
            <div class="ad-card">
                <div class="ad-card-head">
                    <h5><i class="fas fa-file-alt mr-2"></i> Your abstracts</h5>
                    <a href="{{ route('papers.index') }}" class="btn btn-sm btn-brand-outline">Open Abstracts</a>
                </div>
                <div class="ad-card-body">
                    @if($papers->isEmpty())
                        <p class="text-muted">You have not submitted an abstract yet.</p>
                        @if($abstractWindowOpen)
                            <a href="{{ route('papers.create') }}" class="btn btn-brand">
                                <i class="fas fa-plus mr-1"></i> Submit your first abstract
                            </a>
                        @else
                            <p class="text-muted mb-0"><i class="fas fa-lock mr-1"></i> Abstract submission is closed.</p>
                        @endif
                    @else
                        <p class="text-muted small mb-3">
                            {{ $papers->count() }} submitted &middot; {{ $approved }} approved &middot; {{ $pending }} under screening
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @foreach($papers as $paper)
                                        @php [$label, $style] = $statusStyles[$paper->status] ?? ['Under screening', 'warning']; @endphp
                                        <tr>
                                            <td style="width: 8rem;">
                                                <a href="{{ route('papers.show', $paper->id) }}" class="font-weight-bold">{{ $paper->submission_id }}</a>
                                            </td>
                                            <td>
                                                {{ Str::limit($paper->title, 55) }}
                                                <small class="text-muted d-block">{{ Str::limit($paper->subTrack->name ?? ($paper->track->name ?? ''), 50) }}</small>
                                            </td>
                                            <td class="text-right" style="width: 9rem;">
                                                <span class="badge badge-{{ $style }}">{{ $label }}</span>
                                                @if($paper->status === 'approved')
                                                    <small class="d-block text-muted">{{ $paper->payment_status == 1 ? 'Fee paid' : 'Fee due' }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($abstractWindowOpen && $submissionsLeft > 0)
                            <a href="{{ route('papers.create') }}" class="btn btn-sm btn-brand mt-3">
                                <i class="fas fa-plus mr-1"></i> Submit another ({{ $submissionsLeft }} left)
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- The fee table from the requirement document, with the stage in force marked and
         the delegate's own tier highlighted. --}}
    <div class="ad-card">
        <div class="ad-card-head">
            <h5><i class="fas fa-tags mr-2"></i> Registration fees</h5>
            <span class="badge badge-{{ $currentStage === 'early_bird' ? 'success' : 'secondary' }} px-3 py-2">
                {{ $currentStage === 'early_bird' ? 'Early bird rate in force' : 'Regular rate in force' }}
                @if($currentStage === 'early_bird' && $earlyBirdEndsAt)
                    &middot; until {{ $earlyBirdEndsAt->format('j M Y') }}
                @endif
            </span>
        </div>
        <div class="ad-card-body">
            <div class="table-responsive">
                <table class="table table-sm ad-fees mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right {{ $currentStage === 'early_bird' ? 'is-stage' : '' }}">Early registration</th>
                            <th class="text-right {{ $currentStage !== 'early_bird' ? 'is-stage' : '' }}">Late registration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prices as $price)
                            @php $mine = $profile && $profile->price_id == $price->id; @endphp
                            <tr class="{{ $mine ? 'is-mine' : '' }}">
                                <td>
                                    {{ $price->name }}
                                    @if($mine)
                                        <span class="badge badge-success ml-1">Your category</span>
                                    @endif
                                </td>
                                <td class="text-right {{ $currentStage === 'early_bird' ? 'is-stage' : '' }}">
                                    {{ $price->currency_symbol }}{{ number_format($price->early_bird_price, 0) }}
                                </td>
                                <td class="text-right {{ $currentStage !== 'early_bird' ? 'is-stage' : '' }}">
                                    {{ $price->currency_symbol }}{{ number_format($price->regular_price, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <small class="form-text text-muted mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                At least one author of each accepted paper must register at the full rate for the paper to appear in the
                conference programme and proceedings. Every author listed on an abstract is charged a registration fee.
                @if($profile)
                    Your category and the amount you owe are on <a href="{{ route('my-profile') }}">your profile</a>.
                @endif
            </small>
        </div>
    </div>

    {{-- The author guidelines, in the numbers the portal will actually hold them to. --}}
    <div class="ad-card">
        <div class="ad-card-head">
            <h5><i class="fas fa-clipboard-check mr-2"></i> Author guidelines at a glance</h5>
            <a href="{{ route('author-guidelines') }}" target="_blank" rel="noopener" class="btn btn-sm btn-brand-outline">
                Full guidelines <i class="fas fa-external-link-alt ml-1"></i>
            </a>
        </div>
        <div class="ad-card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Abstract</strong>
                            <p>{{ $abstractMin }}–{{ $abstractMax }} words stating the objective, methodology, findings and
                               relevance to the conference theme, with {{ $keywordsMin }}–{{ $keywordsMax }} keywords.</p>
                        </div>
                    </div>
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>What the abstract must name</strong>
                            <p>Paper title, every author with affiliation and country, the corresponding author's email,
                               and the track you are submitting to.</p>
                        </div>
                    </div>
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Full manuscript</strong>
                            <p>IEEE conference format, {{ $minPages }}–{{ $maxPages }} pages including figures, tables and
                               references. Accepted file types: {{ $formats }}.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>{{ $doubleBlind ? 'Double-blind review' : 'Single-blind review' }}</strong>
                            @if($doubleBlind)
                                <p>The manuscript you upload for review must be anonymised &mdash; no author names,
                                   affiliations or acknowledgements that reveal who wrote it.</p>
                            @else
                                <p>Reviewer identities are withheld from you, but your manuscript may carry author names
                                   and affiliations. It does not need to be anonymised.</p>
                            @endif
                        </div>
                    </div>
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Originality</strong>
                            <p>Every submission must be original, unpublished, and not under review anywhere else.</p>
                        </div>
                    </div>
                    <div class="ad-rule">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Presentation &amp; camera-ready</strong>
                            <p>Each accepted abstract must be presented onsite or online by at least one registered author.
                               Accepted papers then need the camera-ready manuscript and a signed copyright transfer form.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-top">
                <a href="{{ route('author-guidelines') }}#templates" target="_blank" rel="noopener" class="btn btn-sm btn-brand-outline mr-1 mb-1">
                    <i class="fas fa-download mr-1"></i> Templates &amp; forms
                </a>
                <a href="{{ route('tracks') }}" target="_blank" rel="noopener" class="btn btn-sm btn-brand-outline mr-1 mb-1">
                    <i class="fas fa-sitemap mr-1"></i> Tracks &amp; sub-tracks
                </a>
                <a href="{{ route('camera-ready-guidelines') }}" target="_blank" rel="noopener" class="btn btn-sm btn-brand-outline mr-1 mb-1">
                    <i class="fas fa-stamp mr-1"></i> Camera-ready &amp; presentation
                </a>
                <a href="{{ route('accommodation-transportation') }}" target="_blank" rel="noopener" class="btn btn-sm btn-brand-outline mb-1">
                    <i class="fas fa-hotel mr-1"></i> Accommodation &amp; travel
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Both of these used to sit on the shared dashboard, inside the delegates-only
             block. They are conference information a delegate wants, so they moved here
             when that block was removed. --}}
        @if($amenities->isNotEmpty())
            <div class="col-lg-5">
                <div class="ad-card">
                    <div class="ad-card-head">
                        <h5><i class="fas fa-gift mr-2"></i> What your registration includes</h5>
                    </div>
                    <div class="ad-card-body">
                        @foreach($amenities as $amenity)
                            <div class="ad-rule">
                                <i class="fas fa-check-circle"></i>
                                <div><strong>{{ $amenity->name }}</strong></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($allSchedules->isNotEmpty())
            <div class="col-lg-{{ $amenities->isNotEmpty() ? 7 : 12 }}">
                <div class="ad-card">
                    <div class="ad-card-head">
                        <h5><i class="far fa-clock mr-2"></i> Programme schedule</h5>
                    </div>
                    <div class="ad-card-body">
                        {{-- One tab per day. A conference grows to dozens of sessions, and
                             listing every day at once turned this card into most of the
                             page; each pane also scrolls, so a single heavy day cannot do
                             the same on its own. --}}
                        <ul class="nav nav-tabs ad-day-tabs" role="tablist">
                            @foreach($allSchedules as $day => $sessions)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                       data-toggle="tab" role="tab"
                                       href="#ad-day-{{ $loop->index }}">
                                        {{ $day === null || $day === '' ? 'Unscheduled' : 'Day ' . $day }}
                                        <span class="ad-day-count">{{ $sessions->count() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content ad-day-panes">
                            @foreach($allSchedules as $day => $sessions)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="ad-day-{{ $loop->index }}" role="tabpanel">
                                    @foreach($sessions as $session)
                                        <div class="ad-rule">
                                            <i class="far fa-dot-circle"></i>
                                            <div>
                                                <strong>{{ $session->title }}</strong>
                                                <p>
                                                    {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                                    @if($session->subtitle) &middot; {{ $session->subtitle }} @endif
                                                    @if($session->speaker) &middot; {{ $session->speaker->name }} @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
