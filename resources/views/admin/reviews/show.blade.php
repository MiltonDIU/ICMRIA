@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@php
    $statusLabels = [
        'invited' => ['Awaiting your answer', 'warning'],
        'accepted' => ['Accepted', 'info'],
        'in_progress' => ['Draft saved', 'primary'],
        'completed' => ['Submitted', 'success'],
        'declined' => ['Declined', 'secondary'],
    ];
    [$statusLabel, $statusStyle] = $statusLabels[$assignment->status] ?? [$assignment->status, 'light'];
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $paper->submission_id }} <span class="badge badge-{{ $statusStyle }} ml-1">{{ $statusLabel }}</span></span>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">Back to my reviews</a>
    </div>
    <div class="card-body">
        <h5 class="mb-2">{{ $paper->title }}</h5>
        <p class="text-muted mb-2">
            {{ $paper->track->name ?? '' }}
            @if($paper->subTrack) &mdash; {{ $paper->subTrack->name }} @endif
        </p>
        <div class="mb-3">
            @foreach(\App\Services\SubmissionRules::splitKeywords($paper->keywords) as $keyword)
                <span class="badge badge-light border mr-1">{{ $keyword }}</span>
            @endforeach
        </div>

        @if($doubleBlind)
            <p class="small text-muted mb-3">Author names are withheld: this conference uses double-blind review.</p>
        @elseif($paper->authors->isNotEmpty())
            <p class="mb-3">
                <span class="small text-muted text-uppercase font-weight-bold">Authors</span><br>
                @foreach($paper->authors as $author)
                    {{ $author->name }}@if($author->institution) <small class="text-muted">({{ $author->institution }})</small>@endif{{ $loop->last ? '' : ',' }}
                @endforeach
            </p>
        @endif

        <details class="mb-3">
            <summary class="text-primary" style="cursor: pointer;">Abstract</summary>
            <p class="mt-2 mb-0" style="white-space: pre-line;">{{ $paper->abstract }}</p>
        </details>

        @if($assignment->status !== 'declined')
            @if($paper->manuscript_path)
                <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download the manuscript
                </a>
            @else
                <div class="alert alert-warning mb-0">
                    The author has not uploaded the manuscript yet. You can start a draft, but it cannot be submitted until the file is here.
                </div>
            @endif
        @endif

        @if($assignment->status === 'invited')
            <hr>
            <p class="mb-2">Can you review this paper impartially and in time?</p>
            <div class="d-flex flex-wrap align-items-start">
                <form action="{{ route('admin.reviews.accept', $assignment->id) }}" method="POST" class="mr-2 mb-2">
                    @csrf
                    <button class="btn btn-success"><i class="fas fa-check"></i> Accept this review</button>
                </form>
                <details class="mb-2" style="flex: 1 1 20rem;">
                    <summary class="btn btn-outline-danger">Decline</summary>
                    <form action="{{ route('admin.reviews.decline', $assignment->id) }}" method="POST" class="mt-2">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="decline_reason">Why are you declining? *</label>
                            <textarea id="decline_reason" name="decline_reason" class="form-control" rows="2" maxlength="500"
                                      placeholder="For example: a conflict of interest, outside my expertise, or no time before the deadline" required>{{ old('decline_reason') }}</textarea>
                        </div>
                        <button class="btn btn-sm btn-danger">Confirm and decline</button>
                    </form>
                </details>
            </div>
        @endif

        @if($assignment->status === 'declined')
            <div class="alert alert-secondary mt-3 mb-0">
                You declined this review{{ $assignment->decline_reason ? ': ' . $assignment->decline_reason : '.' }}
            </div>
        @endif
    </div>
</div>

