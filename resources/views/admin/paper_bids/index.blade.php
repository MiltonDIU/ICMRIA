@extends('layouts.admin')

@section('styles')
@parent
<style>
    /* University Conference Palette & Paper Card Styles */
    :root {
        --conf-navy: #1e3a8a;
        --conf-blue: #2563eb;
        --conf-teal: #0d9488;
        --conf-green: #10b981;
    }

    .compact-paper-card {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
        position: relative;
    }
    .compact-paper-card:hover {
        border-color: #94a3b8;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.07);
    }
    /* Distinct left-border highlight for papers matching expertise */
    .border-match-highlight {
        border-left: 4px solid #10b981 !important;
        background: linear-gradient(90deg, #f0fdf4 0%, #ffffff 14%);
    }

    .compact-title {
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.35;
        color: #0f172a;
    }

    /* Keyword and Match Badges */
    .badge-keyword-match {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border: 1px solid #059669 !important;
        font-weight: 600 !important;
        font-size: 0.76rem !important;
        padding: 0.25rem 0.55rem !important;
        border-radius: 4px !important;
    }
    .badge-match-indicator {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #6ee7b7 !important;
        font-size: 0.76rem !important;
        padding: 0.22rem 0.55rem !important;
        border-radius: 4px !important;
    }

    /* Compact Segmented Radio Group */
    .compact-bid-group {
        display: inline-flex;
        border-radius: 6px;
        overflow: hidden;
        border: 1.5px solid #cbd5e1;
        background-color: #f8fafc;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .compact-bid-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 13px;
        font-size: 0.83rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        border-right: 1px solid #cbd5e1;
        margin: 0 !important;
        user-select: none;
        transition: all 0.15s ease;
        background-color: #ffffff;
    }
    .compact-bid-btn:last-child {
        border-right: none;
    }
    .compact-bid-btn:hover:not(.disabled) {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .compact-bid-btn.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Active States for Segmented Group */
    .compact-bid-btn.bid-btn-want.active {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
    }
    .compact-bid-btn.bid-btn-can.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #1d4ed8 !important;
    }
    .compact-bid-btn.bid-btn-neutral.active {
        background-color: #64748b !important;
        color: #ffffff !important;
        border-color: #475569 !important;
    }
    .compact-bid-btn.bid-btn-conflict.active {
        background-color: #ef4444 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
    }

    /* Sticky Bottom Floating Progress Bar */
    .bidding-floating-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1020;
        background-color: #ffffff;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
    }

    /* Academic Conference Filter Pills */
    .filter-pill {
        font-size: 0.84rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        margin-right: 0.45rem;
        margin-bottom: 0.45rem;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        transition: all 0.18s ease-in-out;
        border: 1.5px solid #cbd5e1;
        background-color: #ffffff;
        color: #334155 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        line-height: 1.4;
    }
    .filter-pill:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a !important;
        transform: translateY(-1px);
    }

    /* Distinct Active States for Filter Pills with Guaranteed High-Contrast White Text */
    .filter-pill.pill-all.is-active {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        border-color: #1e3a8a !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.28);
    }
    .filter-pill.pill-all.is-active span,
    .filter-pill.pill-all.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-unmarked.is-active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.28);
    }
    .filter-pill.pill-unmarked.is-active span,
    .filter-pill.pill-unmarked.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-matched.is-active {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        border-color: #047857 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.28);
    }
    .filter-pill.pill-matched.is-active span,
    .filter-pill.pill-matched.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-want.is-active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border-color: #059669 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.28);
    }
    .filter-pill.pill-want.is-active span,
    .filter-pill.pill-want.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-can.is-active {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        border-color: #0369a1 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.28);
    }
    .filter-pill.pill-can.is-active span,
    .filter-pill.pill-can.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-neutral.is-active {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
        border-color: #475569 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(100, 116, 139, 0.28);
    }
    .filter-pill.pill-neutral.is-active span,
    .filter-pill.pill-neutral.is-active i {
        color: #ffffff !important;
    }

    .filter-pill.pill-conflict.is-active {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.28);
    }
    .filter-pill.pill-conflict.is-active span,
    .filter-pill.pill-conflict.is-active i {
        color: #ffffff !important;
    }

    /* Inside Count Badge for Pills */
    .filter-pill .pill-count {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        margin-left: 0.45rem;
        font-size: 0.74rem;
        font-weight: 700;
        border-radius: 12px;
        background-color: #f1f5f9;
        color: #475569;
        line-height: 1.3;
        transition: all 0.15s ease;
    }
    .filter-pill:hover .pill-count {
        background-color: #e2e8f0;
        color: #0f172a;
    }
    .filter-pill.is-active .pill-count {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }
