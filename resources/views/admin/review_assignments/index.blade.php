@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if(session('short'))
    <div class="alert alert-warning">
        <strong>Still short of reviewers:</strong> {{ implode(', ', session('short')) }}.
        Open each one to see why nobody else in the pool could take it.
    </div>
@endif

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Assign Reviewers</span>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.review-assignments.index') }}"
               class="btn btn-{{ $state === '' ? 'primary' : 'outline-secondary' }}">All</a>
            <a href="{{ route('admin.review-assignments.index', ['state' => 'unassigned']) }}"
               class="btn btn-{{ $state === 'unassigned' ? 'primary' : 'outline-secondary' }}">Needs reviewers</a>
            <a href="{{ route('admin.review-assignments.index', ['state' => 'assigned']) }}"
               class="btn btn-{{ $state === 'assigned' ? 'primary' : 'outline-secondary' }}">Has reviewers</a>
        </div>
    </div>
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <p class="text-muted mb-2 mr-3">
            The conference asks for at least {{ $minimum }} independent reviewers per paper.
            A paper below that is marked in red. Papers whose abstract was rejected are not listed.
        </p>
        @unless($hasNoScope)
            <form action="{{ route('admin.review-assignments.auto-all') }}" method="POST" class="mb-2"
                  onsubmit="return confirm('Assign reviewers automatically to every paper in your tracks that still needs them? Each reviewer assigned is emailed.');">
                @csrf
                <button class="btn btn-sm btn-success">
                    <i class="fas fa-magic"></i> Auto-assign every paper that needs reviewers
                </button>
            </form>
        @endunless
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">
        You are not listed as the chair of any track, so there is nothing to assign here.
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 8rem;">Paper</th>
                        <th>Title</th>
                        <th>Sub-track</th>
                        <th class="text-center" style="width: 7rem;">Manuscript</th>
                        <th class="text-center" style="width: 8rem;">Bids</th>
                        <th style="width: 18rem;">Reviewers</th>
                        <th class="text-right" style="width: 6rem;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($papers as $paper)
                        @php $count = $paper->reviewerAssignments->where('status', '!=', 'declined')->count(); @endphp
                        <tr>
                            <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                            <td>{{ Str::limit($paper->title, 68) }}</td>
                            <td><small class="text-muted">{{ Str::limit($paper->subTrack->name ?? '—', 36) }}</small></td>
                            <td class="text-center">
                                @if($paper->manuscript_path)
                                    <span class="badge badge-success">yes</span>
                                @else
                                    <span class="badge badge-light border text-muted">not yet</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($paper->want_bids_count || $paper->can_bids_count)
                                    @if($paper->want_bids_count)<span class="badge badge-success">{{ $paper->want_bids_count }} want</span>@endif
                                    @if($paper->can_bids_count)<span class="badge badge-info">{{ $paper->can_bids_count }} can</span>@endif
                                @else
                                    <span class="badge badge-light border text-muted">none</span>
                                @endif
                            </td>
                            <td>
                                @if($count === 0)
                                    <span class="badge badge-danger">none assigned</span>
                                @else
                                    <span class="badge badge-{{ $count < $minimum ? 'warning' : 'success' }} mr-1">
                                        {{ $count }} of {{ $minimum }} minimum
                                    </span>
                                    @foreach($paper->reviewerAssignments as $assignment)
                                        <small class="d-block text-muted">
                                            {{ $assignment->reviewer->name ?? '—' }}
                                            @if($assignment->status === 'declined') <span class="text-danger">(declined)</span>@endif
                                            @if($assignment->status === 'completed') <span class="text-success">(evaluated)</span>@endif
                                        </small>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.review-assignments.show', $paper->id) }}" class="btn btn-sm btn-primary">
                                    Assign
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No papers in your tracks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
