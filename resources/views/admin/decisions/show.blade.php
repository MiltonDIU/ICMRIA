@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@php
    $averages = $review->averages();
    $decisionStyles = ['accept' => 'success', 'minor_revisions' => 'info', 'reject' => 'danger'];
    $statusStyles = ['pending_approval' => 'warning', 'approved' => 'success', 'returned' => 'danger'];
    $discussionClosed = $decision && $decision->isApproved();
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $paper->submission_id }}</span>
        <a href="{{ route('admin.decisions.index') }}" class="btn btn-sm btn-outline-secondary">Back to decisions</a>
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
        @if($paper->manuscript_path)
            <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download"></i> Read the manuscript
            </a>
        @endif
        @can('review_assign')
            <a href="{{ route('admin.review-assignments.show', $paper->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-users"></i> Manage reviewers
            </a>
        @endcan
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Consolidated evaluations</span>
        <span class="badge badge-{{ $review->isReady() ? 'success' : 'warning' }}">
            {{ $review->submittedCount() }} of {{ $review->activeCount() }} submitted &middot; {{ $review->minimum() }} needed
        </span>
    </div>
    <div class="card-body">
        @if($review->submittedCount() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            @foreach(\App\Models\PaperEvaluation::CRITERIA as $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                            <th class="text-center">Overall</th>
                            <th>Recommendations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach(array_keys(\App\Models\PaperEvaluation::CRITERIA) as $field)
                                <td class="text-center">{{ $averages[$field] }}</td>
                            @endforeach
                            <td class="text-center font-weight-bold">{{ $averages['overall'] }}</td>
                            <td>
                                @foreach($review->recommendationTally() as $key => $number)
                                    <span class="badge badge-light border mr-1">{{ $number }} &times; {{ \App\Models\PaperEvaluation::RECOMMENDATIONS[$key] }}</span>
                                @endforeach
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if($review->hasConflict())
            <div class="alert alert-danger">
                <strong>The reviewers disagree.</strong>
                <ul class="mb-2 mt-1">
                    @foreach($review->conflicts() as $reason)<li>{{ $reason }}</li>@endforeach
                </ul>
                Before deciding, bring in a third reviewer or open an internal discussion.
                <div class="mt-2 d-flex flex-wrap">
                    @can('review_assign')
                        <a href="{{ route('admin.review-assignments.show', [$paper->id, 'source' => 'discussion']) }}"
                           class="btn btn-sm btn-light border mr-2 mb-1">
                            <i class="fas fa-user-plus"></i> Assign a third reviewer
                        </a>
                    @endcan
                    @if(!$paper->discussion_opened_at && $canDecide)
                        <form action="{{ route('admin.decisions.discussion.open', $paper->id) }}" method="POST" class="mb-1">
                            @csrf
                            <button class="btn btn-sm btn-light border"><i class="fas fa-comments"></i> Open internal discussion</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        @unless($review->isReady())
            <div class="alert alert-info">
                Waiting for {{ $review->minimum() - $review->submittedCount() }} more evaluation{{ $review->minimum() - $review->submittedCount() === 1 ? '' : 's' }}
                before a decision can be entered.
            </div>
        @endunless

        @include('admin.decisions.partials.evaluations', ['review' => $review, 'showNames' => true, 'showConfidential' => true])
    </div>
</div>

@if($paper->discussion_opened_at)
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <span>Internal discussion</span>
            <small class="text-muted">
                Opened {{ $paper->discussion_opened_at->format('j M Y') }} by {{ $paper->discussionOpenedBy->name ?? '—' }}
            </small>
        </div>
        <div class="card-body">
            @include('admin.decisions.partials.discussion', [
                'paper' => $paper, 'review' => $review, 'viewerIsCommittee' => true, 'canPost' => !$discussionClosed,
            ])
        </div>
    </div>
@elseif(!$review->hasConflict() && $canDecide && $review->submittedCount() > 0)
    <form action="{{ route('admin.decisions.discussion.open', $paper->id) }}" method="POST" class="mb-4">
        @csrf
        <button class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-comments"></i> Open an internal discussion with the reviewers
        </button>
    </form>
@endif

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Decision</span>
        @if($decision)
            <span class="badge badge-{{ $statusStyles[$decision->status] ?? 'light' }}">
                {{ \App\Models\PaperDecision::STATUSES[$decision->status] ?? $decision->status }}
            </span>
        @endif
    </div>
    <div class="card-body">
        @if($decision)
            @if($decision->status === 'returned')
                @php $lastReturn = $paper->decisionComments->where('kind', 'returned')->last(); @endphp
                <div class="alert alert-danger">
                    <strong>Returned by the TPC Chair{{ $lastReturn ? ' in round ' . $lastReturn->round : '' }}:</strong> {{ $lastReturn->body ?? '' }}
                    <br><small>Revise the decision below and send it again. Every earlier comment stays in the history.</small>
                </div>
            @endif
            <p class="mb-1">
                <span class="badge badge-{{ $decisionStyles[$decision->decision] ?? 'light' }} px-2 py-1">{{ $decision->label() }}</span>
                <small class="text-muted ml-1">
                    entered by {{ $decision->decidedBy->name ?? '—' }} on {{ optional($decision->decided_at)->format('j M Y') }} &middot; round {{ $decision->round }}
                </small>
            </p>
            @if($decision->isApproved())
                <p class="small text-muted mb-1">
                    Approved by {{ $decision->approvedBy->name ?? '—' }} on {{ optional($decision->approved_at)->format('j M Y') }}.
                    {{ $decision->notified_at ? 'The authors were notified on ' . $decision->notified_at->format('j M Y') . '.' : 'The authors have not been notified yet.' }}
                </p>
            @endif
            @if($decision->note_to_authors)
                <div class="small text-muted text-uppercase font-weight-bold mt-2">Note to the authors</div>
                <p class="mb-1" style="white-space: pre-line;">{{ $decision->note_to_authors }}</p>
            @endif
        @else
            <p class="text-muted mb-0">No decision has been entered yet.</p>
        @endif

        @if($canDecide)
            <hr>
            <form action="{{ route('admin.decisions.store', $paper->id) }}" method="POST">
                @csrf
                <fieldset {{ $review->isReady() ? '' : 'disabled' }}>
                    <div class="form-group">
                        <label class="d-block font-weight-bold mb-1">{{ $decision ? 'Revise the decision' : 'Your decision' }} *</label>
                        @foreach($decisions as $value => $label)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input" id="decision-{{ $value }}" name="decision" value="{{ $value }}"
                                       {{ old('decision', $decision->decision ?? null) === $value ? 'checked' : '' }}>
                                <label class="custom-control-label" for="decision-{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label for="note_to_authors" class="font-weight-bold">Note to the authors</label>
                        <textarea id="note_to_authors" name="note_to_authors" class="form-control" rows="4" maxlength="5000">{{ old('note_to_authors', $decision->note_to_authors ?? '') }}</textarea>
                        <small class="form-text text-muted">Optional. Sent to the authors together with the reviewers' feedback.</small>
                    </div>
                    <div class="form-group">
                        <label for="decision_comment" class="font-weight-bold">Your comment as chair</label>
                        <textarea id="decision_comment" name="comment" class="form-control" rows="3" maxlength="5000">{{ old('comment') }}</textarea>
                        <small class="form-text text-muted">Optional. Added to the comment history under this round, where the TPC Chair reads it. Earlier comments are kept.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> {{ $decision ? 'Send the revised decision for approval' : 'Send for TPC approval' }}
                    </button>
                </fieldset>
            </form>
        @elseif($cannotDecideReason)
            <p class="small text-muted mt-2 mb-0">{{ $cannotDecideReason }}</p>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Comments between the chairs and the TPC Chair</span>
        <span class="badge badge-light border">{{ $paper->decisionComments->count() }}</span>
    </div>
    <div class="card-body">
        @include('admin.decisions.partials.comments', ['comments' => $paper->decisionComments])

        @if($commentForm === 'chair')
            <form action="{{ route('admin.decisions.comments.store', $paper->id) }}" method="POST" class="mt-3">
                @csrf
                <label for="chair_comment" class="font-weight-bold">Add a comment as chair</label>
                <textarea id="chair_comment" name="comment" class="form-control mb-2" rows="3" maxlength="5000" required></textarea>
                <button class="btn btn-sm btn-primary"><i class="fas fa-comment"></i> Add comment</button>
                <small class="form-text text-muted">Seen by the chairs of this paper and the TPC Chair. Never sent to authors or reviewers.</small>
            </form>
        @elseif($commentForm === 'tpc')
            <form action="{{ route('admin.final-approval.comments.store', $decision->id) }}" method="POST" class="mt-3">
                @csrf
                <label for="tpc_comment" class="font-weight-bold">Add a comment as TPC Chair</label>
                <textarea id="tpc_comment" name="comment" class="form-control mb-2" rows="3" maxlength="5000" required></textarea>
                <button class="btn btn-sm btn-dark"><i class="fas fa-comment"></i> Add comment</button>
                <small class="form-text text-muted">Seen by the chairs of this paper and the TPC Chair. Never sent to authors or reviewers.</small>
            </form>
        @elseif($decision && $decision->isApproved())
            <p class="small text-muted mt-2 mb-0">The decision has been approved, so no further comments can be added.</p>
        @endif
    </div>
</div>

@endsection