</style>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>@endif

@php
    $reviewerExpertise = $reviewerExpertise ?? auth()->user()->allExpertise();
@endphp

<!-- Header & Instructions Card -->
<div class="card mb-3 shadow-sm border" style="border-left: 4px solid #1e3a8a !important;">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-gavel text-primary mr-2"></i> Paper Bidding
            </h5>
            <small class="text-muted">
                Mark your preferences for upcoming review assignments in your assigned tracks.
            </small>
        </div>
        <div class="mt-2 mt-md-0">
            @if($biddingOpen)
                <span class="badge badge-success px-3 py-2 font-weight-bold shadow-sm">
                    <i class="fas fa-lock-open mr-1"></i> Bidding is Open
                </span>
            @else
                <span class="badge badge-secondary px-3 py-2 font-weight-bold">
                    <i class="fas fa-lock mr-1"></i> Bidding is Closed
                </span>
            @endif
        </div>
    </div>
    <div class="card-body py-2.5">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="text-muted small mr-3">
                <span class="badge badge-success px-2 py-1 mr-1">Want</span> & <span class="badge badge-primary px-2 py-1 mr-1">Can</span> put you ahead when papers are assigned.
                <span class="badge badge-secondary px-2 py-1 mr-1">Neutral</span> leaves it to subject match.
                <span class="badge badge-danger px-2 py-1 mr-1">Conflict</span> ensures you won't be assigned that paper.
            </div>
            <div class="small text-muted mt-2 mt-md-0">
                <span class="badge badge-keyword-match mr-1">
                    <i class="fas fa-check-circle mr-1"></i> Green keywords
                </span>
                match your research expertise
            </div>
        </div>
        <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
            <div class="small text-primary font-weight-bold">
                <i class="fas fa-magic mr-1"></i> Auto-Save Enabled: Clicking any radio button saves immediately. No manual save button required!
            </div>
            <div class="small text-muted">
                Papers matching your expertise appear at the <strong>TOP</strong> by default.
            </div>
        </div>
    </div>
</div>

@if(!$inPool)
    <div class="alert alert-warning shadow-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        You are not currently in any track's reviewer pool, so there are no papers available to bid on.
        A track chair can add you to their pool under <strong>Reviewers by Track</strong>.
    </div>
@elseif(!$biddingOpen)
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-info-circle mr-2"></i>
        Bidding is currently closed. Your saved choices are shown below for reference and can no longer be modified.
    </div>
@endif

