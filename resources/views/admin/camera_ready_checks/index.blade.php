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
    <div class="card-header">Camera-Ready Check</div>
    <div class="card-body">
        <p class="text-muted mb-2">
            Accepted papers whose authors have uploaded the camera-ready manuscript and the signed copyright form. Check that the
            manuscript follows the conference template and carries every author's name and affiliation, and that the form is
            signed. Then approve the files or send them back with a note. The author can pay the registration fee only after
            you approve, and the paper is confirmed for the proceedings automatically once the fee is paid.
        </p>
        <div class="d-flex flex-wrap">
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.camera-ready-checks.index', ['filter' => $key]) }}"
                   class="btn btn-sm mr-1 mb-1 btn-{{ $filter === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">You are not listed as the chair of any track, so there are no camera-ready files for you to check.</div>
@endif

@forelse($papers as $paper)
    @php
        $final = $paper->cameraReady;
        $paid = \App\Services\ProceedingsRules::isPaid($paper);
        $style = ['submitted' => 'warning', 'changes_requested' => 'danger', 'approved' => 'info', 'confirmed' => 'success'][$final->status] ?? 'light';
    @endphp
    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <span><strong>{{ $paper->submission_id }}</strong> &mdash; {{ Str::limit($paper->title, 90) }}</span>
            <span>
                <span class="badge badge-{{ $style }}">{{ \App\Models\PaperCameraReady::STATUSES[$final->status] ?? '—' }}</span>
                @if($final->status === 'approved')
                    <span class="badge badge-light border">{{ $paid ? 'Fee paid' : 'Fee due' }}</span>
                @endif
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">{{ $paper->subTrack->name ?? ($paper->track->name ?? '') }}</p>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <div>
                    <strong>Camera-ready:</strong> {{ $final->camera_ready_name }}
                    <br><small class="text-muted">Uploaded {{ optional($final->camera_ready_uploaded_at)->format('j M Y, g:i a') }}</small>
                </div>
                <a href="{{ route('papers.camera-ready.download', [$paper->id, 'camera-ready']) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download manuscript
                </a>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <strong>Copyright form:</strong> {{ $final->copyright_name }}
                    <br><small class="text-muted">Uploaded {{ optional($final->copyright_uploaded_at)->format('j M Y, g:i a') }}</small>
                </div>
                <a href="{{ route('papers.camera-ready.download', [$paper->id, 'copyright']) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download form
                </a>
            </div>

            <p class="small mb-2">
                <strong>Authors:</strong> {{ $paper->authors->pluck('name')->implode(', ') }}
            </p>

            @if($final->files_reviewed_at)
                <p class="small text-muted mb-2">
                    Last checked by {{ $final->filesReviewedBy->name ?? 'a chair' }} on {{ $final->files_reviewed_at->format('j M Y, g:i a') }}.
                    @if($final->status === 'changes_requested' && $final->admin_note)
                        <br>Note sent: {{ $final->admin_note }}
                    @endif
                </p>
            @endif

            @if($final->isConfirmed())
                <p class="small text-success mb-0"><i class="fas fa-check-circle"></i> Fee paid; confirmed for the proceedings.</p>
            @elseif($final->status === 'changes_requested')
                <p class="small text-muted mb-0">Waiting for the authors to upload corrected files; they will come back to "Awaiting check".</p>
            @else
                <div class="d-flex flex-wrap align-items-start">
                    @if($final->status === 'submitted')
                        <form action="{{ route('admin.camera-ready-checks.approve', $paper->id) }}" method="POST" class="mr-3 mb-2">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve files</button>
                        </form>
                    @endif
                    @if(!$paid || Gate::allows('camera_ready_review'))
                        <form action="{{ route('admin.camera-ready-checks.changes', $paper->id) }}" method="POST" class="flex-grow-1 mb-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="text" name="admin_note" class="form-control" maxlength="2000" required
                                       placeholder="What the authors need to change">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-danger"><i class="fas fa-undo"></i> Send back</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted">No camera-ready files here.</div></div>
@endforelse

@endsection
