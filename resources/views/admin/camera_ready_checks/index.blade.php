@extends('layouts.admin')
@section('content')


@php
    $tabs = [
        'pending' => 'Awaiting check',
        'changes_requested' => 'Sent back',
        'approved' => 'Approved',
    ];
    $statusStyles = ['submitted' => 'warning', 'changes_requested' => 'danger', 'approved' => 'info', 'confirmed' => 'success'];
@endphp

<div class="card mb-3">
    <div class="card-header">Camera-Ready Check</div>
    <div class="card-body pb-2">
        <p class="text-muted small mb-3">
            Check that each camera-ready manuscript follows the conference template and carries every author's name and
            affiliation, and that the copyright form is signed. Approve the files or send them back with a note. The author can
            pay only after approval, and the paper is confirmed for the proceedings automatically once the fee is paid.
        </p>

        <div class="d-flex flex-wrap mb-2">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.camera-ready-checks.index', ['filter' => $key] + $listFilters->query()) }}"
                   class="btn btn-sm mr-1 mb-1 btn-{{ $filter === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        @include('admin.partials.list-toolbar', ['route' => 'admin.camera-ready-checks.index', 'tabParam' => 'filter', 'tabValue' => $filter, 'allTracksLabel' => $allTracks ? 'All tracks' : 'All my tracks', 'showPayment' => true])
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">You are not listed as the chair of any track, so there are no camera-ready files for you to check.</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 9rem;">Paper</th>
                        <th>Title / Track</th>
                        <th style="width: 15rem;">Files</th>
                        <th style="width: 11rem;">{{ $filter === 'pending' ? 'Waiting' : 'Checked' }}</th>
                        <th style="width: 12rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($papers as $paper)
                        @php
                            $final = $paper->cameraReady;
                            $paid = \App\Services\ProceedingsRules::isPaid($paper);
                            $version = $paper->manuscriptVersions->max('version');
                            $filesInAt = collect([$final->camera_ready_uploaded_at, $final->copyright_uploaded_at])->filter()->max();
                            $fmt = fn ($at) => $at ? $at->format('j M Y, g:i a') : '—';
                            $files = array_filter([
                                $paper->manuscript_path ? ['M' . ($version ? ' v' . $version : ''), 'Manuscript (reviewed)', $paper->downloadName('manuscript', $paper->manuscript_original_name, $version ? (int) $version : null), $paper->manuscript_uploaded_at, route('papers.manuscript.download', $paper->id)] : null,
                                $final->revised_path ? ['R', 'Revised manuscript', $paper->downloadName('revised-manuscript', $final->revised_name), $final->revised_uploaded_at, route('papers.camera-ready.download', [$paper->id, 'revised'])] : null,
                                ['CR', 'Camera-ready manuscript', $paper->downloadName('camera-ready', $final->camera_ready_name), $final->camera_ready_uploaded_at, route('papers.camera-ready.download', [$paper->id, 'camera-ready'])],
                                ['©', 'Copyright Transfer Form', $paper->downloadName('copyright-form', $final->copyright_name), $final->copyright_uploaded_at, route('papers.camera-ready.download', [$paper->id, 'copyright'])],
                            ]);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('papers.show', $paper->id) }}" class="font-weight-bold">{{ $paper->submission_id }}</a>
                                <br><span class="badge badge-{{ $statusStyles[$final->status] ?? 'light' }}">{{ \App\Models\PaperCameraReady::STATUSES[$final->status] ?? $final->status }}</span>
                                <br><span class="badge {{ $paid ? 'badge-success' : 'badge-light border' }} mt-1">{{ $paid ? 'Fee paid' : 'Unpaid' }}</span>
                            </td>
                            <td>
                                <span title="{{ $paper->title }}">{{ Str::limit($paper->title, 90) }}</span>
                                <small class="d-block text-muted">
                                    {{ Str::limit($paper->track->name ?? '', 45) }}@if($paper->subTrack) &rsaquo; {{ Str::limit($paper->subTrack->name, 45) }}@endif
                                </small>
                                <small class="d-block text-muted" title="{{ $paper->authors->pluck('name')->implode(', ') }}">
                                    {{ Str::limit($paper->authors->pluck('name')->implode(', '), 80) }}
                                </small>
                            </td>
                            <td>
                                @foreach($files as [$short, $label, $name, $at, $url])
                                    <a href="{{ $url }}" class="btn btn-xs btn-outline-primary mb-1" data-toggle="tooltip"
                                       title="{{ $label }} — {{ $name }} — uploaded {{ $fmt($at) }}">
                                        <i class="fas fa-download"></i> {{ $short }}
                                    </a>
                                @endforeach
                                <a href="{{ route('admin.camera-ready-checks.zip', $paper->id) }}" class="btn btn-xs btn-outline-secondary mb-1" data-toggle="tooltip"
                                   title="All files of {{ $paper->submission_id }} in one ZIP — {{ $paper->downloadName('files', 'x.zip') }}">
                                    <i class="fas fa-file-archive"></i> ZIP
                                </a>
                            </td>
                            <td>
                                @if($filter === 'pending')
                                    <strong>{{ $filesInAt ? $filesInAt->diffForHumans(null, true) : '—' }}</strong>
                                    <small class="d-block text-muted">since {{ $filesInAt ? $filesInAt->format('j M Y') : '—' }}</small>
                                @else
                                    <small>{{ $final->files_reviewed_at ? $final->files_reviewed_at->format('j M Y') : '—' }}</small>
                                    <small class="d-block text-muted">{{ $final->filesReviewedBy->name ?? '' }}</small>
                                    @if($final->status === 'changes_requested' && $final->admin_note)
                                        <small class="d-block text-danger" title="{{ $final->admin_note }}">{{ Str::limit($final->admin_note, 50) }}</small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($final->status === 'submitted')
                                    <form action="{{ route('admin.camera-ready-checks.approve', $paper->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Approve the files of {{ $paper->submission_id }}? The authors will be asked to pay the fee.');">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                    </form>
                                @endif
                                @if(in_array($final->status, ['submitted', 'approved'], true) && (!$paid || Gate::allows('camera_ready_review')))
                                    <button type="button" class="btn btn-sm btn-outline-danger js-send-back"
                                            data-action="{{ route('admin.camera-ready-checks.changes', $paper->id) }}"
                                            data-paper="{{ $paper->submission_id }}">
                                        <i class="fas fa-undo"></i> Send back
                                    </button>
                                @elseif($final->status === 'changes_requested')
                                    <small class="text-muted">Waiting for corrected files</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No camera-ready files here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('admin.partials.list-pagination', ['paginator' => $papers])
</div>

{{-- One "Send back" form for the whole page; the button fills in which paper. --}}
<div class="modal fade" id="sendBackModal" tabindex="-1" role="dialog" aria-labelledby="sendBackTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" id="sendBackForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="sendBackTitle">Send back <span id="sendBackPaper"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <label for="sendBackNote" class="small font-weight-bold">What the authors need to change</label>
                <textarea id="sendBackNote" name="admin_note" class="form-control" rows="4" maxlength="2000" required></textarea>
                <small class="text-muted">The authors receive this note by email and can upload corrected files.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-danger"><i class="fas fa-undo"></i> Send back</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('.js-send-back').on('click', function () {
            $('#sendBackForm').attr('action', $(this).data('action'));
            $('#sendBackPaper').text($(this).data('paper'));
            $('#sendBackNote').val('');
            $('#sendBackModal').modal('show');
        });
        $('#sendBackModal').on('shown.bs.modal', function () { $('#sendBackNote').trigger('focus'); });
    });
</script>
@endsection
