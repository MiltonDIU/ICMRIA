@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        Edit Submission
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('papers.update', [$paper->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf

            <h4 class="mb-4 text-primary"><strong>Abstract Submission Details</strong></h4>

            <div class="form-group">
                <label class="required" for="paper_title">Paper Title*</label>
                <input class="form-control {{ $errors->has('paper_title') ? 'is-invalid' : '' }}" type="text" name="paper_title" id="paper_title" value="{{ old('paper_title', $paper->title) }}" required>
                @if($errors->has('paper_title'))
                    <div class="invalid-feedback">
                        {{ $errors->first('paper_title') }}
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="abstract_text">Abstract ({{ \App\Services\SubmissionRules::abstractMinWords() }}-{{ \App\Services\SubmissionRules::abstractMaxWords() }} words)*</label>
                <textarea class="form-control {{ $errors->has('abstract_text') ? 'is-invalid' : '' }}" name="abstract_text" id="abstract_text" rows="6" oninput="countWords()" required>{{ old('abstract_text', $paper->abstract) }}</textarea>
                <div id="word_count_display" class="small mt-1 text-muted">Words: <span id="word_count">0</span> / {{ \App\Services\SubmissionRules::abstractMaxWords() }}</div>
                @if($errors->has('abstract_text'))
                    <div class="invalid-feedback">
                        {{ $errors->first('abstract_text') }}
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        @include('partials.keyword-tags', ['labelClass' => 'required', 'value' => old('keywords', $paper->keywords)])
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="required" for="track_id">Conference Track*</label>
                        <select class="form-control {{ $errors->has('track_id') ? 'is-invalid' : '' }}" name="track_id" id="track_id" required onchange="updateSubTracks()">
                            <option value="">Select Main Track</option>
                            @foreach($tracks as $track)
                                <option value="{{ $track->id }}" {{ (old('track_id', $paper->track_id) == $track->id) ? 'selected' : '' }}>{{ $track->name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('track_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('track_id') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="required" for="sub_track_id">Sub-Track*</label>
                        <select class="form-control {{ $errors->has('sub_track_id') ? 'is-invalid' : '' }}" name="sub_track_id" id="sub_track_id" required>
                            <option value="">Select Sub-Track</option>
                        </select>
                        @if($errors->has('sub_track_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('sub_track_id') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check {{ $errors->has('is_corresponding_author') ? 'is-invalid' : '' }}">
                    <input type="hidden" name="is_corresponding_author" value="0">
                    <input class="form-check-input" type="checkbox" name="is_corresponding_author" id="is_corresponding_author" value="1" {{ (old('is_corresponding_author', $paper->is_corresponding_author) == 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_corresponding_author">I am the corresponding author</label>
                </div>
                @if($errors->has('is_corresponding_author'))
                    <div class="invalid-feedback">
                        {{ $errors->first('is_corresponding_author') }}
                    </div>
                @endif
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><strong>Co-Authors (If any)</strong></h5>
            </div>

            <div id="co_authors_container">
                @php
                    $coAuthorIndex = 0;
                    $primaryAuthorFound = false;
                    $submitterEmail = $paper->user->email ?? '';
                @endphp

                {{-- Loop through all existing DB authors --}}
                @foreach($paper->authors as $index => $author)
                    @php
                        $isPrimary = ($author->email === $submitterEmail);
                        if ($isPrimary) $primaryAuthorFound = true;
                    @endphp
                    <div class="co-author-entry border p-3 mb-3 rounded position-relative bg-white shadow-sm">
                        @if(!$isPrimary)
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;" onclick="removeCoAuthor(this)"><i class="fa fa-times"></i></button>
                        @endif
                        <input type="hidden" name="co_authors[{{ $coAuthorIndex }}][id]" value="{{ $author->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-3 pr-5">
                            <h6 class="mb-0 font-weight-bold text-secondary text-uppercase" style="font-size: 0.8rem;">Co-Author Entry</h6>
                            <div class="d-flex align-items-center">
                                <input type="radio" id="presenting_{{ $coAuthorIndex }}" name="presenting_author_index" value="{{ $coAuthorIndex }}" class="mr-2" style="cursor: pointer; transform: scale(1.2);" {{ $author->is_presenting_author ? 'checked' : '' }} required>
                                <label for="presenting_{{ $coAuthorIndex }}" class="mb-0 text-secondary font-weight-bold" style="font-size: 0.85rem; cursor: pointer;">Presenting Author</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][name]" class="form-control form-control-sm" placeholder="Full Name*" value="{{ $author->name }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="email" name="co_authors[{{ $coAuthorIndex }}][email]" class="form-control form-control-sm" placeholder="Email*" value="{{ $author->email }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][designation]" class="form-control form-control-sm" placeholder="Designation*" value="{{ $author->designation }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][department]" class="form-control form-control-sm" placeholder="Department*" value="{{ $author->department }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][institution]" class="form-control form-control-sm" placeholder="Institution*" value="{{ $author->institution }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][country_id]" class="form-control form-control-sm delegate-country-select" required>
                                    <option value="">Country*</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $author->country_id == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][is_student]" class="form-control form-control-sm">
                                    <option value="" {{ is_null($author->is_student) ? 'selected' : '' }}>Student Status</option>
                                    <option value="1" {{ ($author->is_student === true || $author->is_student === 1 || $author->is_student === '1') ? 'selected' : '' }}>Student</option>
                                    <option value="0" {{ ($author->is_student === false || $author->is_student === 0 || $author->is_student === '0') ? 'selected' : '' }}>Regular/Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][price_id]" class="form-control form-control-sm delegate-category-select" required>
                                    <option value="">Delegate Category*</option>
                                    @foreach($prices as $priceOption)
                                        <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}" {{ $author->price_id == $priceOption->id ? 'selected' : '' }}>{{ $priceOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @php $coAuthorIndex++; @endphp
                @endforeach

                {{-- Fallback: If DB is missing the primary author, ensure they are still rendered --}}
                @if(!$primaryAuthorFound)
                    <div class="co-author-entry border p-3 mb-3 rounded position-relative bg-white shadow-sm">
                        <input type="hidden" name="co_authors[{{ $coAuthorIndex }}][id]" value="">
                        <div class="d-flex justify-content-between align-items-center mb-3 pr-5">
                            <h6 class="mb-0 font-weight-bold text-secondary text-uppercase" style="font-size: 0.8rem;">Co-Author Entry</h6>
                            <div class="d-flex align-items-center">
                                <input type="radio" id="presenting_{{ $coAuthorIndex }}" name="presenting_author_index" value="{{ $coAuthorIndex }}" class="mr-2" style="cursor: pointer; transform: scale(1.2);" checked required>
                                <label for="presenting_{{ $coAuthorIndex }}" class="mb-0 text-secondary font-weight-bold" style="font-size: 0.85rem; cursor: pointer;">Presenting Author</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][name]" class="form-control form-control-sm" placeholder="Full Name*" value="{{ trim(($paper->user->profile?->first_name ?? '') . ' ' . ($paper->user->profile?->last_name ?? '')) ?: ($paper->user->name ?? '') }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="email" name="co_authors[{{ $coAuthorIndex }}][email]" class="form-control form-control-sm" placeholder="Email*" value="{{ $submitterEmail }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][designation]" class="form-control form-control-sm" placeholder="Designation*" value="{{ $paper->user->profile?->designation ?? '' }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][department]" class="form-control form-control-sm" placeholder="Department*" value="{{ $paper->user->profile?->department ?? '' }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" name="co_authors[{{ $coAuthorIndex }}][institution]" class="form-control form-control-sm" placeholder="Institution*" value="{{ $paper->user->profile?->institution ?? '' }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][country_id]" class="form-control form-control-sm delegate-country-select" required>
                                    <option value="">Country*</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ ($paper->user->profile?->country_id ?? '') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][is_student]" class="form-control form-control-sm">
                                    <option value="" selected>Student Status</option>
                                    <option value="1">Student</option>
                                    <option value="0">Regular/Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select name="co_authors[{{ $coAuthorIndex }}][price_id]" class="form-control form-control-sm delegate-category-select" required>
                                    <option value="">Delegate Category*</option>
                                    @foreach($prices as $priceOption)
                                        <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}" {{ ($paper->user->profile?->price_id ?? '') == $priceOption->id ? 'selected' : '' }}>{{ $priceOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @php $coAuthorIndex++; @endphp
                @endif
            </div>
            <button type="button" class="btn btn-info btn-sm mb-4" onclick="addCoAuthor()"><i class="fa fa-plus"></i> Add Co-Author</button>

            @include('partials.fee-summary')

            <div class="form-group mb-0 mt-4">
                <button class="btn btn-success" type="submit">
                    <i class="fa fa-save"></i> Update Abstract
                </button>
                <a href="{{ route('papers.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

<template id="co_author_template">
    <div class="co-author-entry border p-3 mb-3 rounded position-relative bg-white shadow-sm">
        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;" onclick="removeCoAuthor(this)"><i class="fa fa-times"></i></button>
        <input type="hidden" name="co_authors[{index}][id]" value="">
        <div class="d-flex justify-content-between align-items-center mb-3 pr-5">
            <h6 class="mb-0 font-weight-bold text-secondary text-uppercase" style="font-size: 0.8rem;">Co-Author Entry</h6>
            <div class="d-flex align-items-center">
                <input type="radio" id="presenting_{index}" name="presenting_author_index" value="{index}" class="mr-2" style="cursor: pointer; transform: scale(1.2);" required>
                <label for="presenting_{index}" class="mb-0 text-secondary font-weight-bold" style="font-size: 0.85rem; cursor: pointer;">Presenting Author</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <input type="text" name="co_authors[{index}][name]" class="form-control form-control-sm" placeholder="Full Name*" required>
            </div>
            <div class="col-md-6 mb-2">
                <input type="email" name="co_authors[{index}][email]" class="form-control form-control-sm" placeholder="Email*" required>
            </div>
            <div class="col-md-2 mb-2">
                <input type="text" name="co_authors[{index}][designation]" class="form-control form-control-sm" placeholder="Designation*" required>
            </div>
            <div class="col-md-2 mb-2">
                <input type="text" name="co_authors[{index}][department]" class="form-control form-control-sm" placeholder="Department*" required>
            </div>
            <div class="col-md-3 mb-2">
                <input type="text" name="co_authors[{index}][institution]" class="form-control form-control-sm" placeholder="Institution*" required>
            </div>
            <div class="col-md-3 mb-2">
                <select name="co_authors[{index}][country_id]" class="form-control form-control-sm delegate-country-select" required>
                    <option value="">Country*</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="co_authors[{index}][is_student]" class="form-control form-control-sm">
                    <option value="" selected>Student Status</option>
                    <option value="1">Student</option>
                    <option value="0">Regular/Other</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <select name="co_authors[{index}][price_id]" class="form-control form-control-sm delegate-category-select" required>
                    <option value="">Delegate Category*</option>
                    @foreach($prices as $priceOption)
                        <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}">{{ $priceOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</template>

@endsection

@push('script')
<script>
    // Resume Javascript index right after Blade index
    let coAuthorIndex = {{ isset($coAuthorIndex) ? $coAuthorIndex : 0 }};

    const tracksData = @json($tracks->values());

    // Delegate category depends on country. Countries absent from this map are
    // international. The server enforces the same rule via
    // App\Rules\DelegateCategoryMatchesCountry.
    const allowedCategoriesByCountry = @json($countryCategories);
    const defaultAllowedCategories = ['international'];

    function syncCategoryOptions(countrySelect, categorySelect) {
        if (!countrySelect || !categorySelect) return;

        const allowed = allowedCategoriesByCountry[countrySelect.value] || defaultAllowedCategories;
        let selectedStillAllowed = false;

        Array.from(categorySelect.options).forEach(option => {
            if (!option.value) return;
            const permitted = allowed.includes(option.dataset.category);
            option.hidden = !permitted;
            option.disabled = !permitted;
            if (permitted && option.selected) selectedStillAllowed = true;
        });

        if (!selectedStillAllowed) {
            categorySelect.value = allowed.length === 1
                ? (Array.from(categorySelect.options).find(o => o.dataset.category === allowed[0])?.value || '')
                : '';
        }
    }

    function syncAllCategoryOptions() {
        document.querySelectorAll('.co-author-entry').forEach(row => {
            syncCategoryOptions(
                row.querySelector('.delegate-country-select'),
                row.querySelector('.delegate-category-select')
            );
        });
    }

    document.addEventListener('change', event => {
        if (event.target.classList.contains('delegate-country-select')) {
            const row = event.target.closest('.co-author-entry');
            if (row) {
                syncCategoryOptions(event.target, row.querySelector('.delegate-category-select'));
            }
        }
    });

    document.addEventListener('DOMContentLoaded', syncAllCategoryOptions);

    function updateSubTracks() {
        const trackSelect = document.getElementById('track_id');
        const subTrackSelect = document.getElementById('sub_track_id');
        const selectedTrackId = trackSelect.value;
        const oldSubTrackId = "{{ old('sub_track_id', $paper->sub_track_id) }}";

        // Clear sub-track options
        subTrackSelect.innerHTML = '<option value="">Select Sub-Track</option>';

        if (selectedTrackId) {
            const selectedTrack = tracksData.find(t => t.id == selectedTrackId);
            if (selectedTrack && selectedTrack.sub_tracks) {
                selectedTrack.sub_tracks.forEach(subTrack => {
                    const option = document.createElement('option');
                    option.value = subTrack.id;
                    option.textContent = subTrack.name;
                    if (subTrack.id == oldSubTrackId) {
                        option.selected = true;
                    }
                    subTrackSelect.appendChild(option);
                });
            }
        }
    }

    function countWords() {
        const text = document.getElementById('abstract_text').value.trim();
        const display = document.getElementById('word_count_display');
        const counter = document.getElementById('word_count');

        let count = 0;
        if (text.length > 0) {
            count = text.split(/\s+/).length;
        }

        counter.innerText = count;

        // Empty is flagged at once because the abstract is mandatory. Being short of
        // the minimum only means it is unfinished, so that stays green.
        const ok = count > 0 && count <= {{ \App\Services\SubmissionRules::abstractMaxWords() }};
        display.classList.remove('text-muted');
        display.classList.toggle('text-success', ok);
        display.classList.toggle('text-danger', !ok);
        display.classList.toggle('font-weight-bold', !ok);
    }


    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSubTracks();
        countWords();
    });

    function addCoAuthor(data = null, isPrimary = false) {
        const container = document.getElementById('co_authors_container');
        const template = document.getElementById('co_author_template').innerHTML;
        const html = template.replace(/{index}/g, coAuthorIndex);

        const div = document.createElement('div');
        div.innerHTML = html;

        if (isPrimary) {
            const btn = div.querySelector('.btn-danger');
            if(btn) btn.style.display = 'none';
        }

        if (data && data.name) {
            div.querySelector(`input[name="co_authors[${coAuthorIndex}][name]"]`).value = data.name || '';
            div.querySelector(`input[name="co_authors[${coAuthorIndex}][email]"]`).value = data.email || '';
            div.querySelector(`input[name="co_authors[${coAuthorIndex}][designation]"]`).value = data.designation || '';
            div.querySelector(`input[name="co_authors[${coAuthorIndex}][department]"]`).value = data.department || '';
            div.querySelector(`input[name="co_authors[${coAuthorIndex}][institution]"]`).value = data.institution || '';

            if (data.country_id) {
                const countrySelect = div.querySelector(`select[name="co_authors[${coAuthorIndex}][country_id]"]`);
                const option = Array.from(countrySelect.options).find(opt => opt.value == data.country_id);
                if (option) option.selected = true;
            }

            if (data.is_student !== undefined && data.is_student !== null) {
                const studentSelect = div.querySelector(`select[name="co_authors[${coAuthorIndex}][is_student]"]`);
                if (studentSelect) {
                    studentSelect.value = (data.is_student === true || data.is_student == 1) ? '1' : '0';
                }
            }
        }

        const entry = div.firstElementChild;
        container.appendChild(entry);

        const categorySelect = entry.querySelector('.delegate-category-select');
        syncCategoryOptions(entry.querySelector('.delegate-country-select'), categorySelect);
        if (data && data.price_id && categorySelect) {
            categorySelect.value = data.price_id;
        }

        coAuthorIndex++;
    }

    function removeCoAuthor(btn) {
        btn.closest('.co-author-entry').remove();
    }
</script>
@endpush