@if($assignment->status !== 'declined')
<div class="card mb-4">
    <div class="card-header">Evaluation</div>
    <div class="card-body">
        @if($evaluation->isSubmitted())
            <div class="alert alert-success">
                Submitted on {{ $evaluation->submitted_at->format('j M Y, g:i A') }}. It can no longer be changed.
            </div>
        @elseif($assignment->status === 'invited')
            <p class="text-muted">Accept the invitation above to start, or begin a draft now &mdash; saving it accepts the review.</p>
        @endif

        <form action="{{ route('admin.reviews.save', $assignment->id) }}" method="POST">
            @csrf
            <fieldset {{ $editable ? '' : 'disabled' }}>
                @foreach($criteria as $field => $label)
                    <div class="form-group">
                        <label class="d-block font-weight-bold mb-1">{{ $label }} *</label>
                        @for($i = 1; $i <= 5; $i++)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input" id="{{ $field }}-{{ $i }}"
                                       name="{{ $field }}" value="{{ $i }}"
                                       {{ (string) old($field, $evaluation->$field) === (string) $i ? 'checked' : '' }}>
                                <label class="custom-control-label" for="{{ $field }}-{{ $i }}">{{ $i }} &middot; {{ \App\Models\PaperEvaluation::SCALE[$i] }}</label>
                            </div>
                        @endfor
                        <small class="form-text text-muted">1 Poor &middot; 2 Fair &middot; 3 Good &middot; 4 Very Good &middot; 5 Excellent</small>
                    </div>
                @endforeach

                <div class="form-group">
                    <label class="d-block font-weight-bold mb-1">Overall recommendation *</label>
                    @foreach($recommendations as $value => $label)
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="recommendation-{{ $value }}"
                                   name="recommendation" value="{{ $value }}"
                                   {{ old('recommendation', $evaluation->recommendation) === $value ? 'checked' : '' }}>
                            <label class="custom-control-label" for="recommendation-{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <label for="feedback_for_authors" class="font-weight-bold">Feedback for authors *</label>
                    <textarea id="feedback_for_authors" name="feedback_for_authors" class="form-control" rows="8"
                              maxlength="10000">{{ old('feedback_for_authors', $evaluation->feedback_for_authors) }}</textarea>
                    <small class="form-text text-muted">
                        Sent to the authors with the decision, without your name. Explain what works and what should be improved.
                    </small>
                </div>

                <div class="form-group">
                    <label for="confidential_comments" class="font-weight-bold">Confidential comments for the chairs</label>
                    <textarea id="confidential_comments" name="confidential_comments" class="form-control" rows="4"
                              maxlength="5000">{{ old('confidential_comments', $evaluation->confidential_comments) }}</textarea>
                    <small class="form-text text-muted">
                        Optional. Seen only by the track chairs and the TPC Chair, and never sent to the authors.
                    </small>
                </div>

                @if($editable)
                    <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                        <i class="fas fa-save"></i> Save draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary"
                            onclick="return confirm('Submit this evaluation? It cannot be changed afterwards.');">
                        <i class="fas fa-paper-plane"></i> Submit evaluation
                    </button>
                @endif
            </fieldset>
        </form>
    </div>
</div>
@endif

@if($inDiscussion)
<div class="card mb-4">
    <div class="card-header">Discussion of this paper</div>
    <div class="card-body">
        <p class="text-muted">
            The evaluations of this paper differ, so the chair has opened a discussion. You can read the other
            evaluations here and add your view. Reviewers appear to one another by number only, and nothing here is
            sent to the authors.
        </p>
        <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem;">The other evaluations</h6>
        @include('admin.decisions.partials.evaluations', ['review' => $review, 'showNames' => false, 'showConfidential' => false, 'exceptUserId' => auth()->id()])
        <hr>
        @include('admin.decisions.partials.discussion', ['paper' => $paper, 'review' => $review, 'viewerIsCommittee' => false, 'canPost' => !$discussionClosed])
    </div>
</div>
@elseif($paper->discussion_opened_at && $assignment->status !== 'declined')
    <div class="alert alert-info">
        The chair has opened a discussion of this paper. You can join it once you have submitted your own evaluation.
    </div>
@endif

@endsection
