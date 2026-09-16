{{--
    Conflict-of-interest fields for a new submission, used on both the registration form
    and the submission form. Lists the chairs and reviewers of the track chosen in
    #track_id, so the author can name anyone they have a conflict with before the paper
    is assigned. Every field is optional and marked data-skip-required, since the
    registration form marks the fields of its abstract section required in a sweep.
--}}
@php
    $conflictCandidates = $conflictCandidates ?? \App\Services\ConflictCandidates::byTrack();
    $oldConflictIds = array_map('intval', (array) old('conflict_user_ids', []));
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
        <select id="conflict_user_ids" name="conflict_user_ids[]" class="form-control" multiple size="6" data-skip-required></select>
        <small class="form-text text-muted" id="conflict_user_hint">Choose a track above to see its chairs and reviewers.</small>
        @error('conflict_user_ids') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror
    </div>

    <div class="form-row">
        <div class="form-group col-md-6 mb-2">
            <label for="conflict_institution" class="small font-weight-bold">An institution you have a conflict with</label>
            <input type="text" id="conflict_institution" name="conflict_institution" class="form-control" maxlength="255"
                   value="{{ old('conflict_institution') }}" placeholder="e.g. your own university" data-skip-required>
        </div>
        <div class="form-group col-md-6 mb-2">
            <label for="conflict_note" class="small font-weight-bold">Reason</label>
            <input type="text" id="conflict_note" name="conflict_note" class="form-control" maxlength="255"
                   value="{{ old('conflict_note') }}" placeholder="e.g. former doctoral supervisor" data-skip-required>
        </div>
    </div>
</div>

@once
    @push('script')
        <script>
            (function () {
                const candidates = @json($conflictCandidates);
                const preselected = @json($oldConflictIds);

                function refreshConflictCandidates() {
                    const track = document.getElementById('track_id');
                    const select = document.getElementById('conflict_user_ids');
                    const hint = document.getElementById('conflict_user_hint');
                    if (!track || !select) {
                        return;
                    }

                    const kept = new Set(Array.from(select.selectedOptions).map(o => parseInt(o.value, 10)).concat(preselected));
                    const people = candidates[track.value] || [];

                    select.innerHTML = '';
                    people.forEach(function (person) {
                        select.add(new Option(person.label, person.id, false, kept.has(person.id)));
                    });

                    if (hint) {
                        hint.textContent = !track.value
                            ? 'Choose a track above to see its chairs and reviewers.'
                            : (people.length
                                ? 'Hold Ctrl (Cmd on a Mac) to select more than one person.'
                                : 'This track has no chairs or reviewers listed yet.');
                    }
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const track = document.getElementById('track_id');
                    if (track) {
                        track.addEventListener('change', refreshConflictCandidates);
                    }
                    refreshConflictCandidates();
                });
            })();
        </script>
    @endpush
@endonce
