{{--
    The submitted evaluations of one paper.
    $review            ReviewConsolidation
    $showNames         reviewer names beside their numbers (committee only)
    $showConfidential  the confidential comments (committee only)
    $exceptUserId      optional: leave this reviewer's own evaluation out
--}}
@php
    $exceptUserId = $exceptUserId ?? null;
    $recommendationStyles = ['strong_accept' => 'success', 'accept' => 'success', 'borderline' => 'warning', 'reject' => 'danger', 'strong_reject' => 'danger'];
    $rows = $review->submitted()->reject(fn ($row) => $exceptUserId !== null && (int) $row['assignment']->reviewer_id === (int) $exceptUserId)->values();
@endphp

@if($rows->isEmpty())
    <p class="text-muted mb-0">No {{ $exceptUserId !== null ? 'other ' : '' }}reviewer has submitted an evaluation yet. Drafts are not shown.</p>
@else
    @foreach($rows as $row)
        @php $evaluation = $row['evaluation']; @endphp
        <div class="border rounded p-3 {{ $loop->last ? '' : 'mb-3' }}">
            <div class="d-flex flex-wrap justify-content-between">
                <strong>Reviewer {{ $row['number'] }}@if($showNames) &mdash; {{ $row['assignment']->reviewer->name ?? '—' }}@endif</strong>
                <small class="text-muted">
                    Submitted {{ $evaluation->submitted_at->format('j M Y') }}
                    @if($evaluation->manuscript_version) &middot; read manuscript v{{ $evaluation->manuscript_version }} @endif
                </small>
            </div>
            <div class="my-2">
                <span class="badge badge-{{ $recommendationStyles[$evaluation->recommendation] ?? 'light' }} mr-1">
                    {{ \App\Models\PaperEvaluation::RECOMMENDATIONS[$evaluation->recommendation] ?? $evaluation->recommendation }}
                </span>
                @foreach(\App\Models\PaperEvaluation::CRITERIA as $field => $label)
                    <span class="badge badge-light border mr-1">{{ $label }}: {{ $evaluation->$field }}/5 ({{ \App\Models\PaperEvaluation::SCALE[$evaluation->$field] ?? '' }})</span>
                @endforeach
                <span class="badge badge-light border">Average {{ $evaluation->averageScore() }}</span>
            </div>
            <div class="small text-muted text-uppercase font-weight-bold">Feedback for authors</div>
            <p class="mb-2" style="white-space: pre-line;">{{ $evaluation->feedback_for_authors }}</p>
            @if($showConfidential && $evaluation->confidential_comments)
                <div class="small text-danger text-uppercase font-weight-bold">Confidential &mdash; chairs only</div>
                <p class="mb-0" style="white-space: pre-line;">{{ $evaluation->confidential_comments }}</p>
            @endif
        </div>
    @endforeach
@endif
