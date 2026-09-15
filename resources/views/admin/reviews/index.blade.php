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
@endphp

<div class="card mb-3">
    <div class="card-header">My Reviews</div>
    <div class="card-body">
        <p class="text-muted mb-2">
            The papers the track chairs have asked you to review. Accept or decline each invitation, read the
            manuscript, then fill in the evaluation form. A draft stays private until you submit it.
            @if($doubleBlind)
                Review is double-blind: author names are withheld from you, and yours from the authors.
            @endif
        </p>
        <div>
            <span class="badge badge-warning mr-1">{{ $counts['invited'] ?? 0 }} awaiting your answer</span>
            <span class="badge badge-primary mr-1">{{ ($counts['accepted'] ?? 0) + ($counts['in_progress'] ?? 0) }} to evaluate</span>
            <span class="badge badge-success mr-1">{{ $counts['completed'] ?? 0 }} submitted</span>
            @if($counts['declined'] ?? 0)
                <span class="badge badge-secondary">{{ $counts['declined'] }} declined</span>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 8rem;">Paper</th>
                        <th>Title</th>
                        <th class="text-center" style="width: 8rem;">Manuscript</th>
                        <th class="text-center" style="width: 10rem;">Status</th>
                        <th class="text-right" style="width: 13rem;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php
                            $paper = $assignment->paper;
                            [$label, $style] = $statusLabels[$assignment->status] ?? [$assignment->status, 'light'];
                        @endphp
                        <tr class="{{ $assignment->status === 'declined' ? 'text-muted' : '' }}">
                            <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                            <td>
                                {{ Str::limit($paper->title, 80) }}
                                <br><small class="text-muted">{{ Str::limit($paper->subTrack->name ?? ($paper->track->name ?? ''), 60) }}</small>
                                @if($assignment->status === 'declined' && $assignment->decline_reason)
                                    <br><small>You said: {{ $assignment->decline_reason }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($assignment->status === 'declined')
                                    <span class="text-muted">&mdash;</span>
                                @elseif($paper->manuscript_path)
                                    <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                @else
                                    <span class="badge badge-light border text-muted">not uploaded yet</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge badge-{{ $style }}">{{ $label }}</span></td>
                            <td class="text-right">
                                @if($assignment->status === 'invited')
                                    <form action="{{ route('admin.reviews.accept', $assignment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Accept</button>
                                    </form>
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-outline-secondary">Open / decline</a>
                                @elseif(in_array($assignment->status, ['accepted', 'in_progress'], true))
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-primary">
                                        {{ $assignment->status === 'in_progress' ? 'Continue evaluation' : 'Write evaluation' }}
                                    </a>
                                @elseif($assignment->status === 'completed')
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-outline-success">View evaluation</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No papers have been assigned to you yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
