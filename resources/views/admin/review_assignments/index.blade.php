@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

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
    <div class="card-body">
        <p class="text-muted mb-0">
            The conference asks for at least {{ $minimum }} independent reviewers per paper.
            A paper below that is marked in red.
        </p>
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
                        <th style="width: 18rem;">Reviewers</th>
                        <th class="text-right" style="width: 6rem;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($papers as $paper)
                        @php $count = $paper->reviewerAssignments->count(); @endphp
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
                            <td>
                                @if($count === 0)
                                    <span class="badge badge-danger">none assigned</span>
                                @else
                                    <span class="badge badge-{{ $count < $minimum ? 'warning' : 'success' }} mr-1">
                                        {{ $count }} of {{ $minimum }} minimum
                                    </span>
                                    @foreach($paper->reviewerAssignments as $assignment)
                                        <small class="d-block text-muted">{{ $assignment->reviewer->name ?? '—' }}</small>
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
                        <tr><td colspan="6" class="text-center text-muted">No papers in your tracks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
