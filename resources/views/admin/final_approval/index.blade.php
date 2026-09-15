@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if(session('skipped'))
    <div class="alert alert-warning">
        <ul class="mb-0">
            @foreach(session('skipped') as $line)<li>{{ $line }}</li>@endforeach
        </ul>
    </div>
@endif

@php
    $tabs = ['pending' => 'Awaiting approval', 'returned' => 'Returned to chairs', 'approved' => 'Approved'];
    $decisionStyles = ['accept' => 'success', 'minor_revisions' => 'info', 'reject' => 'danger'];
@endphp

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <span>Final Approval</span>
        <div class="d-flex flex-wrap">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.final-approval.index', ['tab' => $key]) }}"
                   class="btn btn-sm ml-1 mb-1 btn-{{ $tab === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <p class="text-muted mb-2 mr-3">
            Decisions entered by the track chairs, across every track. Approve them in bulk, or return one to its chair
            with a note. Approving does not email anyone: authors are told when the decision emails are sent.
        </p>
        @if($unnotified > 0)
            <form action="{{ route('admin.final-approval.notify') }}" method="POST" class="mb-2"
                  onsubmit="return confirm('Send the decision email to the authors of {{ $unnotified }} approved paper(s)?');">
                @csrf
                <button class="btn btn-sm btn-success">
                    <i class="fas fa-envelope"></i> Send decision emails ({{ $unnotified }})
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($decisions->isEmpty())
            <p class="text-center text-muted mb-0">Nothing here.</p>
        @else
            @if($tab === 'pending')
                <form id="approve-form" action="{{ route('admin.final-approval.approve') }}" method="POST">@csrf</form>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-3">
                    <thead>
                        <tr>
                            @if($tab === 'pending')
                                <th style="width: 2rem;"><input type="checkbox" id="select-all" aria-label="Select all"></th>
                            @endif
                            <th style="width: 8rem;">Paper</th>
                            <th>Title</th>
                            <th style="width: 11rem;">Decision</th>
                            <th class="text-center" style="width: 8rem;">Evaluations</th>
                            <th style="width: 11rem;">Chair</th>
                            <th style="width: 15rem;">
                                {{ $tab === 'pending' ? 'Return to chair' : ($tab === 'returned' ? 'Returned because' : 'Approval') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($decisions as $decision)
                            @php
                                $paper = $decision->paper;
                                $review = \App\Services\ReviewConsolidation::for($paper);
                            @endphp
                            <tr>
                                @if($tab === 'pending')
                                    <td>
                                        <input type="checkbox" class="decision-check" name="decision_ids[]" value="{{ $decision->id }}" form="approve-form">
                                    </td>
                                @endif
                                <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                                <td>
                                    <a href="{{ route('admin.decisions.show', $paper->id) }}">{{ Str::limit($paper->title, 70) }}</a>
                                    <br><small class="text-muted">{{ Str::limit($paper->track->name ?? '', 50) }}</small>
                                    @if($review->hasConflict())
                                        <br><span class="badge badge-danger">reviewers disagreed</span>
                                    @endif
                                    @php $comments = $paper->decisionComments; @endphp
                                    @if($comments->isNotEmpty())
                                        <details class="mt-1">
                                            <summary class="small text-primary" style="cursor: pointer;">
                                                {{ $comments->count() }} comment{{ $comments->count() === 1 ? '' : 's' }} &middot; round {{ $decision->round }}
                                            </summary>
                                            <div class="mt-1">@include('admin.decisions.partials.comments', ['comments' => $comments])</div>
                                        </details>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $decisionStyles[$decision->decision] ?? 'light' }}">{{ $decision->label() }}</span></td>
                                <td class="text-center">
                                    <small>{{ $review->submittedCount() }} submitted<br>average {{ $review->averages()['overall'] ?? '—' }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ $decision->decidedBy->name ?? '—' }}
                                        <br><span class="text-muted">{{ optional($decision->decided_at)->format('j M Y') }}</span>
                                    </small>
                                </td>
                                <td>
                                    @if($tab === 'pending')
                                        <details>
                                            <summary class="btn btn-sm btn-outline-danger">Return</summary>
                                            <form action="{{ route('admin.final-approval.return', $decision->id) }}" method="POST" class="mt-2">
                                                @csrf
                                                <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" maxlength="2000" required
                                                          placeholder="What should the chair reconsider?"></textarea>
                                                <button class="btn btn-sm btn-danger">Return to chair</button>
                                            </form>
                                        </details>
                                    @elseif($tab === 'returned')
                                        <small>{{ optional($paper->decisionComments->where('kind', 'returned')->last())->body }}</small>
                                    @else
                                        <small>
                                            {{ $decision->approvedBy->name ?? '—' }}, {{ optional($decision->approved_at)->format('j M Y') }}
                                            <br>
                                            @if($decision->notified_at)
                                                <span class="text-success">authors notified {{ $decision->notified_at->format('j M Y') }}</span>
                                            @else
                                                <span class="text-warning">authors not yet notified</span>
                                            @endif
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tab === 'pending')
                <button type="submit" form="approve-form" class="btn btn-success"
                        onclick="return confirm('Approve the ticked decisions?');">
                    <i class="fas fa-check-double"></i> Approve the ticked decisions
                </button>
                <script>
                    document.getElementById('select-all').addEventListener('change', function (event) {
                        document.querySelectorAll('.decision-check').forEach(function (box) {
                            box.checked = event.target.checked;
                        });
                    });
                </script>
            @endif
        @endif
    </div>
</div>

@endsection
