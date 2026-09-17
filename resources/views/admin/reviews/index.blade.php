@extends('layouts.admin')

@section('styles')
@parent
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 6px;
        min-height: 44px;
        padding: 4px 6px;
        background-color: #fff;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border: 1px solid #0069d9;
        color: #ffffff;
        border-radius: 4px;
        padding: 4px 10px;
        margin: 3px 5px 3px 0;
        font-size: 0.875rem;
        line-height: 1.4;
        display: inline-flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.85) !important;
        margin-right: 6px;
        font-weight: bold;
        cursor: pointer;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffffff !important;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        margin-top: 5px;
        margin-bottom: 5px;
        padding-left: 4px;
    }
    .btn-xs {
        padding: 0.15rem 0.45rem;
        font-size: 0.75rem;
        line-height: 1.4;
        border-radius: 0.2rem;
    }
</style>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@include('partials.bidding-banner')

@php
    $statusLabels = [
        'invited' => ['Awaiting your answer', 'warning'],
        'accepted' => ['Accepted', 'info'],
        'in_progress' => ['Draft saved', 'primary'],
        'completed' => ['Submitted', 'success'],
        'declined' => ['Declined', 'secondary'],
    ];
@endphp

<div class="card mb-3">
    <div class="card-header">My Reviews</div>
    <div class="card-body">
        <p class="text-muted mb-2">
            The papers the track chairs have asked you to review. Accept or decline each invitation, read the
            manuscript, then fill in the evaluation form. A draft stays private until you submit it.
            @if($doubleBlind)
                Review is double-blind: author names are withheld from you, and yours from the authors.
            @endif
        </p>
        <div>
            <span class="badge badge-warning mr-1">{{ $counts['invited'] ?? 0 }} awaiting your answer</span>
            <span class="badge badge-primary mr-1">{{ ($counts['accepted'] ?? 0) + ($counts['in_progress'] ?? 0) }} to evaluate</span>
            <span class="badge badge-success mr-1">{{ $counts['completed'] ?? 0 }} submitted</span>
            @if($counts['declined'] ?? 0)
                <span class="badge badge-secondary">{{ $counts['declined'] }} declined</span>
            @endif
        </div>
    </div>
</div>

@php
    $myKeywords = auth()->user()->allExpertise();
    $keywordsMin = \App\Services\SubmissionRules::reviewerKeywordsMin();
    $keywordsMax = \App\Services\SubmissionRules::reviewerKeywordsMax();
    $suggestedAreas = $suggestedAreas ?? \App\Services\SubmissionRules::suggestedResearchAreas();

    $selectedKeywords = old('research_keywords') !== null
        ? \App\Services\SubmissionRules::splitKeywords(old('research_keywords'))
        : $myKeywords;

    $selectedMap = [];
    foreach ($selectedKeywords as $k) {
        $selectedMap[\App\Services\SubmissionRules::normaliseKeyword($k)] = true;
    }

    $allOptions = \App\Services\SubmissionRules::splitKeywords(array_merge($suggestedAreas, $selectedKeywords));
    natcasesort($allOptions);
