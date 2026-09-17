@extends('layouts.admin')

@section('styles')
@parent
<style>
    /* Evaluation Criteria & Recommendation Cards */
    .eval-options-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    /* 1 to 5 Score Pill / Card */
    .eval-score-card {
        display: inline-flex;
        align-items: center;
        padding: 7px 14px;
        margin: 0 !important;
        border-radius: 6px;
        border: 1.5px solid #cbd5e1;
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        user-select: none;
        font-size: 0.88rem;
        color: #334155;
    }
    .eval-score-card:hover:not(.disabled) {
        border-color: #94a3b8;
        background-color: #f8fafc;
    }
    .eval-score-card.disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .eval-score-input {
        margin: 0 8px 0 0 !important;
        width: 16px !important;
        height: 16px !important;
        cursor: pointer;
        flex-shrink: 0;
        vertical-align: middle;
        accent-color: #2563eb;
    }
    .score-number {
        font-weight: 700;
        margin-right: 5px;
        color: #0f172a;
    }
    .score-label {
        font-weight: 500;
        color: #475569;
    }

    /* Selected states for Scores */
    .eval-score-card.is-selected {
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .eval-score-card.eval-score-1.is-selected {
        background-color: #fef2f2 !important;
        border-color: #ef4444 !important;
        color: #991b1b !important;
    }
    .eval-score-card.eval-score-1.is-selected .score-number,
    .eval-score-card.eval-score-1.is-selected .score-label {
        color: #991b1b !important;
    }

    .eval-score-card.eval-score-2.is-selected {
        background-color: #fff7ed !important;
        border-color: #f97316 !important;
        color: #9a3412 !important;
    }
    .eval-score-card.eval-score-2.is-selected .score-number,
    .eval-score-card.eval-score-2.is-selected .score-label {
        color: #9a3412 !important;
    }

    .eval-score-card.eval-score-3.is-selected {
        background-color: #fefce8 !important;
        border-color: #eab308 !important;
        color: #854d0e !important;
    }
    .eval-score-card.eval-score-3.is-selected .score-number,
    .eval-score-card.eval-score-3.is-selected .score-label {
        color: #854d0e !important;
    }

    .eval-score-card.eval-score-4.is-selected {
        background-color: #f0fdf4 !important;
        border-color: #22c55e !important;
        color: #166534 !important;
    }
    .eval-score-card.eval-score-4.is-selected .score-number,
    .eval-score-card.eval-score-4.is-selected .score-label {
        color: #166534 !important;
    }

    .eval-score-card.eval-score-5.is-selected {
        background-color: #ecfdf5 !important;
        border-color: #10b981 !important;
        color: #065f46 !important;
    }
    .eval-score-card.eval-score-5.is-selected .score-number,
    .eval-score-card.eval-score-5.is-selected .score-label {
        color: #065f46 !important;
    }

    /* Overall Recommendation Cards */
    .eval-rec-card {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        margin: 0 !important;
        border-radius: 6px;
        border: 1.5px solid #cbd5e1;
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        user-select: none;
        font-size: 0.88rem;
        color: #334155;
    }
    .eval-rec-card:hover:not(.disabled) {
        border-color: #94a3b8;
        background-color: #f8fafc;
    }
    .eval-rec-card.disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .eval-rec-input {
        margin: 0 8px 0 0 !important;
        width: 16px !important;
        height: 16px !important;
        cursor: pointer;
        flex-shrink: 0;
        vertical-align: middle;
        accent-color: #2563eb;
    }
    .rec-icon {
        font-size: 0.95rem;
        margin-right: 6px;
        display: inline-flex;
        align-items: center;
    }
    .rec-label {
        font-weight: 600;
    }

    /* Recommendation Selected States */
    .eval-rec-card.eval-rec-strong_accept.is-selected {
        background-color: #ecfdf5 !important;
        border-color: #059669 !important;
        color: #065f46 !important;
        box-shadow: 0 0 0 1px #059669;
    }
    .eval-rec-card.eval-rec-accept.is-selected {
        background-color: #f0fdf4 !important;
        border-color: #16a34a !important;
        color: #166534 !important;
        box-shadow: 0 0 0 1px #16a34a;
    }
    .eval-rec-card.eval-rec-borderline.is-selected {
        background-color: #fefce8 !important;
        border-color: #ca8a04 !important;
        color: #854d0e !important;
        box-shadow: 0 0 0 1px #ca8a04;
    }
    .eval-rec-card.eval-rec-reject.is-selected {
        background-color: #fff1f2 !important;
        border-color: #e11d48 !important;
        color: #9f1239 !important;
        box-shadow: 0 0 0 1px #e11d48;
    }
    .eval-rec-card.eval-rec-strong_reject.is-selected {
        background-color: #fef2f2 !important;
        border-color: #dc2626 !important;
        color: #991b1b !important;
        box-shadow: 0 0 0 1px #dc2626;
    }
</style>
@endsection

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
    [$statusLabel, $statusStyle] = $statusLabels[$assignment->status] ?? [$assignment->status, 'light'];
@endphp

<div class="card mb-4 shadow-sm border">
    <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <span>{{ $paper->submission_id }} <span class="badge badge-{{ $statusStyle }} ml-1">{{ $statusLabel }}</span></span>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">Back to my reviews</a>
    </div>
    <div class="card-body">
        <h5 class="mb-2 font-weight-bold text-dark">{{ $paper->title }}</h5>
        <p class="text-muted mb-2">
            <strong>Track:</strong> {{ $paper->track->name ?? '' }}@if($paper->subTrack) &mdash; {{ $paper->subTrack->name }}@endif
        </p>

        @if(!$doubleBlind)
            <p class="text-muted mb-2">
                <strong>Authors:</strong>
                {{ $paper->authors->map(fn($a) => $a->name . ($a->affiliation ? ' (' . $a->affiliation . ')' : ''))->implode(', ') }}
            </p>
        @endif

        @if($paper->keywords)
            <p class="mb-2">
                @foreach(\App\Services\SubmissionRules::splitKeywords($paper->keywords) as $keyword)
                    <span class="badge badge-light border text-secondary mr-1 mb-1">{{ $keyword }}</span>
                @endforeach
            </p>
        @endif

        <details class="mb-3">
            <summary class="text-primary font-weight-bold" style="cursor: pointer;">Abstract</summary>
            <p class="mt-2 mb-0 p-3 bg-light rounded text-muted" style="white-space: pre-line; line-height: 1.6;">{{ $paper->abstract }}</p>
        </details>

        @if($assignment->status !== 'declined')
            @if($paper->manuscript_path)
                <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                    <i class="fas fa-download mr-1"></i> Download the manuscript
                </a>
            @else
                <div class="alert alert-warning mb-0">
                    The author has not uploaded the manuscript yet. You can start a draft, but it cannot be submitted until the file is here.
                </div>
            @endif
        @endif

        @if($assignment->status === 'invited')
            <hr>
            <p class="mb-2">Can you review this paper impartially and in time?</p>
            <div class="d-flex flex-wrap align-items-start">
                <form action="{{ route('admin.reviews.accept', $assignment->id) }}" method="POST" class="mr-2 mb-2">
                    @csrf
                    <button class="btn btn-success"><i class="fas fa-check"></i> Accept this review</button>
                </form>
                <details class="mb-2" style="flex: 1 1 20rem;">
                    <summary class="btn btn-outline-danger">Decline</summary>
                    <form action="{{ route('admin.reviews.decline', $assignment->id) }}" method="POST" class="mt-2">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="decline_reason">Why are you declining? *</label>
                            <textarea id="decline_reason" name="decline_reason" class="form-control" rows="2" maxlength="500"
                                      placeholder="For example: a conflict of interest, outside my expertise, or no time before the deadline" required>{{ old('decline_reason') }}</textarea>
                        </div>
                        <button class="btn btn-sm btn-danger">Confirm and decline</button>
                    </form>
                </details>
            </div>
        @endif

        @if($assignment->status === 'declined')
            <div class="alert alert-secondary mt-3 mb-0">
                You declined this review{{ $assignment->decline_reason ? ': ' . $assignment->decline_reason : '.' }}
            </div>
        @endif
    </div>
</div>

@if($assignment->status !== 'declined')
<div class="card mb-4 shadow-sm border">
    <div class="card-header bg-white py-3 font-weight-bold text-dark">
        <i class="fas fa-clipboard-check text-primary mr-2"></i> Evaluation
    </div>
    <div class="card-body">
        @if($evaluation->isSubmitted())
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i> Submitted on {{ $evaluation->submitted_at->format('j M Y, g:i A') }}. It can no longer be changed.
            </div>
        @elseif($assignment->status === 'invited')
            <p class="text-muted">Accept the invitation above to start, or begin a draft now &mdash; saving it accepts the review.</p>
        @endif

        <form action="{{ route('admin.reviews.save', $assignment->id) }}" method="POST">
            @csrf
            <fieldset {{ $editable ? '' : 'disabled' }}>
                @foreach($criteria as $field => $label)
                    <div class="form-group mb-4">
                        <label class="d-block font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">
                            {{ $label }} <span class="text-danger">*</span>
                        </label>
                        <div class="eval-options-row">
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $isSelected = (string) old($field, $evaluation->$field) === (string) $i;
                                @endphp
                                <label class="eval-score-card eval-score-{{ $i }} {{ $isSelected ? 'is-selected' : '' }} {{ $editable ? '' : 'disabled' }}"
                                       for="{{ $field }}-{{ $i }}">
                                    <input type="radio"
                                           id="{{ $field }}-{{ $i }}"
                                           name="{{ $field }}"
                                           value="{{ $i }}"
                                           class="eval-score-input"
                                           {{ $isSelected ? 'checked' : '' }}
                                           {{ $editable ? '' : 'disabled' }}>
                                    <span class="score-number">{{ $i }}</span>
                                    <span class="score-label">&middot; {{ \App\Models\PaperEvaluation::SCALE[$i] }}</span>
                                </label>
                            @endfor
                        </div>
                        <small class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle mr-1"></i> 1 Poor &middot; 2 Fair &middot; 3 Good &middot; 4 Very Good &middot; 5 Excellent
                        </small>
                    </div>
                @endforeach

                <div class="form-group mb-4">
                    <label class="d-block font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">
                        Overall recommendation <span class="text-danger">*</span>
                    </label>
                    <div class="eval-options-row">
                        @foreach($recommendations as $value => $label)
                            @php
                                $isSelected = old('recommendation', $evaluation->recommendation) === $value;
                                $iconClass = match($value) {
                                    'strong_accept' => 'fas fa-check-double text-success',
                                    'accept' => 'fas fa-check text-success',
                                    'borderline' => 'fas fa-adjust text-warning',
                                    'reject' => 'fas fa-times text-danger',
                                    'strong_reject' => 'fas fa-ban text-danger',
                                    default => 'far fa-circle'
                                };
                            @endphp
                            <label class="eval-rec-card eval-rec-{{ $value }} {{ $isSelected ? 'is-selected' : '' }} {{ $editable ? '' : 'disabled' }}"
                                   for="recommendation-{{ $value }}">
                                <input type="radio"
                                       id="recommendation-{{ $value }}"
                                       name="recommendation"
                                       value="{{ $value }}"
                                       class="eval-rec-input"
                                       {{ $isSelected ? 'checked' : '' }}
                                       {{ $editable ? '' : 'disabled' }}>
                                <span class="rec-icon">
                                    <i class="{{ $iconClass }}"></i>
                                </span>
                                <span class="rec-label">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="feedback_for_authors" class="font-weight-bold text-dark">
                        Feedback for authors <span class="text-danger">*</span>
                    </label>
                    <textarea id="feedback_for_authors" name="feedback_for_authors" class="form-control" rows="8"
                              maxlength="10000" placeholder="Provide constructive comments for the authors...">{{ old('feedback_for_authors', $evaluation->feedback_for_authors) }}</textarea>
                    <small class="form-text text-muted">
                        Sent to the authors with the decision, without your name. Explain what works and what should be improved.
                    </small>
                </div>

                <div class="form-group mb-4">
                    <label for="confidential_comments" class="font-weight-bold text-dark">
                        Confidential comments for the chairs
                    </label>
                    <textarea id="confidential_comments" name="confidential_comments" class="form-control" rows="4"
                              maxlength="5000" placeholder="Optional comments visible only to the track chairs and TPC chair...">{{ old('confidential_comments', $evaluation->confidential_comments) }}</textarea>
                    <small class="form-text text-muted">
                        Optional. Seen only by the track chairs and the TPC Chair, and never sent to the authors.
                    </small>
                </div>

                @if($editable)
                    <div class="pt-2">
                        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary px-3 py-2 mr-2 font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Save draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm"
                                onclick="return confirm('Submit this evaluation? It cannot be changed afterwards.');">
                            <i class="fas fa-paper-plane mr-1"></i> Submit evaluation
                        </button>
                    </div>
                @endif
            </fieldset>
        </form>
    </div>
</div>
@endif

@if($inDiscussion)
<div class="card mb-4 shadow-sm border">
    <div class="card-header bg-white py-3 font-weight-bold text-dark">
        <i class="fas fa-comments text-primary mr-2"></i> Discussion of this paper
    </div>
    <div class="card-body">
        <p class="text-muted">
            The evaluations of this paper differ, so the chair has opened a discussion. You can read the other
            evaluations here and add your view. Reviewers appear to one another by number only, and nothing here is
            sent to the authors.
        </p>
        <h6 class="font-weight-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem;">The other evaluations</h6>
        @include('admin.decisions.partials.evaluations', ['review' => $review, 'showNames' => false, 'showConfidential' => false, 'exceptUserId' => auth()->id()])
        <hr>
        @include('admin.decisions.partials.discussion', ['paper' => $paper, 'review' => $review, 'viewerIsCommittee' => false, 'canPost' => !$discussionClosed])
    </div>
</div>
@elseif($paper->discussion_opened_at && $assignment->status !== 'declined')
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-info-circle mr-2"></i>
        The chair has opened a discussion of this paper. You can join it once you have submitted your own evaluation.
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
    $(function() {
        // Synchronize selected visual state when radio changes
        $(document).on('change', '.eval-score-input', function() {
            var fieldName = $(this).attr('name');
            $('input[name="' + fieldName + '"]').closest('.eval-score-card').removeClass('is-selected');
            if ($(this).is(':checked')) {
                $(this).closest('.eval-score-card').addClass('is-selected');
            }
        });

        $(document).on('change', '.eval-rec-input', function() {
            $('input[name="recommendation"]').closest('.eval-rec-card').removeClass('is-selected');
            if ($(this).is(':checked')) {
                $(this).closest('.eval-rec-card').addClass('is-selected');
            }
        });
    });
</script>
@endsection