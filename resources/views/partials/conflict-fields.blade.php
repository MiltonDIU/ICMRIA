{{--
    Conflict-of-interest fields for a new submission or editing an existing submission,
    used on registration form, paper submission form, and paper edit form.
    Lists the chairs and reviewers of the track chosen in #track_id.
--}}
@php
    $conflictCandidates = $conflictCandidates ?? \App\Services\ConflictCandidates::byTrack();
    $oldConflictIds = array_map('intval', (array) old('conflict_user_ids', $existingConflictUserIds ?? []));
    $defaultInstitution = old('conflict_institution', $existingConflictInstitution ?? '');
    $defaultNote = old('conflict_note', $existingConflictNote ?? '');
@endphp

<div class="conflict-fields border rounded p-3 mb-4" style="background: #F8FAFC;">
    <h5 class="mb-1"><strong>Conflicts of Interest</strong> <small class="text-muted">(optional)</small></h5>
    <p class="small text-muted mb-3">
        Name any chair or reviewer of your chosen track with whom you have a conflict of interest &mdash; for example
        a supervisor, a close collaborator, or a colleague at your own institution. They will not be asked to review
        or decide on your paper. You can add more later from your paper page.
    </p>

    <div class="form-group mb-2">
        <label for="conflict_user_ids" class="small font-weight-bold">Chairs and reviewers of the selected track</label>
        <select id="conflict_user_ids" name="conflict_user_ids[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="Search and select chairs or reviewers..." data-skip-required>
        </select>
        <small class="form-text text-muted" id="conflict_user_hint">Choose a track above to see its chairs and reviewers.</small>
        @error('conflict_user_ids') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
    </div>

    <div class="form-row">
        <div class="form-group col-md-6 mb-2">
            <label for="conflict_institution" class="small font-weight-bold">An institution you have a conflict with</label>
            <input type="text" id="conflict_institution" name="conflict_institution" class="form-control" maxlength="255"
                   value="{{ $defaultInstitution }}" placeholder="e.g. your own university" data-skip-required>
        </div>
        <div class="form-group col-md-6 mb-2">
            <label for="conflict_note" class="small font-weight-bold">Reason</label>
            <input type="text" id="conflict_note" name="conflict_note" class="form-control" maxlength="255"
                   value="{{ $defaultNote }}" placeholder="e.g. former doctoral supervisor" data-skip-required>
        </div>
    </div>
</div>

@once
    @push('style')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--multiple {
                border-color: #ced4da;
                min-height: 38px;
            }
            .select2-container--default.select2-container--focus .select2-selection--multiple {
                border-color: #80bdff;
                box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
            }
        </style>
    @endpush
    @push('script')
        <script>
            (function () {
                const candidates = @json($conflictCandidates);
                let preselected = @json($oldConflictIds);
                let isInitialLoad = true;

                function getSelectedUserIds(select) {
                    if (isInitialLoad && preselected.length > 0) {
                        return preselected.map(v => parseInt(v, 10));
                    }
                    if (window.jQuery && jQuery.fn.select2 && jQuery(select).data('select2')) {
                        const val = jQuery(select).val();
                        return (Array.isArray(val) ? val : []).map(v => parseInt(v, 10));
                    }
                    return Array.from(select.selectedOptions).map(o => parseInt(o.value, 10));
                }

                function refreshConflictCandidates() {
                    const track = document.getElementById('track_id');
                    const select = document.getElementById('conflict_user_ids');
                    const hint = document.getElementById('conflict_user_hint');
                    if (!track || !select) {
                        return;
                    }

                    const trackVal = track.value;
                    const people = candidates[trackVal] || [];
                    const selectedSet = new Set(getSelectedUserIds(select));

                    select.innerHTML = '';
                    people.forEach(function (person) {
                        const isSelected = selectedSet.has(parseInt(person.id, 10));
                        select.add(new Option(person.label, person.id, isSelected, isSelected));
                    });

                    if (hint) {
                        hint.textContent = !trackVal
                            ? 'Choose a track above to see its chairs and reviewers.'
                            : (people.length
                                ? 'Search and select any chairs or reviewers you have a conflict of interest with.'
                                : 'This track has no chairs or reviewers listed yet.');
                    }

                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery(select).select2({
                            placeholder: people.length ? 'Search and select chairs or reviewers...' : 'No chairs or reviewers in this track',
                            allowClear: true,
                            width: '100%'
                        });
                        jQuery(select).trigger('change');
                    }

                    isInitialLoad = false;
                }

                function initConflictSelect() {
                    const track = document.getElementById('track_id');
                    const select = document.getElementById('conflict_user_ids');

                    if (select && window.jQuery && jQuery.fn.select2) {
                        jQuery(select).select2({
                            placeholder: 'Search and select chairs or reviewers...',
                            allowClear: true,
                            width: '100%'
                        });
                    }

                    if (track) {
                        track.addEventListener('change', refreshConflictCandidates);
                        if (window.jQuery) {
                            jQuery(track).on('change', refreshConflictCandidates);
                        }
                    }

                    refreshConflictCandidates();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initConflictSelect);
                } else {
                    initConflictSelect();
                }
            })();
        </script>
    @endpush
@endonce
