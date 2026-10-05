@extends('layouts.admin')
@section('content')


@php
    $filters = [
        'pending' => 'Awaiting check',
        'changes_requested' => 'Sent back',
        'approved' => 'Approved',
    ];
@endphp

<div class="card mb-3">
    <div class="card-header">Revised Manuscripts</div>
    <div class="card-body">
        <p class="text-muted mb-2">
            Papers accepted with minor revisions whose authors have uploaded the revised manuscript. Check that it answers the
            reviewers' comments, then approve it or send it back with a note. The paper can be confirmed for the proceedings
            only once its revision is approved.
        </p>
        <div class="d-flex flex-wrap">
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.revisions.index', ['filter' => $key]) }}"
                   class="btn btn-sm mr-1 mb-1 btn-{{ $filter === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">You are not listed as the chair of any track, so there are no revisions for you to check.</div>
@endif

@forelse($papers as $paper)
    @php $final = $paper->cameraReady; @endphp
    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <span><strong>{{ $paper->submission_id }}</strong> &mdash; {{ Str::limit($paper->title, 90) }}</span>
            <span class="badge badge-{{ ['pending' => 'warning', 'approved' => 'success', 'changes_requested' => 'danger'][$final->revision_status] ?? 'light' }}">
                {{ \App\Models\PaperCameraReady::REVISION_STATUSES[$final->revision_status] ?? '—' }}
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">{{ $paper->subTrack->name ?? ($paper->track->name ?? '') }}</p>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <strong>Revised:</strong> {{ $final->revised_name }}
                    <br><small class="text-muted">Uploaded {{ optional($final->revised_uploaded_at)->format('j M Y, g:i a') }}</small>
                </div>
                <div>
                    <a href="{{ route('papers.camera-ready.download', [$paper->id, 'revised']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Download revision
                    </a>
                    <a href="{{ route('admin.decisions.show', $paper->id) }}" class="btn btn-sm btn-outline-secondary">
                        Reviews &amp; decision
                    </a>
                </div>
            </div>

            <div class="small text-muted text-uppercase font-weight-bold">How the authors addressed the reviewers' comments</div>
            <div class="mb-3" style="white-space: pre-line;">{{ $final->revision_summary }}</div>

            @if($final->revision_reviewed_at)
                <p class="small text-muted mb-2">
                    Last checked by {{ $final->revisionReviewedBy->name ?? 'a chair' }} on {{ $final->revision_reviewed_at->format('j M Y, g:i a') }}.
                    @if($final->revision_status === 'changes_requested' && $final->revision_note)
                        <br>Note sent: {{ $final->revision_note }}
                    @endif
                </p>
            @endif

            @unless($final->isConfirmed())
                <div class="d-flex flex-wrap align-items-start">
                    @if($final->revision_status !== 'approved')
                        <form action="{{ route('admin.revisions.approve', $paper->id) }}" method="POST" class="mr-3 mb-2">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve revision</button>
                        </form>
                    @endif
                    @if($final->revision_status === 'approved' && $final->camera_ready_path)
                        <p class="small text-muted mb-2">The authors have uploaded the camera-ready version on this approval.</p>
                    @else
                    <form action="{{ route('admin.revisions.changes', $paper->id) }}" method="POST" class="flex-grow-1 mb-2">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="text" name="revision_note" class="form-control" maxlength="2000" required
                                   placeholder="What still needs to change">
                            <div class="input-group-append">
                                <button class="btn btn-outline-danger"><i class="fas fa-undo"></i> Send back</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            @endunless
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted">No revised manuscripts here.</div></div>
@endforelse

@endsection