<!-- Advanced Filter Bar -->
@if($inPool)
<div class="card mb-3 shadow-sm border">
    <div class="card-body p-3 bg-light rounded">
        <form method="GET" action="{{ route('admin.paper-bids.index') }}" id="bids-filter-form">
            <!-- Hidden filter parameter to preserve pill filter -->
            <input type="hidden" name="filter" value="{{ $filter }}">

            <div class="row align-items-end">
                <!-- Track / Sub-track Filter (Strictly Reviewer's Assigned Scopes) -->
                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">
                        <i class="fas fa-layer-group mr-1"></i> Assigned Track Scope
                    </label>
                    <select name="track_scope" class="form-control form-control-sm">
                        <option value="">All My Assigned Tracks ({{ count($assignedScopes) }})</option>
                        @foreach($assignedScopes as $scope)
                            <option value="{{ $scope['key'] }}" {{ $trackScope === $scope['key'] ? 'selected' : '' }}>
                                {{ $scope['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range From -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">
                        <i class="far fa-calendar-alt mr-1"></i> Date From
                    </label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>

                <!-- Date Range To -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">
                        <i class="far fa-calendar-alt mr-1"></i> Date To
                    </label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>

                <!-- Sorting Dropdown (Expertise Match on Top by Default) -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">
                        <i class="fas fa-sort-amount-down mr-1"></i> Sort By
                    </label>
                    <select name="sort" class="form-control form-control-sm">
                        <option value="match" {{ $sort === 'match' ? 'selected' : '' }}>★ Best Match First</option>
                        <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Newest Submissions</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest Submissions</option>
                        <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>Title (A – Z)</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                    <label class="small font-weight-bold text-secondary mb-1">
                        <i class="fas fa-search mr-1"></i> Search
                    </label>
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Title, ID, keyword..." class="form-control form-control-sm">
                </div>

                <!-- Filter Actions -->
                <div class="col-md-1 col-sm-12 mb-2 mb-md-0 text-right">
                    <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold shadow-sm" title="Apply filters">
                        Filter
                    </button>
                </div>
            </div>

            @if($trackScope || $dateFrom || $dateTo || $searchQuery || ($sort && $sort !== 'match'))
                <div class="mt-2 text-right">
                    <a href="{{ route('admin.paper-bids.index', ['filter' => $filter]) }}" class="small text-danger font-weight-bold">
                        <i class="fas fa-times-circle mr-1"></i> Reset Filters
                    </a>
                </div>
            @endif
        </form>

        <!-- Status Filter Tabs / Pills with University Conference Colors -->
        <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center">
            <span class="small font-weight-bold text-muted mr-2 mb-1">Status:</span>

            @php
                $queryParams = request()->except(['filter', 'page']);
            @endphp

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'all'])) }}"
               class="filter-pill pill-all {{ $filter === 'all' ? 'is-active' : '' }}">
                <span>All</span>
                <span class="pill-count">{{ $total }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'unmarked'])) }}"
               class="filter-pill pill-unmarked {{ $filter === 'unmarked' ? 'is-active' : '' }}">
                <i class="far fa-circle mr-1"></i>
                <span>Unmarked</span>
                <span class="pill-count">{{ $totalUnmarked }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'matched'])) }}"
               class="filter-pill pill-matched {{ $filter === 'matched' ? 'is-active' : '' }}">
                <i class="fas fa-star mr-1 text-warning"></i>
                <span>Expertise Match</span>
                <span class="pill-count">{{ $totalMatched }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'want'])) }}"
               class="filter-pill pill-want {{ $filter === 'want' ? 'is-active' : '' }}">
                <i class="fas fa-heart mr-1"></i>
                <span>Want</span>
                <span class="pill-count">{{ $countWant }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'can'])) }}"
               class="filter-pill pill-can {{ $filter === 'can' ? 'is-active' : '' }}">
                <i class="fas fa-thumbs-up mr-1"></i>
                <span>Can</span>
                <span class="pill-count">{{ $countCan }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'neutral'])) }}"
               class="filter-pill pill-neutral {{ $filter === 'neutral' ? 'is-active' : '' }}">
                <i class="fas fa-minus-circle mr-1"></i>
                <span>Neutral</span>
                <span class="pill-count">{{ $countNeutral }}</span>
            </a>

            <a href="{{ route('admin.paper-bids.index', array_merge($queryParams, ['filter' => 'conflict'])) }}"
               class="filter-pill pill-conflict {{ $filter === 'conflict' ? 'is-active' : '' }}">
                <i class="fas fa-ban mr-1"></i>
                <span>Conflict</span>
                <span class="pill-count">{{ $countConflict }}</span>
            </a>
        </div>
    </div>
</div>
@endif

<!-- Paper Cards List -->
@if($papers->isEmpty())
    @if($inPool)
        <div class="card shadow-sm border-0">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <h6 class="font-weight-bold">No papers found</h6>
                <p class="mb-0 small">
                    @if($filter === 'unmarked' && $total > 0)
                        You have marked all {{ $total }} papers in your review pool!
                    @elseif($filter === 'matched')
                        No papers currently match your research keywords in the selected scope.
                    @else
                        No papers match your selected track scope, date range, or filters.
                    @endif
                </p>
                @if($filter !== 'all' || $trackScope || $dateFrom || $dateTo || $searchQuery)
                    <a href="{{ route('admin.paper-bids.index') }}" class="btn btn-outline-primary btn-sm mt-3 font-weight-bold">
                        View All Papers
                    </a>
                @endif
            </div>
        </div>
    @endif
