{{--
    The review decision as the authors see it, once they have been notified. Reviewer
    names, recommendations and confidential comments are never shown here.
--}}
@php $decision = $paper->decision; @endphp

@if($decision && $decision->notified_at)
    @php
        $review = \App\Services\ReviewConsolidation::for($paper);
        $style = ['accept' => 'success', 'minor_revisions' => 'info', 'reject' => 'danger'][$decision->decision] ?? 'secondary';
    @endphp
    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-gavel mr-2 text-primary"></i> Review Decision</span>
            <span class="badge badge-{{ $style }} px-3 py-2">{{ $decision->label() }}</span>
        </div>
        <div class="card-body">
            @if($decision->note_to_authors)
                <div class="small text-muted text-uppercase font-weight-bold">From the track chair</div>
                <p style="white-space: pre-line;">{{ $decision->note_to_authors }}</p>
            @endif

            @foreach($review->submitted() as $row)
                @php $evaluation = $row['evaluation']; @endphp
                <div class="border rounded p-3 mb-3">
                    <strong>Reviewer {{ $loop->iteration }}</strong>
                    <div class="my-2">
                        @foreach(\App\Models\PaperEvaluation::CRITERIA as $field => $label)
                            <span class="badge badge-light border mr-1">{{ $label }}: {{ $evaluation->$field }}/5</span>
                        @endforeach
                    </div>
                    <p class="mb-0" style="white-space: pre-line;">{{ $evaluation->feedback_for_authors }}</p>
                </div>
            @endforeach

            <small class="text-muted">
                Notified on {{ $decision->notified_at->format('j M Y') }}. Reviews are anonymous, so reviewers' names are not shown.
            </small>
        </div>
    </div>
@endif