@endphp
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-microscope text-primary mr-2"></i> My Research Areas & Expertise
        </h6>
        @if(count($myKeywords) < $keywordsMin)
            <span class="badge badge-warning py-1 px-2 font-weight-normal">
                <i class="fas fa-exclamation-circle mr-1"></i> Please add at least {{ $keywordsMin }} research area{{ $keywordsMin > 1 ? 's' : '' }}
            </span>
        @else
            <span class="badge badge-success py-1 px-2 font-weight-normal">
                <i class="fas fa-check-circle mr-1"></i> {{ count($myKeywords) }} area{{ count($myKeywords) > 1 ? 's' : '' }} saved
            </span>
        @endif
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Select existing research areas from the dropdown, or type a new one and press <kbd>Enter</kbd> or <kbd>,</kbd> to create it.
            You can select <strong>{{ $keywordsMin }} to {{ $keywordsMax }}</strong> areas. Papers matching your research areas are prioritized when matching reviewers.
        </p>

        @error('research_keywords')
            <div class="alert alert-danger py-2 small mb-3">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $message }}
            </div>
        @enderror

        <form action="{{ route('admin.reviews.expertise') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="research_keywords" class="small font-weight-bold text-dark mb-0">
                        Research Areas / Keywords
                    </label>
                    <button type="button" class="btn btn-outline-secondary btn-xs" id="clear-all-keywords">
                        <i class="fas fa-times mr-1"></i> Clear all
                    </button>
                </div>
                <select name="research_keywords[]" id="research_keywords" class="form-control select2-keywords" multiple="multiple" style="width: 100%;">
                    @foreach($allOptions as $option)
                        <option value="{{ $option }}" {{ isset($selectedMap[\App\Services\SubmissionRules::normaliseKeyword($option)]) ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <span class="small text-muted mb-2 mb-md-0" id="keyword-count-display">
                    <i class="fas fa-tags text-secondary mr-1"></i> Selected: <strong>{{ count($selectedKeywords) }}</strong> / {{ $keywordsMax }} areas
                </span>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Save Research Areas
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 8rem;">Paper</th>
                        <th>Title</th>
                        <th class="text-center" style="width: 8rem;">Manuscript</th>
                        <th class="text-center" style="width: 10rem;">Status</th>
                        <th class="text-right" style="width: 13rem;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php
                            $paper = $assignment->paper;
                            [$label, $style] = $statusLabels[$assignment->status] ?? [$assignment->status, 'light'];
                        @endphp
                        <tr class="{{ $assignment->status === 'declined' ? 'text-muted' : '' }}">
                            <td><small class="text-muted">{{ $paper->submission_id }}</small></td>
                            <td>
                                {{ Str::limit($paper->title, 80) }}
                                <br><small class="text-muted">{{ Str::limit($paper->subTrack->name ?? ($paper->track->name ?? ''), 60) }}</small>
                                @if($assignment->status === 'declined' && $assignment->decline_reason)
                                    <br><small>You said: {{ $assignment->decline_reason }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($assignment->status === 'declined')
                                    <span class="text-muted">&mdash;</span>
                                @elseif($paper->manuscript_path)
                                    <a href="{{ route('papers.manuscript.download', $paper->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                @else
                                    <span class="badge badge-light border text-muted">not uploaded yet</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge badge-{{ $style }}">{{ $label }}</span></td>
                            <td class="text-right">
                                @if($assignment->status === 'invited')
                                    <form action="{{ route('admin.reviews.accept', $assignment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Accept</button>
                                    </form>
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-outline-secondary">Open / decline</a>
                                @elseif(in_array($assignment->status, ['accepted', 'in_progress'], true))
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-primary">
                                        {{ $assignment->status === 'in_progress' ? 'Continue evaluation' : 'Write evaluation' }}
                                    </a>
                                @elseif($assignment->status === 'completed')
                                    <a href="{{ route('admin.reviews.show', $assignment->id) }}" class="btn btn-sm btn-outline-success">View evaluation</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No papers have been assigned to you yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
    $(function() {
        var $select = $('#research_keywords');

        function updateCounter() {
            var count = ($select.val() || []).length;
            $('#keyword-count-display strong').text(count);
        }

        $select.select2({
            tags: true,
            tokenSeparators: [',', ';'],
            placeholder: 'Select existing or type to create new research areas...',
            maximumSelectionLength: {{ $keywordsMax }},
            allowClear: false,
            width: '100%',
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '' || term.length < 2 || term.length > 100) {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            templateResult: function (data) {
                var $result = $('<span></span>').text(data.text);
                if (data.newTag) {
                    $result.append(' <span class="badge badge-info ml-1" style="font-size: 0.75rem;">Create new</span>');
                }
                return $result;
            }
        });

        $select.on('change', updateCounter);

        $('#clear-all-keywords').on('click', function() {
            $select.val(null).trigger('change');
            updateCounter();
        });
    });
</script>
@endsection
