{{--
    What a submission has to meet, shown wherever authors submit: the registration form,
    the submission form, and (with $compact) beside the manuscript upload.

    In its full form the panel is folded away behind an info link, so the submission form
    opens on the fields rather than on a wall of rules. $compact stays inline, since it is
    already short and sits next to the upload button that it explains.
--}}
@php
    [$minPages, $maxPages] = \App\Services\SubmissionRules::pageLimits();
    $compact = $compact ?? false;
    $opensAt = \App\Services\SubmissionRules::manuscriptWindowOpensAt();
    $closesAt = \App\Services\SubmissionRules::manuscriptWindowClosesAt();
    // Unique per render, so the partial can appear more than once on a page.
    $guidanceId = 'submission-guidance-' . \Illuminate\Support\Str::random(6);
@endphp

@unless($compact)
    <p class="mb-3">
        <a class="font-weight-bold text-primary" data-toggle="collapse" href="#{{ $guidanceId }}"
           role="button" aria-expanded="false" aria-controls="{{ $guidanceId }}"
           title="Abstract, keyword, template and anonymity requirements — click to read">
            <i class="fas fa-info-circle mr-1"></i> Before you submit
            <small class="text-muted font-weight-normal">(submission requirements)</small>
        </a>
    </p>
@endunless

<div class="{{ $compact ? '' : 'collapse' }}" id="{{ $guidanceId }}">
    <div class="alert alert-info {{ $compact ? 'py-2 small mb-3' : 'mb-4' }}" role="note">
        @if($compact)
            <strong><i class="fas fa-info-circle mr-1"></i> Before you submit</strong>
        @endif
        <ul class="mb-0 {{ $compact ? 'mt-1' : '' }} pl-3">
            @unless($compact)
                <li>
                    An abstract of {{ \App\Services\SubmissionRules::abstractMinWords() }}&ndash;{{ \App\Services\SubmissionRules::abstractMaxWords() }} words
                    with {{ \App\Services\SubmissionRules::keywordsMin() }}&ndash;{{ \App\Services\SubmissionRules::keywordsMax() }} keywords.
                </li>
            @endunless
            <li>
                The full manuscript must use the <strong>IEEE conference template</strong> and be
                <strong>{{ $minPages }}&ndash;{{ $maxPages }} pages</strong> long.
                <a href="{{ route('author-guidelines') }}#templates" target="_blank" rel="noopener">Author guidelines &amp; templates</a>
            </li>
            @if(\App\Services\SubmissionRules::isDoubleBlind())
                <li>
                    Review is <strong>double-blind</strong>: the manuscript uploaded for review must be <strong>anonymised</strong>,
                    with no author names, affiliations or acknowledgements that reveal who wrote it.
                </li>
            @endif
            @unless($compact)
                <li>
                    The full manuscript is uploaded later from your paper page
                    @if($opensAt && $closesAt)
                        , between <strong>{{ $opensAt->format('j M') }}</strong> and <strong>{{ $closesAt->format('j M Y') }}</strong>
                    @endif.
                </li>
            @endunless
        </ul>
    </div>
</div>
