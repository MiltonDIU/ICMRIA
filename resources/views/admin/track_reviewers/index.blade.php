@extends('layouts.admin')

@section('content')

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <span>Reviewers by Track</span>
        <div style="min-width: 260px;" class="mt-2 mt-md-0">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" id="track-scope-search" class="form-control" placeholder="Quick filter tracks on page...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">
            @if($seesEverything)
                You can manage the reviewer pool of every track.
            @else
                You can manage the reviewers of the tracks and sub-tracks you chair.
                An overall Track Chair covers the whole track; a Sub-Track Chair covers their own sub-track.
            @endif
            Adding someone here makes them available for paper assignment; it does not assign them a paper yet.
            Click a name to open that reviewer's profile &mdash; their expertise, the tracks they serve and every paper
            already sitting with them. Papers from a track you do not chair are counted there but not named.
        </p>
    </div>
</div>

@if($hasNoScope)
    <div class="alert alert-warning">
        You are not listed as the chair of any track yet, so there is nothing to manage here.
        An administrator assigns chairs under <strong>Tracks &amp; Chairs</strong>.
    </div>
@endif

@foreach($scopes as $scope)
    @php
        $track = $scope['track'];
        $subTrack = $scope['sub_track'];
        $key = $track->id . ':' . ($subTrack->id ?? 'all');
        $assigned = $reviewersByScope[$key] ?? collect();
        $formId = 'add-reviewer-' . str_replace(':', '-', $key);
        // A track may set its own ceiling; otherwise the conference-wide one applies.
        $capacity = (int) ($track->max_papers_per_reviewer ?: $defaultCapacity);
    @endphp

    <div class="card mb-4 scope-card" data-track-name="{{ strtolower(($subTrack->name ?? '') . ' ' . $track->name) }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                @if($subTrack)
                    <strong>{{ $subTrack->name }}</strong>
                    <br><small class="text-muted">{{ $track->name }}</small>
                @else
                    <strong>{{ $track->name }}</strong>
                    <br><small class="text-muted">Whole track — covers every sub-track below it</small>
                @endif
            </div>
            <span class="badge {{ $assigned->isEmpty() ? 'badge-warning' : 'badge-success' }}">
                {{ $assigned->count() }} reviewer{{ $assigned->count() === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="card-body">
            @if($assigned->isEmpty())
                <p class="text-muted">No reviewers here yet.</p>
            @else
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Reviewer</th>
                                <th>Email</th>
                                <th>Expertise</th>
                                <th class="text-center" style="width: 9rem;">Papers assigned</th>
                                <th class="text-right">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assigned as $assignment)
                                @php
                                    $reviewerExpertise = $assignment->user ? $assignment->user->allExpertise() : ($assignment->expertise ?? []);
                                    $load = $loads[$assignment->user_id] ?? ['open' => 0, 'done' => 0, 'declined' => 0, 'total' => 0];
                                @endphp
                                <tr>
                                    <td>
                                        @if($assignment->user)
                                            <a href="{{ route('admin.track-reviewers.show', $assignment->user_id) }}" class="font-weight-bold">
                                                {{ $assignment->user->name }}
                                            </a>
                                        @else
                                            <strong>Unknown</strong>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $assignment->user->email ?? '' }}</small></td>
                                    <td>
                                        @forelse($reviewerExpertise as $topic)
                                            <span class="badge badge-light border mr-1 mb-1">{{ $topic }}</span>
                                        @empty
                                            <small class="text-muted">—</small>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        @if($assignment->user)
                                            <a href="{{ route('admin.track-reviewers.show', $assignment->user_id) }}"
                                               class="badge {{ $load['open'] >= $capacity ? 'badge-danger' : ($load['open'] ? 'badge-info' : 'badge-light border text-muted') }}"
                                               title="Open papers against the limit for this track. Click for the full list.">
                                                {{ $load['open'] }} / {{ $capacity }}
                                            </a>
                                            @if($load['done'])
                                                <small class="text-muted d-block">{{ $load['done'] }} submitted</small>
                                            @endif
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.track-reviewers.destroy', $assignment->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Remove this reviewer from this track? Their account stays.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($pool->isNotEmpty())
                <form action="{{ route('admin.track-reviewers.store') }}" method="POST" class="mb-3 pb-3 border-bottom">
                    @csrf
                    <input type="hidden" name="track_id" value="{{ $track->id }}">
                    <input type="hidden" name="sub_track_id" value="{{ $subTrack->id ?? '' }}">

                    <div class="form-row align-items-end">
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold mb-1">
                                <i class="fas fa-search text-primary mr-1"></i> Add someone who already reviews
                            </label>
                            <select name="reviewer_id" class="form-control form-control-sm js-reviewer-pool" required
                                    data-topics="{{ implode(',', $scopeTopics[$key] ?? []) }}"
                                    data-here="{{ $assigned->pluck('user_id')->implode(',') }}" style="width: 100%;">
                                <option value="">Loading the reviewer pool&hellip;</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold mb-1">Expertise here</label>
                            <select name="expertise[]" multiple class="form-control form-control-sm js-expertise-tags"
                                    data-placeholder="Blank = carry over what they already cover" style="width: 100%;"></select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-sm btn-primary btn-block">
                                <i class="fa fa-user-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        Anyone holding the Reviewer role can be searched and added here.
                        <strong>&#9733;</strong> marks those whose recorded expertise overlaps this subject.
                    </small>
                </form>
            @endif

            <form action="{{ route('admin.track-reviewers.store') }}" method="POST" id="{{ $formId }}">
                @csrf
                <input type="hidden" name="track_id" value="{{ $track->id }}">
                <input type="hidden" name="sub_track_id" value="{{ $subTrack->id ?? '' }}">

                <label class="small font-weight-bold mb-2 d-block">Or invite somebody new</label>

                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Name*</label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold mb-1">Email*</label>
                        <input type="email" name="email" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold mb-1">Expertise</label>
                        <select name="expertise[]" multiple class="form-control form-control-sm js-expertise-tags"
                                data-placeholder="Blank = this sub-track's topics" style="width: 100%;"></select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-sm btn-success btn-block">
                            <i class="fa fa-plus"></i> Add Reviewer
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">
                    An existing account with this address is given the Reviewer role and added here.
                    A new address creates an account; the reviewer sets their own password through the reset link.
                </small>
            </form>
        </div>
    </div>
