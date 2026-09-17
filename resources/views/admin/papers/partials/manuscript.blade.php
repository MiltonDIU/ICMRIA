@php
    $isOwner = (int) auth()->id() === (int) $paper->user_id;
    $canAdminManage = auth()->user()->can('paper_manuscript_manage');
    $canUpload = ($isOwner && $manuscriptWindowOpen) || $canAdminManage;
    $canConflictManage = $isOwner || auth()->user()->can('paper_conflict_manage');
@endphp

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0 rounded-lg mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-file-pdf mr-2 text-primary"></i> Full Manuscript
        </h6>
        @if($paper->manuscript_status === 'not_submitted')
            <span class="badge badge-warning">Not submitted</span>
        @elseif($paper->manuscript_status === 'revised')
            <span class="badge badge-info">Revised</span>
        @else
            <span class="badge badge-success">Submitted</span>
        @endif
    </div>

    <div class="card-body">
        @if($paper->hasManuscript())
            <div class="d-flex justify-content-between align-items-center p-3 bg-light border rounded mb-3">
                <div>
                    <strong>{{ $paper->manuscript_original_name }}</strong>
                    <br>
                    <small class="text-muted">
                        Uploaded {{ optional($paper->manuscript_uploaded_at)->format('j M Y, g:i a') }}
                    </small>
                </div>
                <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        @else
            <p class="text-muted">No manuscript on file yet.</p>
        @endif

        @if($canUpload)
            <form action="{{ route('papers.manuscript.upload', $paper->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(!$isOwner && $canAdminManage)
                    <div class="alert alert-warning py-2 mb-3 small d-flex align-items-center">
                        <i class="fas fa-shield-alt fa-lg mr-2 text-warning"></i>
                        <div>
                            <strong>Administrative Override:</strong> You have permission to upload/replace this manuscript on behalf of the author.
                        </div>
                    </div>
                @else
                    @include('partials.submission-guidance', ['compact' => true])
                @endif

                <div class="form-row align-items-end">
                    <div class="col-md-8 mb-2">
                        <label class="small font-weight-bold mb-1">
                            @if(!$isOwner && $canAdminManage)
                                {{ $paper->hasManuscript() ? 'Replace manuscript (Admin Override)' : 'Upload manuscript (Admin Override)' }}
                            @else
                                {{ $paper->hasManuscript() ? 'Replace the manuscript' : 'Upload the manuscript' }}
                            @endif
                        </label>
                        <input type="file" name="manuscript" class="form-control-file @error('manuscript') is-invalid @enderror"
                               accept=".pdf,.doc,.docx" required>
                        @error('manuscript') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-upload"></i> {{ $paper->hasManuscript() ? 'Replace' : 'Upload' }}
                        </button>
                    </div>
                </div>

                @php [$minPages, $maxPages] = \App\Services\SubmissionRules::pageLimits(); @endphp
                <div class="custom-control custom-checkbox mt-2 mb-2">
                    <input type="checkbox" class="custom-control-input @error('format_confirmed') is-invalid @enderror"
                           id="format_confirmed" name="format_confirmed" value="1" required>
                    <label class="custom-control-label" for="format_confirmed">
                        I confirm the manuscript follows the IEEE conference template and is {{ $minPages }}&ndash;{{ $maxPages }} pages long
                    </label>
                    @error('format_confirmed') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                @if(\App\Services\SubmissionRules::isDoubleBlind())
                    <div class="custom-control custom-checkbox mt-2 mb-2">
                        <input type="checkbox" class="custom-control-input @error('anonymity_confirmed') is-invalid @enderror"
                               id="anonymity_confirmed" name="anonymity_confirmed" value="1" required>
                        <label class="custom-control-label" for="anonymity_confirmed">
                            I confirm the file contains no author names or affiliations
                        </label>
                        @error('anonymity_confirmed') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    </div>
                @endif

                <small class="form-text text-muted">
                    PDF or Word, up to 20&nbsp;MB, following the conference template.
                    @if(\App\Services\SubmissionRules::isDoubleBlind())
                        Review is <strong>double-blind</strong>, and nothing can remove names from inside your file but you.
                    @endif
                    Replacing it keeps the earlier version on record.
                </small>
            </form>
        @elseif($isOwner)
            <div class="alert alert-secondary mb-0">
                @if($manuscriptOpensAt && now()->lt($manuscriptOpensAt))
                    Manuscript submission opens on <strong>{{ $manuscriptOpensAt->format('j M Y') }}</strong>.
                @elseif($manuscriptClosesAt)
                    Manuscript submission closed on <strong>{{ $manuscriptClosesAt->format('j M Y') }}</strong>.
                @else
                    Manuscript submission is not open.
                @endif
            </div>
        @endif

        @if($paper->manuscriptVersions->count() > 1)
            <hr>
            <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.6px;">
                Upload history
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($paper->manuscriptVersions as $version)
                            <tr class="{{ $loop->first ? 'font-weight-bold' : 'text-muted' }}">
                                <td style="width: 1%;">v{{ $version->version }}</td>
                                <td>
                                    {{ $version->original_name }}
                                    @if($loop->first)
                                        <span class="badge badge-success ml-1">current</span>
                                    @endif
                                    @if(!$version->anonymity_confirmed)
                                        <span class="badge badge-warning ml-1" title="Uploaded without confirming the file was anonymised">not confirmed</span>
                                    @endif
                                </td>
                                <td><small>{{ $version->created_at->format('j M Y, g:i a') }}</small></td>
                                <td><small>{{ $version->uploadedBy->name ?? '—' }}</small></td>
                                <td class="text-right">
                                    <a href="{{ route('papers.manuscript.version', [$paper->id, $version->version]) }}"
                                       class="btn btn-sm btn-link p-0" title="Download this version">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <small class="form-text text-muted">
                Earlier versions are kept so it stays clear which file a reviewer was given.
            </small>
        @endif
    </div>
