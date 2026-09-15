@extends('layouts.admin')
@section('content')
@php $bidsByReviewer = $paper->bids->pluck('preference', 'reviewer_id'); @endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if(session('refused'))
    <div class="alert alert-warning">
        <strong>Not assigned:</strong>
        <ul class="mb-0 mt-1">
            @foreach(session('refused') as $line)<li>{{ $line }}</li>@endforeach
        </ul>
    </div>
@endif

@if(request('source') === 'discussion')
    <div class="alert alert-info">
        You are adding a reviewer because the evaluations of this paper disagree. Anyone you assign from here is
        recorded as brought in for that reason.
    </div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $paper->submission_id }}</span>
        <a href="{{ route('admin.review-assignments.index') }}" class="btn btn-sm btn-outline-secondary">Back to list</a>
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
        @else
            <div class="alert alert-warning mb-0">
                No manuscript has been uploaded yet. Reviewers can be lined up now, but there is nothing for them to read.
            </div>
        @endif

        @if($paper->conflicts->isNotEmpty())
            <hr>
            <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem;">
                Conflicts the author declared
            </h6>
            @foreach($paper->conflicts as $conflict)
                <div><span class="badge badge-danger">{{ $conflict->label }}</span>
                    @if($conflict->note)<small class="text-muted ml-1">{{ $conflict->note }}</small>@endif
                </div>
            @endforeach
            <small class="form-text text-muted">
                Named people are removed from the candidate list automatically.
                An institution cannot be matched by the system, so weigh it yourself.
            </small>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Assigned reviewers</span>
        @php $count = $paper->reviewerAssignments->where('status', '!=', 'declined')->count(); @endphp
        <span class="badge badge-{{ $count === 0 ? 'danger' : ($count < $minimum ? 'warning' : 'success') }}">
            {{ $count }} assigned · {{ $minimum }} is the minimum · {{ $wanted }} wanted
        </span>
    </div>
    <div class="card-body">
        @if($paper->reviewerAssignments->isEmpty())
            <p class="text-muted mb-0">Nobody yet.</p>
        @else
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Reviewer</th><th>How</th><th>Bid</th><th>Match</th><th>Status</th><th>Assigned</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($paper->reviewerAssignments as $assignment)
                        <tr>
                            <td>
                                {{ $assignment->reviewer->name ?? '—' }}
                                <br><small class="text-muted">{{ $assignment->reviewer->email ?? '' }}</small>
                                @if($bidsByReviewer->get($assignment->reviewer_id) === 'conflict')
                                    <br><span class="badge badge-danger">has since marked a conflict with this paper</span>
                                @endif
                            </td>
                            <td><span class="badge badge-light border">{{ $assignment->assignment_source }}</span></td>
                            <td>@include('admin.review_assignments.partials.bid-badge', ['preference' => $bidsByReviewer->get($assignment->reviewer_id)])</td>
                            <td>{{ $assignment->match_score !== null ? $assignment->match_score . ' keywords' : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $assignment->status === 'declined' ? 'secondary' : ($assignment->status === 'completed' ? 'success' : 'info') }}">{{ $assignment->status }}</span>
                                @if($assignment->status === 'declined' && $assignment->decline_reason)
                                    <br><small class="text-muted">{{ $assignment->decline_reason }}</small>
                                @endif
                            </td>
                            <td><small>{{ optional($assignment->assigned_at)->format('j M Y') }}</small></td>
                            <td class="text-right">
                                @unless($assignment->evaluation && $assignment->evaluation->isSubmitted())
                                <form action="{{ route('admin.review-assignments.destroy', [$paper->id, $assignment->id]) }}"
                                      method="POST" onsubmit="return confirm('Take this paper back from the reviewer?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i></button>
                                </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@php $review = \App\Services\ReviewConsolidation::for($paper); @endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Evaluations received</span>
        <span class="badge badge-light border">{{ $review->submittedCount() }} of {{ $count }} reviewers</span>
    </div>
    <div class="card-body">
        @include('admin.decisions.partials.evaluations', ['review' => $review, 'showNames' => true, 'showConfidential' => true])
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Candidates</span>
        @php $eligible = $candidates->where('eligible', true)->count(); @endphp
        <form action="{{ route('admin.review-assignments.auto', $paper->id) }}" method="POST" class="mb-0">
            @csrf
            <button class="btn btn-sm btn-success" {{ $eligible === 0 ? 'disabled' : '' }}>
                <i class="fas fa-magic"></i> Assign the best {{ max($wanted - $paper->reviewerAssignments->where('status', '!=', 'declined')->count(), 0) }} automatically
            </button>
        </form>
    </div>

    <div class="card-body">
        @if($candidates->isEmpty())
            <div class="alert alert-warning mb-0">
                This track has no reviewer pool yet.
                Add reviewers under <a href="{{ route('admin.track-reviewers.index') }}">Reviewers by Track</a>.
            </div>
        @else
            @if($eligible === 0)
                <div class="alert alert-warning">
                    Nobody in the pool can take this paper. Every reason is shown beside a name below &mdash; if they
                    are all conflicts, it is worth asking the author about it before adding more reviewers.
                </div>
            @endif
            <form action="{{ route('admin.review-assignments.store', $paper->id) }}" method="POST">
                @csrf
                <input type="hidden" name="source" value="{{ request('source') === 'discussion' ? 'discussion' : 'manual' }}">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th style="width: 2rem;"></th>
                                <th>Reviewer</th>
                                <th>Expertise</th>
                                <th class="text-center" style="width: 8rem;">Bid</th>
                                <th class="text-center" style="width: 8rem;">Keyword match</th>
                                <th class="text-center" style="width: 7rem;">Workload</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidates as $candidate)
                                <tr class="{{ $candidate['eligible'] ? '' : 'bg-light text-muted' }}">
                                    <td>
                                        <input type="checkbox" name="reviewer_ids[]" value="{{ $candidate['reviewer']->id }}"
                                               {{ $candidate['eligible'] ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        {{ $candidate['reviewer']->name }}
                                        @unless($candidate['eligible'])
                                            <span class="badge badge-danger ml-1">{{ $candidate['reason'] }}</span>
                                        @endunless
                                        <br><small class="text-muted">{{ $candidate['reviewer']->email }}</small>
                                    </td>
                                    <td>
                                        @foreach($candidate['expertise'] as $topic)
                                            <span class="badge badge-light border mr-1">{{ $topic }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @include('admin.review_assignments.partials.bid-badge', ['preference' => $candidate['bid']])
                                    </td>
                                    <td class="text-center">
                                        @if($candidate['score'] > 0)
                                            <span class="badge badge-{{ $candidate['eligible'] ? 'success' : 'secondary' }}">{{ $candidate['score'] }}</span>
                                        @else
                                            <span class="badge badge-light border text-muted">none</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <small class="{{ $candidate['load'] >= $candidate['capacity'] ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            {{ $candidate['load'] }} / {{ $candidate['capacity'] }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Assign the ticked reviewers
                </button>
                @php $blocked = $candidates->where('eligible', false)->count(); @endphp
                <small class="form-text text-muted">
                    Everyone in this track's pool is listed. Reviewers who bid Want to Review or Can Review come
                    first, then the closest keyword match.
                    @if($blocked)
                        <strong>{{ $blocked }}</strong> cannot take this paper and the reason is shown beside the name;
                        they are greyed out rather than hidden, so a pool that looks thin can be told apart from an
                        author who has declared conflicts widely.
                    @endif
                </small>
            </form>
        @endif
    </div>
</div>

@endsection
