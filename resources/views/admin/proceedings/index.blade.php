@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@php
    $tabs = ['camera' => 'Camera-ready', 'payments' => 'Payments to verify', 'confirmed' => 'Confirmed for Proceedings'];
    $decisionStyles = ['accept' => 'success', 'minor_revisions' => 'info', 'reject' => 'danger'];
    $finalStyles = ['submitted' => 'info', 'changes_requested' => 'danger', 'confirmed' => 'success'];
    $proofStyles = ['submitted' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];
    $exports = ['json' => 'JSON', 'xml' => 'XML', 'abstracts' => 'Book of Abstracts (PDF)', 'program' => 'Programme (PDF)', 'files' => 'Camera-ready files (ZIP)'];
@endphp

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <span>Proceedings</span>
        <div class="d-flex flex-wrap">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.proceedings.index', ['tab' => $key]) }}"
                   class="btn btn-sm ml-1 mb-1 btn-{{ $tab === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">
            Accepted papers on their way into the proceedings. A paper can be confirmed once its camera-ready manuscript,
            the signed copyright form and a verified registration fee are all in.
        </p>
        <div class="d-flex flex-wrap align-items-center">
            <span class="small text-muted mr-2">Export papers confirmed for the proceedings:</span>
            @foreach($exports as $format => $label)
                <a href="{{ route('admin.proceedings.export', $format) }}" class="btn btn-sm btn-outline-primary mr-1 mb-1">
                    <i class="fas fa-download"></i> {{ $label }}
                </a>
            @endforeach
            <a href="{{ route('admin.proceedings.export', ['format' => 'json', 'scope' => 'accepted']) }}" class="btn btn-sm btn-link mb-1">
                Every accepted paper (JSON)
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($tab === 'camera')
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 8rem;">Paper</th>
                            <th>Title</th>
                            <th class="text-center" style="width: 8rem;">Camera-ready</th>
                            <th class="text-center" style="width: 8rem;">Copyright</th>
                            <th class="text-center" style="width: 9rem;">Fee</th>
                            <th style="width: 16rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inProgress as $paper)
                            @php
                                $final = $paper->cameraReady;
                                $missing = \App\Services\ProceedingsRules::missing($paper);
                                $pendingProof = $paper->paymentProofs->firstWhere('status', 'submitted');
                            @endphp
                            <tr>
                                <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                                <td>
                                    <a href="{{ route('papers.show', $paper->id) }}">{{ Str::limit($paper->title, 70) }}</a>
                                    <br><small class="text-muted">{{ $paper->user->name ?? '' }} &middot; {{ Str::limit($paper->track->name ?? '', 40) }}</small>
                                    <br><span class="badge badge-{{ $decisionStyles[$paper->decision->decision] ?? 'light' }}">{{ $paper->decision->label() }}</span>
                                </td>
                                <td class="text-center">
                                    @if($final?->camera_ready_path)
                                        <a href="{{ route('papers.camera-ready.download', [$paper->id, 'camera-ready']) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                                        <br><small class="text-muted">{{ optional($final->camera_ready_uploaded_at)->format('j M') }}</small>
                                    @else
                                        <span class="badge badge-light border text-muted">missing</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($final?->copyright_path)
                                        <a href="{{ route('papers.camera-ready.download', [$paper->id, 'copyright']) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                                    @else
                                        <span class="badge badge-light border text-muted">missing</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(\App\Services\ProceedingsRules::isPaid($paper))
                                        <span class="badge badge-success">paid</span>
                                    @elseif($pendingProof)
                                        <a href="{{ route('admin.proceedings.index', ['tab' => 'payments']) }}" class="badge badge-warning">proof to verify</a>
                                    @else
                                        <span class="badge badge-secondary">unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    @if($final)
                                        <span class="badge badge-{{ $finalStyles[$final->status] ?? 'light' }}">{{ \App\Models\PaperCameraReady::STATUSES[$final->status] }}</span>
                                        @if($final->status === 'changes_requested')
                                            <br><small class="text-danger">{{ $final->admin_note }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">nothing uploaded</span>
                                    @endif

                                    @can('camera_ready_review')
                                        <div class="mt-2">
                                            <form action="{{ route('admin.proceedings.confirm', $paper->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success" {{ $missing ? 'disabled' : '' }}
                                                        title="{{ $missing ? 'Outstanding: ' . implode('; ', $missing) : 'Confirm for proceedings' }}">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            @if($final)
                                                <details class="d-inline-block align-top">
                                                    <summary class="btn btn-sm btn-outline-danger">Request changes</summary>
                                                    <form action="{{ route('admin.proceedings.changes', $paper->id) }}" method="POST" class="mt-2">
                                                        @csrf
                                                        <textarea name="admin_note" class="form-control form-control-sm mb-1" rows="2" maxlength="2000" required
                                                                  placeholder="What should the author correct?"></textarea>
                                                        <button class="btn btn-sm btn-danger">Send to author</button>
                                                    </form>
                                                </details>
                                            @endif
                                        </div>
                                        @if($missing)
                                            <small class="d-block text-muted mt-1">Outstanding: {{ implode('; ', $missing) }}</small>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No accepted papers are waiting for confirmation.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'payments')
            <h6 class="font-weight-bold mb-2">Awaiting verification</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-4">
                    <thead>
                        <tr>
                            <th style="width: 8rem;">Paper</th>
                            <th>Author</th>
                            <th>Method &amp; reference</th>
                            <th class="text-right">Amount</th>
                            <th>Paid on</th>
                            <th class="text-center">Proof</th>
                            <th style="width: 15rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingProofs as $proof)
                            <tr>
                                <td><a href="{{ route('papers.show', $proof->paper_id) }}"><small>{{ $proof->paper->submission_id }}</small></a></td>
                                <td><small>{{ $proof->user->name ?? '' }}<br><span class="text-muted">{{ $proof->user->email ?? '' }}</span></small></td>
                                <td><small>{{ $proof->methodLabel() }}<br><strong>{{ $proof->transaction_id }}</strong></small></td>
                                <td class="text-right"><small>{{ $proof->currency }} {{ number_format((float) $proof->amount, 2) }}</small></td>
                                <td><small>{{ $proof->paid_on->format('j M Y') }}</small></td>
                                <td class="text-center">
                                    <a href="{{ route('papers.payment-proof.download', $proof->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-paperclip"></i></a>
                                </td>
                                <td>
                                    @can('camera_ready_review')
                                        <form action="{{ route('admin.proceedings.payments.verify', $proof->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Verify this payment and mark the paper as paid?');">
                                            @csrf
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Verify</button>
                                        </form>
                                        <details class="d-inline-block align-top">
                                            <summary class="btn btn-sm btn-outline-danger">Reject</summary>
                                            <form action="{{ route('admin.proceedings.payments.reject', $proof->id) }}" method="POST" class="mt-2">
                                                @csrf
                                                <textarea name="review_note" class="form-control form-control-sm mb-1" rows="2" maxlength="1000" required
                                                          placeholder="What is wrong with the payment?"></textarea>
                                                <button class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </details>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No payments are waiting to be verified.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reviewedProofs->isNotEmpty())
                <h6 class="font-weight-bold mb-2">Recently reviewed</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Paper</th><th>Reference</th><th class="text-right">Amount</th><th>Result</th><th>By</th></tr></thead>
                        <tbody>
                            @foreach($reviewedProofs as $proof)
                                <tr>
                                    <td><small>{{ $proof->paper->submission_id }}</small></td>
                                    <td><small>{{ $proof->transaction_id }}</small></td>
                                    <td class="text-right"><small>{{ $proof->currency }} {{ number_format((float) $proof->amount, 2) }}</small></td>
                                    <td>
                                        <span class="badge badge-{{ $proofStyles[$proof->status] ?? 'light' }}">{{ \App\Models\PaperPaymentProof::STATUSES[$proof->status] }}</span>
                                        @if($proof->review_note)<br><small class="text-muted">{{ $proof->review_note }}</small>@endif
                                    </td>
                                    <td><small>{{ $proof->reviewedBy->name ?? '—' }}, {{ optional($proof->reviewed_at)->format('j M Y') }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 8rem;">Paper</th>
                            <th>Title</th>
                            <th style="width: 9rem;">Confirmed</th>
                            <th style="width: 26rem;">Programme session</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($confirmed as $paper)
                            @php $final = $paper->cameraReady; @endphp
                            <tr>
                                <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                                <td>
                                    <a href="{{ route('papers.show', $paper->id) }}">{{ Str::limit($paper->title, 70) }}</a>
                                    <br><small class="text-muted">{{ $paper->user->name ?? '' }} &middot; {{ Str::limit($paper->subTrack->name ?? ($paper->track->name ?? ''), 45) }}</small>
                                </td>
                                <td><small>{{ optional($final->confirmed_at)->format('j M Y') }}</small></td>
                                <td>
                                    @can('camera_ready_review')
                                        <form action="{{ route('admin.proceedings.schedule', $paper->id) }}" method="POST" class="form-inline">
                                            @csrf
                                            <select name="schedule_id" class="form-control form-control-sm mr-1 mb-1" style="max-width: 16rem;">
                                                <option value="">Not scheduled</option>
                                                @foreach($schedules as $session)
                                                    <option value="{{ $session->id }}" {{ (int) $final->schedule_id === (int) $session->id ? 'selected' : '' }}>
                                                        Day {{ $session->day_number }} &middot; {{ substr((string) $session->start_time, 0, 5) }} &middot; {{ Str::limit($session->title, 40) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="presentation_order" class="form-control form-control-sm mr-1 mb-1" style="width: 5rem;"
                                                   min="1" max="99" placeholder="Slot" value="{{ $final->presentation_order }}">
                                            <button class="btn btn-sm btn-primary mb-1">Save</button>
                                        </form>
                                    @else
                                        <small>{{ $final->schedule->title ?? 'Not scheduled' }}</small>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No paper has been confirmed for the proceedings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
