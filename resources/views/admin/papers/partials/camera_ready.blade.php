{{--
    Camera-ready manuscript, copyright form and proceedings status, for an accepted paper.
--}}
@if(\App\Services\ProceedingsRules::isAccepted($paper))
    @php
        $final = $paper->cameraReady;
        $checklist = \App\Services\ProceedingsRules::checklist($paper);
        $isOwner = auth()->id() === $paper->user_id;
        $windowOpen = \App\Services\ProceedingsRules::cameraReadyWindowIsOpen();
        $deadline = \App\Services\ProceedingsRules::cameraReadyDeadline();
        $locked = $final && $final->isConfirmed();
        $statusStyle = ['submitted' => 'info', 'changes_requested' => 'danger', 'confirmed' => 'success'][$final->status ?? ''] ?? 'warning';
    @endphp

    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-book mr-2 text-primary"></i> Camera-Ready &amp; Proceedings</span>
            <span class="badge badge-{{ $statusStyle }} px-3 py-2">
                {{ $final ? \App\Models\PaperCameraReady::STATUSES[$final->status] : 'Not submitted' }}
            </span>
        </div>
        <div class="card-body">
            @if($final && $final->status === 'changes_requested')
                <div class="alert alert-danger">
                    <strong>Changes requested:</strong> {{ $final->admin_note }}
                </div>
            @endif

            @if($locked)
                <div class="alert alert-success">
                    Confirmed for the proceedings on {{ optional($final->confirmed_at)->format('j M Y') }}.
                    @if($final->schedule)
                        <br>You present on <strong>Day {{ $final->schedule->day_number }}</strong> at
                        <strong>{{ substr((string) $final->schedule->start_time, 0, 5) }}</strong>, in
                        &ldquo;{{ $final->schedule->title }}&rdquo;@if($final->presentation_order) (slot {{ $final->presentation_order }})@endif.
                    @else
                        <br>Your presentation session will appear here once the programme is scheduled.
                    @endif
                </div>
            @endif

            <ul class="list-unstyled mb-3">
                @foreach($checklist as $item)
                    <li class="mb-1">
                        <i class="fas {{ $item['done'] ? 'fa-check-circle text-success' : 'fa-circle text-muted' }} mr-2"></i>{{ $item['label'] }}
                    </li>
                @endforeach
            </ul>

            @if(\App\Services\ProceedingsRules::needsRevision($paper))
                @php
                    $revisionDeadline = \App\Services\ProceedingsRules::revisionDeadline();
                    $revisionOpen = \App\Services\ProceedingsRules::revisionWindowIsOpen();
                @endphp
                <div class="border rounded p-3 mb-3 {{ $final?->revised_path ? 'bg-light' : 'border-warning' }}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <strong><i class="fas fa-redo mr-1"></i> Step 1: Revised manuscript</strong>
                        @if($revisionDeadline)
                            <small class="text-muted">Deadline: {{ $revisionDeadline->format('j M Y, g:i a') }}</small>
                        @endif
                    </div>
                    <p class="small text-muted mb-2">
                        Your paper was accepted with minor revisions. Upload the revised manuscript that addresses the reviewers'
                        comments and summarise what you changed. The camera-ready version and copyright form come next.
                    </p>
                    @if($final?->revised_path)
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <div>
                                <strong>Revised:</strong> {{ $final->revised_name }}
                                <br><small class="text-muted">Uploaded {{ optional($final->revised_uploaded_at)->format('j M Y, g:i a') }}</small>
                            </div>
                            <a href="{{ route('papers.camera-ready.download', [$paper->id, 'revised']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @endif
                    @if($isOwner && !$locked)
                        @if($revisionOpen)
                            <form action="{{ route('papers.revision.upload', $paper->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="revised_manuscript" class="small font-weight-bold">
                                        {{ $final?->revised_path ? 'Replace the revised manuscript' : 'Revised manuscript' }} *
                                    </label>
                                    <input type="file" id="revised_manuscript" name="revised_manuscript" class="form-control-file" accept=".pdf,.doc,.docx" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="revision_summary" class="small font-weight-bold">How you addressed the reviewers' comments *</label>
                                    <textarea id="revision_summary" name="revision_summary" class="form-control" rows="4" maxlength="5000" required>{{ old('revision_summary', $final->revision_summary ?? '') }}</textarea>
                                </div>
                                <button class="btn btn-sm btn-primary"><i class="fas fa-upload"></i> Upload revised manuscript</button>
                            </form>
                        @else
                            <div class="alert alert-warning small mb-0">The deadline for the revised manuscript has passed. Please contact the conference team.</div>
                        @endif
                    @endif
                </div>
            @endif

            @if($final && ($final->camera_ready_path || $final->copyright_path))
                <div class="border rounded p-3 mb-3 bg-light">
                    @if($final->camera_ready_path)
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <div>
                                <strong>Camera-ready:</strong> {{ $final->camera_ready_name }}
                                <br><small class="text-muted">Uploaded {{ optional($final->camera_ready_uploaded_at)->format('j M Y, g:i a') }}</small>
                            </div>
                            <a href="{{ route('papers.camera-ready.download', [$paper->id, 'camera-ready']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @endif
                    @if($final->copyright_path)
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <strong>Copyright form:</strong> {{ $final->copyright_name }}
                                <br><small class="text-muted">Uploaded {{ optional($final->copyright_uploaded_at)->format('j M Y, g:i a') }}</small>
                            </div>
                            <a href="{{ route('papers.camera-ready.download', [$paper->id, 'copyright']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @endif
                    @if($final->revision_summary)
                        <div class="small text-muted text-uppercase font-weight-bold mt-2">How the reviewers' comments were addressed</div>
                        <div style="white-space: pre-line;">{{ $final->revision_summary }}</div>
                    @endif
                </div>
            @endif

            @if($isOwner && !$locked)
                @if($windowOpen)
                    <form action="{{ route('papers.camera-ready.upload', $paper->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="camera_ready" class="font-weight-bold">Camera-ready manuscript</label>
                            <input type="file" id="camera_ready" name="camera_ready" class="form-control-file" accept=".pdf,.doc,.docx">
                            <small class="form-text text-muted">
                                PDF or Word, up to 20 MB. Unlike the review copy, this version must include every author's name and
                                affiliation. Follow the <a href="{{ route('camera-ready-guidelines') }}" target="_blank">Camera-Ready Guidelines</a>.
                            </small>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="names_confirmed" name="names_confirmed" value="1">
                            <label class="custom-control-label" for="names_confirmed">
                                The camera-ready manuscript carries every author name and affiliation and follows the conference template.
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="copyright_form" class="font-weight-bold">Signed copyright transfer form</label>
                            <input type="file" id="copyright_form" name="copyright_form" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                PDF or a clear scan (JPG or PNG), up to 5 MB.
                                @if(\App\Http\Controllers\Admin\CopyrightFormController::available())
                                    <a href="{{ route('copyright-form.download') }}"><i class="fas fa-file-download"></i> Download the blank Copyright Transfer Form</a>, sign it, and upload it here.
                                @else
                                    The blank Copyright Transfer Form will be available to download here shortly.
                                @endif
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                        @if($deadline)
                            <small class="text-muted ml-2">Deadline: {{ $deadline->format('j M Y, g:i a') }}</small>
                        @endif
                    </form>
                @else
                    <div class="alert alert-warning mb-0">The camera-ready deadline has passed. Please contact the conference team.</div>
                @endif
            @endif
        </div>
    </div>
@endif
