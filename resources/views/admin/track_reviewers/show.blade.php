@extends('layouts.admin')

@section('content')

@php
    $statusLabels = [
        'invited' => ['Invited', 'warning'],
        'accepted' => ['Accepted', 'info'],
        'in_progress' => ['In progress', 'primary'],
        'completed' => ['Evaluation submitted', 'success'],
        'declined' => ['Declined', 'secondary'],
    ];
    $profile = $reviewer->profile;
    $affiliation = collect([$profile->designation ?? null, $profile->department ?? null, $profile->institution ?? null])
        ->filter()
        ->implode(', ');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <strong>{{ $reviewer->name }}</strong>
            <br><small class="text-muted">{{ $reviewer->email }}</small>
        </div>
        <a href="{{ route('admin.track-reviewers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back to Reviewers by Track
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <dl class="row mb-0">
                    @if($affiliation)
                        <dt class="col-sm-4 small text-muted">Affiliation</dt>
                        <dd class="col-sm-8">{{ $affiliation }}</dd>
                    @endif
                    @if($profile && $profile->country)
                        <dt class="col-sm-4 small text-muted">Country</dt>
                        <dd class="col-sm-8">{{ $profile->country->name }}</dd>
                    @endif
                    @if($profile && $profile->orcid_id)
                        <dt class="col-sm-4 small text-muted">ORCID iD</dt>
                        <dd class="col-sm-8">{{ $profile->orcid_id }}</dd>
                    @endif
                    <dt class="col-sm-4 small text-muted">Reviewer since</dt>
                    <dd class="col-sm-8">{{ optional($reviewer->created_at)->format('j M Y') ?: '—' }}</dd>
                </dl>
            </div>
            <div class="col-md-5">
                <div class="border rounded p-3 text-center h-100">
                    <div class="h3 mb-0 {{ $load['open'] >= $defaultCapacity ? 'text-danger' : 'text-dark' }}">
                        {{ $load['open'] }} <small class="text-muted">/ {{ $defaultCapacity }}</small>
                    </div>
                    <small class="text-muted d-block mb-2">papers open right now</small>
                    <span class="badge badge-success mr-1">{{ $load['done'] }} submitted</span>
                    <span class="badge badge-secondary mr-1">{{ $load['declined'] }} declined</span>
                    <span class="badge badge-light border">{{ $load['total'] }} in all</span>
                    @if($load['open'] >= $defaultCapacity)
                        <div class="small text-danger mt-2">
                            At or over the conference-wide limit. A track with its own limit may still differ.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Expertise</div>
    <div class="card-body">
        @forelse($expertise as $topic)
            <span class="badge badge-light border mr-1 mb-1">{{ $topic }}</span>
        @empty
            <p class="text-muted mb-0">Nothing recorded yet. A chair can fill this in when adding them to a track.</p>
        @endforelse
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Tracks they review for</span>
        <span class="badge badge-light border">{{ $scopes->count() }}</span>
    </div>
    <div class="card-body">
        @forelse($scopes as $row)
            <div class="mb-2">
                @if($row->subTrack)
                    <strong>{{ $row->subTrack->name }}</strong>
                    <small class="text-muted d-block">{{ $row->track->name ?? '' }}</small>
                @else
                    <strong>{{ $row->track->name ?? 'Unknown track' }}</strong>
                    <small class="text-muted d-block">Whole track — covers every sub-track below it</small>
                @endif
            </div>
        @empty
            <p class="text-muted mb-0">Not in any track's reviewer pool yet.</p>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Papers assigned to them</span>
        <span class="badge {{ $rows->isEmpty() ? 'badge-warning' : 'badge-info' }}">{{ $rows->count() }}</span>
    </div>
    <div class="card-body">
        @if($rows->isEmpty())
            <p class="text-muted mb-0">No paper has been handed to them yet.</p>
        @else
            @php $hidden = $rows->where('visible', false)->count(); @endphp
            @if($hidden)
                <p class="text-muted small">
                    {{ $hidden }} of these {{ $hidden === 1 ? 'sits' : 'sit' }} in a track you do not chair. They are
                    counted here so the workload above is honest, but the title and authors stay with that track's chair.
                    You are seeing this reviewer because they also serve a track of yours.
                </p>
            @endif
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width: 8rem;">Submission</th>
                            <th>Title</th>
                            <th>Track</th>
                            <th style="width: 10rem;">Status</th>
                            <th style="width: 7rem;">Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php
                                $paper = $row['paper'];
                                $assignment = $row['assignment'];
                                [$label, $style] = $statusLabels[$assignment->status] ?? [$assignment->status, 'light'];
                            @endphp
                            <tr class="{{ $row['visible'] ? '' : 'bg-light text-muted' }}">
                                <td>
                                    @if($row['visible'])
                                        <a href="{{ route('admin.review-assignments.show', $paper->id) }}">{{ $paper->submission_id }}</a>
                                    @else
                                        <small class="text-muted">{{ $paper->submission_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($row['visible'])
                                        {{ $paper->title }}
                                    @else
                                        <small class="font-italic">Withheld — another chair's track</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $paper->track->name ?? '—' }}</small>
                                    @if($paper->subTrack)
                                        <small class="text-muted d-block">{{ $paper->subTrack->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $style }}">{{ $label }}</span>
                                    @if($assignment->status === 'declined' && $assignment->decline_reason && $row['visible'])
                                        <small class="text-muted d-block">{{ $assignment->decline_reason }}</small>
                                    @endif
                                </td>
                                <td><small>{{ optional($assignment->assigned_at)->format('j M Y') ?: '—' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
