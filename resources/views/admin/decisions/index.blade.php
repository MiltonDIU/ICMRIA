@extends('layouts.admin')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@php
    $filters = [
        '' => 'All',
        'ready' => 'Ready to decide',
        'conflict' => 'Reviewers disagree',
        'awaiting' => 'Awaiting TPC approval',
        'returned' => 'Returned',
        'approved' => 'Approved',
    ];
    $decisionStyles = ['accept' => 'success', 'minor_revisions' => 'info', 'reject' => 'danger'];
    $statusStyles = ['pending_approval' => 'warning', 'approved' => 'success', 'returned' => 'danger'];
@endphp

<div class="card mb-3">
    <div class="card-header">Decisions</div>
    <div class="card-body">
        <p class="text-muted mb-2">
            Each paper in your tracks with its evaluations brought together. A decision can be entered once at least
            {{ $minimum }} evaluations are in; the TPC Chair then approves it before the authors are told.
        </p>
        <div class="d-flex flex-wrap">
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.decisions.index', $key ? ['filter' => $key] : []) }}"
                   class="btn btn-sm mr-1 mb-1 btn-{{ $filter === $key ? 'primary' : 'outline-secondary' }}">
                    {{ $label }} <span class="badge badge-light ml-1">{{ $counts[$key ?: 'all'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">You are not listed as the chair of any track, so there is nothing to decide here.</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 8rem;">Paper</th>
                        <th>Title</th>
                        <th class="text-center" style="width: 8rem;">Evaluations</th>
                        <th class="text-center" style="width: 6rem;">Average</th>
                        <th style="width: 13rem;">Recommendations</th>
                        <th style="width: 13rem;">Decision</th>
                        <th class="text-right" style="width: 5rem;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $paper = $row['paper'];
                            $review = $row['review'];
                            $decision = $paper->decision;
                            $tally = $review->recommendationTally();
                        @endphp
                        <tr>
                            <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                            <td>
                                {{ Str::limit($paper->title, 70) }}
                                <br><small class="text-muted">{{ Str::limit($paper->subTrack->name ?? ($paper->track->name ?? ''), 50) }}</small>
                                @if($review->hasConflict())
                                    <br><span class="badge badge-danger">reviewers disagree</span>
                                @endif
                                @if($paper->discussion_opened_at)
                                    <span class="badge badge-light border">discussion open</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $review->isReady() ? 'success' : 'warning' }}">
                                    {{ $review->submittedCount() }} of {{ $review->activeCount() }}
                                </span>
                            </td>
                            <td class="text-center">{{ $review->averages()['overall'] ?? '—' }}</td>
                            <td>
                                @if($tally)
                                    @foreach($tally as $key => $number)
                                        <small class="d-block">{{ $number }} &times; {{ \App\Models\PaperEvaluation::RECOMMENDATIONS[$key] }}</small>
                                    @endforeach
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($decision)
                                    <span class="badge badge-{{ $decisionStyles[$decision->decision] ?? 'light' }}">{{ $decision->label() }}</span>
                                    <br><small class="text-{{ $statusStyles[$decision->status] ?? 'muted' }}">{{ \App\Models\PaperDecision::STATUSES[$decision->status] ?? $decision->status }}</small>
                                    @if($decision->notified_at)
                                        <small class="d-block text-muted">authors notified</small>
                                    @endif
                                @else
                                    <span class="text-muted">none yet</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.decisions.show', $paper->id) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No papers here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