</div>

<div class="card shadow-sm border-0 rounded-lg mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-user-shield mr-2 text-primary"></i> Conflicts of Interest
        </h6>
    </div>

    <div class="card-body">
        <p class="text-muted small">
            Name anyone who should not review this paper — a former supervisor, a co-author, a colleague at your
            own institution. Reviewer assignment will steer around them.
        </p>

        @if($paper->conflicts->isEmpty())
            <p class="text-muted mb-3">None declared.</p>
        @else
            <ul class="list-group mb-3">
                @foreach($paper->conflicts as $conflict)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $conflict->label }}</strong>
                            @if($conflict->note)
                                <br><small class="text-muted">{{ $conflict->note }}</small>
                            @endif
                        </div>
                        @if($canConflictManage)
                            <form action="{{ route('papers.conflicts.destroy', [$paper->id, $conflict->id]) }}" method="POST"
                                  onsubmit="return confirm('Remove this declared conflict?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i></button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if($canConflictManage)
            <form action="{{ route('papers.conflicts.store', $paper->id) }}" method="POST">
                @csrf
                @if(!$isOwner && auth()->user()->can('paper_conflict_manage'))
                    <div class="alert alert-warning py-2 mb-3 small d-flex align-items-center">
                        <i class="fas fa-shield-alt fa-lg mr-2 text-warning"></i>
                        <div>
                            <strong>Administrative Override:</strong> You have permission to declare conflicts on behalf of the author.
                        </div>
                    </div>
                @endif
                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold mb-1">Person</label>
                        <select name="conflicted_user_id" class="form-control form-control-sm">
                            <option value="">— none —</option>
                            @foreach($conflictCandidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">or Institution</label>
                        <input type="text" name="conflicted_institution" class="form-control form-control-sm"
                               placeholder="e.g. University of X">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Reason</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-sm btn-primary btn-block">
                            <i class="fa fa-plus"></i> Declare
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">
                    The list offers the chairs and reviewers attached to this paper's track.
                    Give an institution instead if the person is not listed.
                </small>
            </form>
        @endif
    </div>
</div>