@endforeach

@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pool = @json($pool);

    // Track scope quick filter
    var $trackSearch = $('#track-scope-search');
    if ($trackSearch.length) {
        $trackSearch.on('input keyup', function () {
            var q = $(this).val().toLowerCase().trim();
            $('.scope-card').each(function () {
                var name = $(this).data('track-name') || $(this).text().toLowerCase();
                $(this).toggle(q === '' || name.indexOf(q) !== -1);
            });
        });
    }

    // With nothing typed, only the best few are listed; typing searches everyone.
    var SHOWN_WITHOUT_SEARCH = 10;

    // Custom multi-field search matcher for Select2. The options are a flat list (no
    // optgroups): an optgroup has no id, so an earlier "skip anything without an id"
    // check hid every reviewer as soon as something was typed.
    function reviewerCustomMatcher(params, data) {
        // The placeholder row is never a result.
        if (!data.id) {
            return null;
        }

        var $opt = $(data.element);

        if (!params.term || $.trim(params.term) === '') {
            return (parseInt($opt.data('rank'), 10) || 0) < SHOWN_WITHOUT_SEARCH ? data : null;
        }

        var name = ($opt.data('name') || data.name || '').toString().toLowerCase();
        var email = ($opt.data('email') || data.email || '').toString().toLowerCase();
        var tracks = ($opt.data('tracks') || data.tracks || '').toString().toLowerCase();
        var expertise = ($opt.data('expertise') || data.expertise || '').toString().toLowerCase();
        var text = (data.text || '').toString().toLowerCase();

        var haystack = name + ' ' + email + ' ' + tracks + ' ' + expertise + ' ' + text;
        var queryTerms = params.term.toLowerCase().trim().split(/\s+/).filter(Boolean);

        var matchesAll = queryTerms.every(function (term) {
            return haystack.indexOf(term) !== -1;
        });

        return matchesAll ? data : null;
    }

    document.querySelectorAll('.js-reviewer-pool').forEach(function (select) {
        var topics = (select.dataset.topics || '').split(',').filter(Boolean);
        var here = (select.dataset.here || '').split(',').filter(Boolean).map(Number);

        var availableRows = [];
        var assignedRows = [];

        pool.forEach(function (person) {
            var isHere = here.indexOf(person.id) !== -1;
            var hits = (person.slugs || []).filter(function (slug) {
                return topics.indexOf(slug) !== -1;
            }).length;

            var item = { person: person, hits: hits, already: isHere };
            if (isHere) {
                assignedRows.push(item);
            } else {
                availableRows.push(item);
            }
        });

        // Best subject match first, then alphabetically
        availableRows.sort(function (a, b) {
            return b.hits - a.hits || a.person.name.localeCompare(b.person.name);
        });

        assignedRows.sort(function (a, b) {
            return a.person.name.localeCompare(b.person.name);
        });

        select.innerHTML = '';

        var blank = document.createElement('option');
        blank.value = '';
        blank.textContent = 'Search or select a reviewer (' + pool.length + ' available in pool)...';
        select.appendChild(blank);

        function createOption(item, rank) {
            var option = document.createElement('option');
            option.value = item.person.id;
            option.dataset.rank = rank;
            option.dataset.name = item.person.name;
            option.dataset.email = item.person.email || '';
            option.dataset.tracks = (item.person.tracks || []).join(', ');
            option.dataset.expertise = (item.person.expertise || []).join(', ');
            option.dataset.hits = item.hits ? '1' : '0';
            option.dataset.already = item.already ? '1' : '0';
            option.dataset.load = item.person.load || 0;

            var parts = [item.person.name];
            if (item.person.email) {
                parts.push('(' + item.person.email + ')');
            }
            if (item.person.tracks && item.person.tracks.length) {
                parts.push('— in ' + item.person.tracks.join(', '));
            }
            if (item.person.expertise && item.person.expertise.length) {
                parts.push('— ' + item.person.expertise.slice(0, 4).join(', '));
            }
            if (item.person.load) {
                parts.push('— ' + item.person.load + ' paper' + (item.person.load === 1 ? '' : 's') + ' open');
            }
            if (item.already) {
                parts.push('[Already in this track]');
            }

            option.textContent = (item.hits ? '★ ' : '') + parts.join(' ');
            return option;
        }

        // One flat list: the best-matching available reviewers first, then those already in
        // this track (badged "Already in this track"). rank decides who shows untyped.
        availableRows.concat(assignedRows).forEach(function (item, rank) {
            select.appendChild(createOption(item, rank));
        });

        // Initialize Select2
        var $s2 = $(select);
        if ($s2.hasClass('select2-hidden-accessible')) {
            $s2.select2('destroy');
        }

        $s2.select2({
            width: '100%',
            placeholder: pool.length > SHOWN_WITHOUT_SEARCH
                ? 'Top ' + SHOWN_WITHOUT_SEARCH + ' shown — type a name, email or expertise to search all ' + pool.length + ' reviewers...'
                : 'Type name, email or expertise to search (' + pool.length + ' reviewers)...',
            allowClear: true,
            matcher: reviewerCustomMatcher,
            minimumResultsForSearch: 0
            // Plain Select2 look: each option shows its own one-line text (name, email,
            // tracks, expertise, "[Already in this track]"), built in createOption above.
        });

        // Auto-focus the search field upon opening
        $s2.on('select2:open', function () {
            setTimeout(function () {
                var search = document.querySelector('.select2-container--open .select2-search__field');
                if (search) {
                    search.focus();
                }
            }, 10);
        });
    });

    // "Expertise here" pickers: the same tag picker as the reviewer's own Research Areas,
    // offering the keywords already in use so one topic is not typed several ways.
    // Auto-assignment matches whole keywords (SubmissionRules::keywordOverlap), so picking
    // an existing one is what makes a match.
    var suggestedAreas = @json(array_values($suggestedAreas));
    var maxKeywords = {{ (int) $keywordsMax }};

    document.querySelectorAll('.js-expertise-tags').forEach(function (select) {
        suggestedAreas.forEach(function (area) {
            select.appendChild(new Option(area, area, false, false));
        });

        $(select).select2({
            tags: true,
            tokenSeparators: [',', ';'],
            placeholder: select.dataset.placeholder || 'Select existing or type to create new...',
            maximumSelectionLength: maxKeywords,
            width: '100%',
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '' || term.length < 2 || term.length > 100) {
                    return null;
                }
                return { id: term, text: term, newTag: true };
            },
            templateResult: function (data) {
                var $result = $('<span></span>').text(data.text);
                if (data.newTag) {
                    $result.append(' <span class="badge badge-info ml-1" style="font-size: 0.75rem;">Create new</span>');
                }
                return $result;
            }
        });
    });
});
</script>
@endpush
