@extends('layouts.admin')

@section('styles')
@parent
<style>
    /* The conference palette, so the portal reads as the same site as icmria.com. */
    .mp {
        --brand-blue: #0055A0;
        --brand-green: #10BB43;
        --brand-navy: #00396B;
        --brand-grey: #54585B;
        --brand-ink: #1E2430;
        --brand-tint: #F4F7FA;
        color: var(--brand-ink);
    }
    .mp .mp-hero {
        background: linear-gradient(135deg, var(--brand-navy) 0%, var(--brand-blue) 100%);
        color: #fff;
        border-radius: 12px;
        padding: 28px 30px;
        margin-bottom: 24px;
    }
    .mp .mp-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        border: 2px solid rgba(255, 255, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .mp .mp-hero h2 { font-size: 1.6rem; font-weight: 700; margin: 0 0 4px; }
    .mp .mp-hero .mp-email { opacity: .85; font-size: .95rem; }
    .mp .mp-chip {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin: 6px 6px 0 0;
    }
    .mp .mp-chip-green { background: var(--brand-green); border-color: var(--brand-green); }
    .mp .mp-regid {
        background: rgba(0, 0, 0, 0.18);
        border-radius: 10px;
        padding: 12px 18px;
        text-align: center;
    }
    .mp .mp-regid small { display: block; text-transform: uppercase; letter-spacing: 1px; font-size: .68rem; opacity: .8; }
    .mp .mp-regid strong { font-size: 1.15rem; letter-spacing: 1px; }

    .mp .mp-tile {
        background: #fff;
        border: 1px solid #E3E9F0;
        border-top: 3px solid var(--brand-blue);
        border-radius: 10px;
        padding: 18px;
        height: 100%;
    }
    .mp .mp-tile.is-good { border-top-color: var(--brand-green); }
    .mp .mp-tile.is-due { border-top-color: #E8A33D; }
    .mp .mp-tile .mp-tile-label {
        text-transform: uppercase;
        letter-spacing: .8px;
        font-size: .7rem;
        font-weight: 700;
        color: var(--brand-grey);
        margin-bottom: 6px;
    }
    .mp .mp-tile .mp-tile-value { font-size: 1.35rem; font-weight: 700; color: var(--brand-navy); line-height: 1.2; }
    .mp .mp-tile .mp-tile-note { font-size: .8rem; color: var(--brand-grey); margin-top: 4px; }

    .mp .mp-card {
        background: #fff;
        border: 1px solid #E3E9F0;
        border-radius: 10px;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .mp .mp-card-head {
        background: var(--brand-tint);
        border-bottom: 1px solid #E3E9F0;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .mp .mp-card-head h5 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--brand-navy); }
    .mp .mp-card-body { padding: 20px; }

    .mp .mp-todo {
        border-left: 4px solid var(--brand-blue);
        background: var(--brand-tint);
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 12px;
    }
    .mp .mp-todo.tone-warning { border-left-color: #E8A33D; background: #FDF6EA; }
    .mp .mp-todo.tone-danger { border-left-color: #C0392B; background: #FBEDEB; }
    .mp .mp-todo.tone-info { border-left-color: var(--brand-green); background: #EDF9F1; }
    .mp .mp-todo strong { display: block; color: var(--brand-navy); margin-bottom: 3px; }
    .mp .mp-todo p { margin: 0; font-size: .875rem; color: var(--brand-grey); }

    .mp .mp-field { padding: 9px 0; border-bottom: 1px dashed #E3E9F0; display: flex; justify-content: space-between; }
    .mp .mp-field:last-child { border-bottom: 0; }
    .mp .mp-field dt { font-weight: 600; color: var(--brand-grey); font-size: .85rem; margin: 0; flex: 0 0 45%; }
    .mp .mp-field dd { margin: 0; text-align: right; color: var(--brand-ink); flex: 1; word-break: break-word; }

    .mp .btn-brand { background: var(--brand-blue); border-color: var(--brand-blue); color: #fff; }
    .mp .btn-brand:hover { background: var(--brand-navy); border-color: var(--brand-navy); color: #fff; }
    .mp .btn-brand-green { background: var(--brand-green); border-color: var(--brand-green); color: #fff; }
    .mp .btn-brand-green:hover { background: #0e9e39; border-color: #0e9e39; color: #fff; }
    .mp .btn-brand-outline { border-color: var(--brand-blue); color: var(--brand-blue); background: transparent; }
    .mp .btn-brand-outline:hover { background: var(--brand-blue); color: #fff; }

    .mp .mp-locked {
        background: var(--brand-tint);
        border: 1px dashed #C9D6E4;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: .82rem;
        color: var(--brand-grey);
    }
    .mp table thead th {
        background: var(--brand-tint);
        color: var(--brand-navy);
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 2px solid #E3E9F0;
    }
    @media (max-width: 575px) {
        .mp .mp-field { display: block; }
        .mp .mp-field dd { text-align: left; margin-top: 2px; }
    }
</style>
@endsection

@section('content')
@php
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');

    $paid = $profile && $profile->payment_status == '1';
    $currency = $profile->currency ?? 'BDT';

    $approved = $papers->where('status', 'approved')->count();
    $pending = $papers->filter(fn ($p) => $p->status === null || $p->status === 'pending')->count();
    $rejected = $papers->where('status', 'rejected')->count();

    // Fees are charged per listed author per abstract, so the number that decides what is
    // owed is the total of the author rows, not the number of people. Somebody named on
    // two of your abstracts is billed twice, so both figures are worth showing when they
    // differ: the billable total, and how many distinct people that is.
    $authorRows = $papers->flatMap(fn ($paper) => $paper->authors);
    $authorTotal = $authorRows->count();
    $distinctAuthors = $authorRows
        ->map(fn ($author) => strtolower(trim((string) $author->email)))
        ->filter()
        ->unique()
        ->count();

    $statusStyles = [
        'approved' => ['Approved', 'success'],
        'pending'  => ['Under screening', 'warning'],
        'rejected' => ['Not accepted', 'secondary'],
    ];
@endphp

<div class="mp">

    <div class="mp-hero">
        <div class="row align-items-center">
            <div class="col-auto d-none d-sm-block">
                <div class="mp-avatar">{{ $initials ?: '—' }}</div>
            </div>
            <div class="col">
                <h2>{{ $user->name }}</h2>
                <div class="mp-email"><i class="fas fa-envelope mr-1"></i> {{ $user->email }}</div>
                <div>
                    @if($profile)
                        <span class="mp-chip">{{ $profile->is_author ? 'Author' : 'Participant' }}</span>
                        @if($profile->participation_mode)
                            <span class="mp-chip">{{ ucfirst($profile->participation_mode) }}</span>
                        @endif
                        @if($profile->country)
                            <span class="mp-chip">{{ $profile->country->name }}</span>
                        @endif
                        <span class="mp-chip {{ $paid ? 'mp-chip-green' : '' }}">
                            {{ $paid ? 'Registration paid' : 'Payment pending' }}
                        </span>
                    @else
                        <span class="mp-chip">Not registered yet</span>
                    @endif
                </div>
            </div>
            @if($profile)
                <div class="col-md-auto mt-3 mt-md-0">
                    <div class="mp-regid">
                        <small>Registration ID</small>
                        <strong>{{ $profile->registration_id ?: 'Not issued yet' }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$profile)
        <div class="mp-card">
            <div class="mp-card-body">
                <h5 class="mb-2" style="color: var(--brand-navy);">Your registration is not complete</h5>
                <p class="text-muted">
                    You have an account, but we have no registration details for you yet — no delegate category,
                    no fee, and nothing we can issue a registration ID against.
                </p>
                <a href="{{ route('book-ticket') }}" class="btn btn-brand">
                    <i class="fas fa-arrow-right mr-1"></i> Complete your registration
                </a>
            </div>
        </div>
    @else

        {{-- Where things stand, at a glance. Five across on a wide screen, three then two
             on a tablet, two per row on a phone. --}}
        <div class="row mb-4">
            <div class="col-6 col-md-4 col-xl mb-3">
                <div class="mp-tile {{ $paid ? 'is-good' : 'is-due' }}">
                    <div class="mp-tile-label">Registration</div>
                    <div class="mp-tile-value">{{ $paid ? 'Paid' : 'Unpaid' }}</div>
                    <div class="mp-tile-note">
                        {{ $paid ? 'Your place is confirmed.' : 'The fee is still outstanding.' }}
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl mb-3">
                <div class="mp-tile {{ $paid ? 'is-good' : 'is-due' }}">
                    <div class="mp-tile-label">{{ $paid ? 'Amount paid' : 'Amount due' }}</div>
                    <div class="mp-tile-value">{{ $currency }} {{ number_format($profile->pay_amount ?? 0, 2) }}</div>
                    <div class="mp-tile-note">
                        {{ ucwords(str_replace('_', ' ', \App\Services\PricingService::currentStage())) }} rate
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl mb-3">
                <div class="mp-tile">
                    <div class="mp-tile-label">Abstracts</div>
                    <div class="mp-tile-value">{{ $papers->count() }}</div>
                    <div class="mp-tile-note">
                        @php
                            $breakdown = collect([
                                $approved ? "{$approved} approved" : null,
                                $pending ? "{$pending} under screening" : null,
                                $rejected ? "{$rejected} not accepted" : null,
                            ])->filter();
                        @endphp
                        {{ $breakdown->isEmpty() ? 'None submitted' : $breakdown->implode(' · ') }}
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl mb-3">
                <div class="mp-tile">
                    <div class="mp-tile-label">Authors</div>
                    <div class="mp-tile-value">{{ $authorTotal }}</div>
                    <div class="mp-tile-note">
                        @if($papers->isEmpty())
                            No abstract submitted
                        @else
                            across {{ $papers->count() }} abstract{{ $papers->count() === 1 ? '' : 's' }}
                            @if($distinctAuthors && $distinctAuthors !== $authorTotal)
                                &middot; {{ $distinctAuthors }} distinct people
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl mb-3">
                <div class="mp-tile">
                    <div class="mp-tile-label">Workshops</div>
                    <div class="mp-tile-value">{{ $user->schedules->count() }}</div>
                    <div class="mp-tile-note">
                        {{ $user->schedules->isEmpty() ? 'No session selected' : 'Sessions you have chosen' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- What is actually waiting on the delegate. --}}
        <div class="mp-card">
            <div class="mp-card-head">
                <h5><i class="fas fa-tasks mr-2"></i> What needs your attention</h5>
            </div>
            <div class="mp-card-body">
                @forelse($todo as $item)
                    <div class="mp-todo tone-{{ $item['tone'] }}">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['body'] }}</p>
                        @if($item['url'] && $item['action'])
                            <a href="{{ $item['url'] }}" class="btn btn-sm btn-brand-outline mt-2">
                                {{ $item['action'] }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="mb-0 text-muted">
                        <i class="fas fa-check-circle mr-1" style="color: var(--brand-green);"></i>
                        Nothing is waiting on you. We will email you if that changes.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Registration fee, for a participant who is not paying per abstract. --}}
        @if(!$profile->is_author && !$paid)
            <div class="mp-card">
                <div class="mp-card-head">
                    <h5><i class="fas fa-credit-card mr-2"></i> Pay your registration fee</h5>
                </div>
                <div class="mp-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <p class="text-muted mb-2">
                                Your place is held once the fee clears. The amount follows the current pricing stage —
                                refresh it if the deadline has moved since you registered.
                            </p>
                            <h4 class="mb-0" style="color: var(--brand-navy);">
                                {{ $currency }} {{ number_format($profile->pay_amount ?? 0, 2) }}
                            </h4>
                        </div>
                        <div class="col-md-5 text-md-right mt-3 mt-md-0">
                            @if($isPaymentOpen)
                                <form action="{{ route('profile.recalculate-fee') }}" method="POST" class="d-inline-block mr-1">
                                    @csrf
                                    <button type="submit" class="btn btn-brand-outline" title="Update the amount to the current pricing stage">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh price
                                    </button>
                                </form>
                                <button class="btn btn-brand-green px-4" data-toggle="modal" data-target="#registrationPayModal">
                                    <i class="fas fa-lock mr-1"></i> Review &amp; pay
                                </button>
                            @else
                                <span class="badge badge-danger p-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Payment closed {{ optional($paymentLastDate)->format('j M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="registrationPayModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title font-weight-bold" style="color: #00396B;">Registration summary</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body pt-3 pb-4">
                            <div class="p-4 rounded border mb-4" style="background: #F4F7FA;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Registration type</span>
                                    <span class="font-weight-bold">{{ $profile->is_author ? 'Author' : 'Participant' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Country</span>
                                    <span class="font-weight-bold">{{ $profile->country->name ?? 'International' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Participation</span>
                                    <span class="font-weight-bold">{{ ucfirst($profile->participation_mode ?? '—') }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="h5 mb-0">Registration fee</span>
                                    <span class="h4 mb-0 font-weight-bold" style="color: #0055A0;">
                                        {{ $currency }} {{ number_format($profile->pay_amount ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('payNow') }}" method="post">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $profile->user_id }}">
                                {{-- The old REGSP300 special-discount flag is not carried over: profiles
                                     has no special_coupon column, so the condition guarding it could
                                     never be true. --}}
                                <button type="submit" class="btn btn-brand-green btn-block btn-lg" style="border-radius: 8px;">
                                    <i class="fas fa-lock mr-2"></i> Proceed to secure checkout
                                </button>
                            </form>
                            <p class="text-center mt-3 small text-muted">
                                <i class="fas fa-shield-alt mr-1"></i> Payment is handled by OneCard
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Abstract fees: every listed author pays, settled in one transaction. --}}
        @if($unpaidPapers->isNotEmpty() && $profile->author_list_confirmed)
            <div class="mp-card">
                <div class="mp-card-head">
                    <h5><i class="fas fa-shopping-cart mr-2"></i> Pay for your approved abstracts</h5>
                </div>
                <div class="mp-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <p class="text-muted mb-0">
                                <strong>{{ $unpaidPapers->count() }}</strong> approved abstract{{ $unpaidPapers->count() === 1 ? '' : 's' }}
                                {{ $unpaidPapers->count() === 1 ? 'is' : 'are' }} awaiting payment. Every author listed on an
                                abstract is charged a registration fee; you can settle them all at once.
                            </p>
                        </div>
                        <div class="col-md-5 text-md-right mt-3 mt-md-0">
                            @if($isPaymentOpen)
                                <form action="{{ route('profile.recalculate-fee') }}" method="POST" class="d-inline-block mr-1">
                                    @csrf
                                    <button type="submit" class="btn btn-brand-outline">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh price
                                    </button>
                                </form>
                                <button class="btn btn-brand-green px-4" data-toggle="modal" data-target="#bulkPaymentModal">
                                    <i class="fas fa-credit-card mr-1"></i> Review &amp; pay all
                                </button>
                            @else
                                <span class="badge badge-danger p-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Payment closed {{ optional($paymentLastDate)->format('j M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="bulkPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title font-weight-bold" style="color: #00396B;">Abstract payment review</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body pt-3 pb-4">
                            @php $totalPrice = 0; $lastCurrency = $currency; @endphp
                            <div class="p-3 rounded border mb-4" style="background: #F4F7FA;">
                                <table class="table table-borderless table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Abstract</th>
                                            <th>Rate</th>
                                            <th class="text-right">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unpaidPapers as $up)
                                            @php
                                                $pricing = \App\Services\PricingService::calculatePaperCost($profile, $up);
                                                $totalPrice += $pricing['final_price'];
                                                $lastCurrency = $pricing['currency'];
                                            @endphp
                                            <tr>
                                                <td class="font-weight-bold">
                                                    {{ $up->submission_id }}
                                                    <small class="text-muted d-block">
                                                        {{ $pricing['authors_count'] }} author{{ $pricing['authors_count'] > 1 ? 's' : '' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    {{ ucwords(str_replace('_', ' ', $pricing['stage'])) }}
                                                    @if($pricing['discount'] > 0)
                                                        <small class="d-block" style="color: #10BB43;">
                                                            −{{ $pricing['currency'] }} {{ number_format($pricing['individual_discount'], 2) }} per author
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-right">{{ $pricing['currency'] }} {{ number_format($pricing['final_price'], 2) }}</td>
                                            </tr>
                                            @if($pricing['authors_count'] > 1)
                                                <tr>
                                                    <td colspan="3" class="pt-0 pb-3">
                                                        <ul class="mb-0 small pl-3 text-muted">
                                                            @foreach($up->authors as $author)
                                                                <li>
                                                                    {{ $author->name }}
                                                                    — <strong style="color: #0055A0;">
                                                                        {{ $pricing['currency'] }}
                                                                        {{ number_format($pricing['author_fees'][$author->id] ?? $pricing['individual_final_price'], 2) }}
                                                                    </strong>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        <tr class="border-top">
                                            <td colspan="2" class="font-weight-bold pt-3" style="font-size: 1.05rem;">Total</td>
                                            <td class="text-right font-weight-bold pt-3" style="font-size: 1.2rem; color: #0055A0;">
                                                {{ $lastCurrency }} {{ number_format($totalPrice, 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('payNowPapers') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                @foreach($unpaidPapers as $up)
                                    <input type="hidden" name="paper_ids[]" value="{{ $up->id }}">
                                @endforeach
                                <button type="submit" class="btn btn-brand-green btn-block btn-lg" style="border-radius: 8px;">
                                    <i class="fas fa-lock mr-2"></i> Proceed to secure checkout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Theirs to change. --}}
            <div class="col-lg-6">
                <div class="mp-card">
                    <div class="mp-card-head">
                        <h5><i class="fas fa-user-circle mr-2"></i> Your details</h5>
                        <a href="{{ route('edit-profile', ['id' => $profile->id]) }}" class="btn btn-sm btn-brand">
                            <i class="fas fa-pen mr-1"></i> Edit
                        </a>
                    </div>
                    <div class="mp-card-body">
                        <dl class="mb-0">
                            <div class="mp-field"><dt>Name</dt><dd>{{ trim($profile->first_name . ' ' . $profile->last_name) ?: $user->name }}</dd></div>
                            <div class="mp-field"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                            <div class="mp-field"><dt>WhatsApp</dt><dd>{{ $profile->whatsapp_number ?: '—' }}</dd></div>
                            <div class="mp-field"><dt>Designation</dt><dd>{{ $profile->designation ?: '—' }}</dd></div>
                            <div class="mp-field"><dt>Department</dt><dd>{{ $profile->department ?: '—' }}</dd></div>
                            <div class="mp-field"><dt>Institution</dt><dd>{{ $profile->institution ?: '—' }}</dd></div>
                            <div class="mp-field">
                                <dt>ORCID iD</dt>
                                <dd>
                                    @if($profile->orcid_id)
                                        <a href="https://orcid.org/{{ $profile->orcid_id }}" target="_blank" rel="noopener">{{ $profile->orcid_id }}</a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="mp-field"><dt>Country</dt><dd>{{ $profile->country->name ?? '—' }}</dd></div>
                            <div class="mp-field"><dt>Participation</dt><dd>{{ ucfirst($profile->participation_mode ?? '—') }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Not theirs to change: shown so they can check it and query it. --}}
            <div class="col-lg-6">
                <div class="mp-card">
                    <div class="mp-card-head">
                        <h5><i class="fas fa-file-invoice-dollar mr-2"></i> Registration &amp; payment</h5>
                        <span class="badge badge-{{ $paid ? 'success' : 'warning' }} px-3 py-2">
                            {{ $paid ? 'Paid' : 'Unpaid' }}
                        </span>
                    </div>
                    <div class="mp-card-body">
                        <dl class="mb-3">
                            <div class="mp-field">
                                <dt>Registration ID</dt>
                                <dd>{{ $profile->registration_id ?: 'Issued once your payment clears' }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>Registration type</dt>
                                <dd>{{ $profile->is_author ? 'Author' : 'Participant' }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>Delegate category</dt>
                                <dd>{{ $profile->price->name ?? ucfirst($profile->price->category ?? '—') }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>Current pricing stage</dt>
                                <dd>{{ ucwords(str_replace('_', ' ', \App\Services\PricingService::currentStage())) }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>{{ $paid ? 'Amount paid' : 'Amount due' }}</dt>
                                <dd class="font-weight-bold">{{ $currency }} {{ number_format($profile->pay_amount ?? 0, 2) }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>Author list confirmed</dt>
                                <dd>{{ $profile->author_list_confirmed ? 'Yes' : 'Not yet' }}</dd>
                            </div>
                            <div class="mp-field">
                                <dt>Registered on</dt>
                                <dd>{{ optional($profile->created_at)->format('j M Y') ?: '—' }}</dd>
                            </div>
                            @if($paymentLastDate)
                                <div class="mp-field">
                                    <dt>Payment deadline</dt>
                                    <dd>{{ $paymentLastDate->format('j M Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                        <div class="mp-locked">
                            <i class="fas fa-lock mr-1"></i>
                            These are set by the organising committee and cannot be edited here. If something looks wrong,
                            write to us rather than trying to change it — the registration ID and the fee are issued against
                            the payment record.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submissions. --}}
        <div class="mp-card">
            <div class="mp-card-head">
                <h5><i class="fas fa-file-alt mr-2"></i> Your abstracts</h5>
                <a href="{{ route('papers.index') }}" class="btn btn-sm btn-brand-outline">Open the Abstracts page</a>
            </div>
            <div class="mp-card-body">
                @if($papers->isEmpty())
                    <p class="text-muted mb-0">You have not submitted an abstract yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 8rem;">ID</th>
                                    <th>Title</th>
                                    <th>Track</th>
                                    <th style="width: 14rem;">Authors</th>
                                    <th style="width: 10rem;">Screening</th>
                                    <th style="width: 8rem;">Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($papers as $paper)
                                    @php
                                        [$label, $style] = $statusStyles[$paper->status] ?? ['Under screening', 'warning'];
                                    @endphp
                                    <tr>
                                        <td>
                                            @can('paper_show')
                                                <a href="{{ route('papers.show', $paper->id) }}" class="font-weight-bold">{{ $paper->submission_id }}</a>
                                            @else
                                                <strong>{{ $paper->submission_id }}</strong>
                                            @endcan
                                        </td>
                                        <td>{{ $paper->title }}</td>
                                        <td>
                                            <small>{{ $paper->track->name ?? '—' }}</small>
                                            @if($paper->subTrack)
                                                <small class="text-muted d-block">{{ $paper->subTrack->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">
                                                {{ $paper->authors->count() }} author{{ $paper->authors->count() === 1 ? '' : 's' }}
                                            </span>
                                            @if($paper->authors->isNotEmpty())
                                                <small class="text-muted d-block mt-1">
                                                    {{ $paper->authors->pluck('name')->filter()->implode(', ') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-{{ $style }}">{{ $label }}</span></td>
                                        <td>
                                            @if($paper->payment_status == 1)
                                                <span class="badge badge-success">Paid</span>
                                            @elseif($paper->status === 'approved')
                                                <span class="badge badge-warning">Due</span>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right border-top">Total</th>
                                    <th class="border-top">
                                        {{ $authorTotal }} author{{ $authorTotal === 1 ? '' : 's' }}
                                        @if($distinctAuthors && $distinctAuthors !== $authorTotal)
                                            <small class="text-muted font-weight-normal d-block">
                                                {{ $distinctAuthors }} distinct people
                                            </small>
                                        @endif
                                    </th>
                                    <th colspan="2" class="border-top"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <small class="form-text text-muted mt-2">
                        Every author listed on an abstract is charged a registration fee, so this total is what the
                        amount above is worked out from. Somebody named on two of your abstracts counts once for each.
                    </small>
                @endif
            </div>
        </div>

        {{-- Workshops. --}}
        <div class="mp-card">
            <div class="mp-card-head">
                <h5><i class="fas fa-chalkboard-teacher mr-2"></i> Workshops you have chosen</h5>
            </div>
            <div class="mp-card-body">
                @forelse($user->schedules as $schedule)
                    <div class="mp-field">
                        <dt>{{ $schedule->title }}</dt>
                        <dd>
                            Day {{ $schedule->day_number }}
                            @if($schedule->start_time)
                                · {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                            @endif
                        </dd>
                    </div>
                @empty
                    <p class="text-muted mb-0">
                        You have not selected a workshop.
                        Pick one from <a href="{{ route('edit-profile', ['id' => $profile->id]) }}">your details</a>.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Payment attempts, so a failed transaction is visible rather than silent. --}}
        <div class="mp-card">
            <div class="mp-card-head">
                <h5><i class="fas fa-receipt mr-2"></i> Payment history</h5>
            </div>
            <div class="mp-card-body">
                @if($payments->isEmpty())
                    <p class="text-muted mb-0">No payment has been attempted yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 10rem;">Date</th>
                                    <th>Reference</th>
                                    <th>For</th>
                                    <th class="text-right" style="width: 9rem;">Amount</th>
                                    <th style="width: 8rem;">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    @php
                                        [$payLabel, $payStyle] = match ((int) $payment->status) {
                                            1 => ['Successful', 'success'],
                                            2 => ['Failed', 'danger'],
                                            default => ['Not completed', 'secondary'],
                                        };
                                    @endphp
                                    <tr>
                                        <td><small>{{ optional($payment->created_at)->format('j M Y, g:i A') }}</small></td>
                                        <td><small class="text-muted">{{ $payment->reff_id ?: '—' }}</small></td>
                                        <td><small>{{ $payment->service_type === 'paper_event' ? 'Abstract fee' : 'Registration' }}</small></td>
                                        <td class="text-right">{{ $payment->currency_code ?? 'BDT' }} {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td><span class="badge badge-{{ $payStyle }}">{{ $payLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="form-text text-muted mt-2">
                        An attempt shown as not completed means the checkout was started but never finished. It charges
                        nothing; start again from the button above.
                    </small>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
