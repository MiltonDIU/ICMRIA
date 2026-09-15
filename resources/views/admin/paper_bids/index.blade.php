@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Paper Bidding</span>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.paper-bids.index') }}"
               class="btn btn-{{ $filter === '' ? 'primary' : 'outline-secondary' }}">All</a>
            <a href="{{ route('admin.paper-bids.index', ['filter' => 'unmarked']) }}"
               class="btn btn-{{ $filter === 'unmarked' ? 'primary' : 'outline-secondary' }}">Not marked yet</a>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">
            These are the papers in the tracks you review for. <strong>Want to Review</strong> and
            <strong>Can Review</strong> put you ahead when reviewers are assigned, <strong>Neutral</strong>
            leaves it to subject match, and <strong>Conflict</strong> means you will not be given the paper.
        </p>
        <p class="text-muted mb-0">
            You have marked <strong>{{ $marked }}</strong> of {{ $total }}.
            A bid is a preference, not a promise: the track chair makes the assignment.
        </p>
    </div>
</div>

@if(!$inPool)
    <div class="alert alert-warning">
        You are not in any track's reviewer pool yet, so there are no papers to bid on.
        A track chair adds reviewers under Reviewers by Track.
    </div>
@elseif(!$biddingOpen)
    <div class="alert alert-info">
        Bidding is closed. Your earlier choices are shown below and can no longer be changed.
    </div>
@endif

@if($papers->isEmpty())
    @if($inPool)
        <div class="card">
            <div class="card-body text-center text-muted">
                {{ $filter === 'unmarked' && $total > 0 ? 'You have marked every paper.' : 'No papers in your tracks yet.' }}
            </div>
        </div>
    @endif
@else
    <form action="{{ route('admin.paper-bids.store') }}" method="POST">
        @csrf
        @foreach($papers as $paper)
            @php $current = old('bids.' . $paper->id, $bids[$paper->id] ?? null); @endphp
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <div class="mr-4 mb-2" style="flex: 1 1 22rem; min-width: 0;">
                            <small class="text-muted">{{ $paper->submission_id }}</small>
                            @if($assignedIds->contains($paper->id))
                                <span class="badge badge-primary ml-1">Assigned to you</span>
                            @endif
                            <h6 class="mt-1 mb-1">{{ $paper->title }}</h6>
                            <small class="text-muted d-block mb-2">
                                {{ $paper->track->name ?? '' }}@if($paper->subTrack) &mdash; {{ $paper->subTrack->name }}@endif
                            </small>
                            @foreach(\App\Services\SubmissionRules::splitKeywords($paper->keywords) as $keyword)
                                <span class="badge badge-light border mr-1">{{ $keyword }}</span>
                            @endforeach
                            <details class="mt-2">
                                <summary class="text-primary" style="cursor: pointer;">Abstract</summary>
                                <p class="mt-2 mb-0" style="white-space: pre-line;">{{ $paper->abstract }}</p>
                            </details>
                        </div>
                        <div class="mb-2" style="flex: 0 0 11rem;">
                            @foreach($preferences as $value => $label)
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input"
                                           id="bid-{{ $paper->id }}-{{ $value }}"
                                           name="bids[{{ $paper->id }}]" value="{{ $value }}"
                                           {{ $current === $value ? 'checked' : '' }}
                                           {{ $biddingOpen ? '' : 'disabled' }}>
                                    <label class="custom-control-label" for="bid-{{ $paper->id }}-{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if($biddingOpen)
            <button type="submit" class="btn btn-primary mb-4">
                <i class="fas fa-save"></i> Save my bids
            </button>
        @endif
    </form>
@endif

@endsection