@else
    <div class="paper-bids-container mb-4">
        @foreach($papers as $paper)
            @php
                $current = $bids[$paper->id] ?? null;
                $paperKeywords = \App\Services\SubmissionRules::splitKeywords($paper->keywords);
                $matchCount = $paper->match_count ?? 0;
            @endphp
            <div class="card compact-paper-card mb-2 shadow-sm border {{ $matchCount > 0 ? 'border-match-highlight' : '' }}" id="paper-card-{{ $paper->id }}">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                        <!-- Left Info Column -->
                        <div class="paper-info-col mr-lg-3 mb-2 mb-lg-0" style="flex: 1 1 auto; min-width: 0;">
                            <!-- Top metadata row -->
                            <div class="d-flex align-items-center flex-wrap mb-1">
                                <span class="badge badge-dark px-2 py-1 font-weight-bold mr-2">{{ $paper->submission_id }}</span>
                                <span class="badge badge-light border text-muted px-2 py-1 mr-2">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ $paper->created_at?->format('M d, Y') ?? 'N/A' }}
                                </span>
                                <span class="badge badge-light border text-secondary px-2 py-1 mr-2">
                                    <i class="fas fa-layer-group mr-1 text-secondary"></i>
                                    {{ $paper->track->name ?? 'Track' }}@if($paper->subTrack) &rsaquo; <strong>{{ $paper->subTrack->name }}</strong>@endif
                                </span>
                                @if($assignedIds->contains($paper->id))
                                    <span class="badge badge-primary px-2 py-1 mr-2">
                                        <i class="fas fa-user-check mr-1"></i> Assigned to you
                                    </span>
                                @endif
                                @if($matchCount > 0)
                                    <span class="badge badge-match-indicator px-2 py-1 font-weight-bold mr-2" title="Matches your research expertise">
                                        <i class="fas fa-star text-warning mr-1"></i> {{ $matchCount }} keyword match{{ $matchCount > 1 ? 'es' : '' }}
                                    </span>
                                @endif
                            </div>

                            <!-- Paper Title -->
                            <h6 class="compact-title mb-1 font-weight-bold text-dark">
                                {{ $paper->title }}
                            </h6>

                            <!-- Keywords & Abstract Button -->
                            <div class="compact-keywords d-flex align-items-center flex-wrap mt-1">
                                @foreach($paperKeywords as $keyword)
                                    @if(isset($paper->matched_keywords[$keyword]))
                                        <span class="badge badge-keyword-match px-2 py-0.5 mr-1 mb-1 font-weight-500" title="Matches your research expertise">
                                            <i class="fas fa-check-circle mr-1"></i> {{ $keyword }}
                                        </span>
                                    @else
                                        <span class="badge badge-light border text-secondary px-2 py-0.5 mr-1 mb-1">
                                            {{ $keyword }}
                                        </span>
                                    @endif
                                @endforeach

                                @if(!empty($paper->abstract))
                                    <a href="javascript:void(0)" class="toggle-abstract-btn text-primary ml-2 font-weight-bold small" data-id="{{ $paper->id }}">
                                        <i class="fas fa-chevron-down mr-1"></i><span class="btn-text">Abstract</span>
                                    </a>
                                @endif
                            </div>

                            <!-- Collapsible Abstract preview -->
                            @if(!empty($paper->abstract))
                                <div class="compact-abstract-box d-none mt-2 p-2 rounded bg-light border text-muted small" id="abstract-box-{{ $paper->id }}" style="white-space: pre-line; line-height: 1.5;">
                                    {{ $paper->abstract }}
                                </div>
                            @endif
                        </div>

                        <!-- Right Preferences Column -->
                        <div class="paper-actions-col flex-shrink-0 text-lg-right">
                            <!-- Card inline save indicator -->
                            <div class="mb-1 text-right">
                                <span class="card-status-indicator small font-weight-500 text-muted" id="status-text-{{ $paper->id }}">
                                    @if($current)
                                        <i class="fas fa-check-circle text-success mr-1"></i> Saved ({{ $preferences[$current] ?? ucfirst($current) }})
                                    @else
                                        <span class="text-muted"><i class="far fa-circle mr-1"></i> Not marked</span>
                                    @endif
                                </span>
                            </div>

                            <!-- Horizontal Segmented Radio Group -->
                            <div class="compact-bid-group" role="group" aria-label="Bid Preference">
                                @foreach($preferences as $value => $label)
                                    @php
                                        $isChecked = $current === $value;
                                        $shortLabel = match($value) {
                                            'want' => 'Want',
                                            'can' => 'Can',
                                            'neutral' => 'Neutral',
                                            'conflict' => 'Conflict',
                                            default => $label
                                        };
                                        $icon = match($value) {
                                            'want' => 'fas fa-heart',
                                            'can' => 'fas fa-thumbs-up',
                                            'neutral' => 'fas fa-minus-circle',
                                            'conflict' => 'fas fa-ban',
                                            default => 'far fa-circle'
                                        };
                                    @endphp
                                    <label class="compact-bid-btn bid-btn-{{ $value }} {{ $isChecked ? 'active' : '' }} {{ $biddingOpen ? '' : 'disabled' }}"
                                           for="bid-{{ $paper->id }}-{{ $value }}"
                                           title="{{ $label }}">
                                        <input type="radio"
                                               id="bid-{{ $paper->id }}-{{ $value }}"
                                               name="bid_{{ $paper->id }}"
                                               value="{{ $value }}"
                                               data-paper-id="{{ $paper->id }}"
                                               class="compact-bid-radio d-none"
                                               {{ $isChecked ? 'checked' : '' }}
                                               {{ $biddingOpen ? '' : 'disabled' }}>
                                        <i class="{{ $icon }} mr-1"></i>
                                        <span>{{ $shortLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Sticky Floating Progress Bar at Bottom of Viewport -->
@if($inPool && $total > 0)
<div class="bidding-floating-bar shadow-lg">
    <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between py-2 px-3">
        <div class="d-flex align-items-center mr-3">
            <div class="font-weight-bold mr-3 text-dark small">
                <i class="fas fa-tasks text-primary mr-1"></i>
                Marked: <span id="bar-marked-count" class="text-primary">{{ $marked }}</span> / <span id="bar-total-count">{{ $total }}</span>
                (<span id="bar-percentage">{{ $total > 0 ? round(($marked / $total) * 100) : 0 }}%</span>)
            </div>
            <div class="progress" style="width: 140px; height: 10px; border-radius: 5px;">
                <div class="progress-bar bg-success progress-bar-striped" id="bar-progress-meter"
                     style="width: {{ $total > 0 ? round(($marked / $total) * 100) : 0 }}%;"></div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <span id="floating-sync-status" class="small font-weight-500 mr-3 text-muted">
                <i class="fas fa-check-circle text-success mr-1"></i> All choices auto-saved
            </span>
            @if($totalUnmarked > 0 && $filter !== 'unmarked')
                <a href="{{ route('admin.paper-bids.index', array_merge(request()->query(), ['filter' => 'unmarked'])) }}"
                   class="btn btn-sm btn-outline-primary font-weight-bold">
                    View {{ $totalUnmarked }} Unmarked
                </a>
            @endif
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@parent
<script>
    $(function() {
        // Toggle abstract preview on click
        $(document).on('click', '.toggle-abstract-btn', function() {
            var paperId = $(this).data('id');
            var $box = $('#abstract-box-' + paperId);
            var $btnText = $(this).find('.btn-text');
            var $icon = $(this).find('i');

            if ($box.hasClass('d-none')) {
                $box.removeClass('d-none');
                $btnText.text('Hide Abstract');
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $box.addClass('d-none');
                $btnText.text('Abstract');
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });

        // Instant Auto-Save on Radio Change
        $(document).on('change', '.compact-bid-radio', function() {
            var $radio = $(this);
            var paperId = $radio.data('paper-id');
            var preference = $radio.val();
            var $group = $radio.closest('.compact-bid-group');
            var $statusText = $('#status-text-' + paperId);

            // Immediately reflect active UI state
            $group.find('.compact-bid-btn').removeClass('active');
            $radio.closest('.compact-bid-btn').addClass('active');

            // Indicate saving status on card & floating bar
            $statusText.html('<span class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>');
            $('#floating-sync-status').html('<span class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Saving preference...</span>');

            $.ajax({
                url: "{{ route('admin.paper-bids.store') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    paper_id: paperId,
                    preference: preference
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        var prefLabel = res.label || preference;
                        $statusText.html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Saved (' + prefLabel + ')</span>');

                        // Update sticky progress bar
                        $('#bar-marked-count').text(res.marked);
                        $('#bar-total-count').text(res.total);
                        $('#bar-percentage').text(res.percentage + '%');
                        $('#bar-progress-meter').css('width', res.percentage + '%');

                        $('#floating-sync-status').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> All choices auto-saved</span>');
                    }
                },
                error: function(xhr) {
                    var errMsg = 'Failed to save';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    $statusText.html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> ' + errMsg + '</span>');
                    $('#floating-sync-status').html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Save error</span>');
                }
            });
        });
    });
</script>
@endsection