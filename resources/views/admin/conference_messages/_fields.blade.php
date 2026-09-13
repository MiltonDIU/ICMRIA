@php $cm = $conferenceMessage ?? null; @endphp

<div class="form-group">
    <label class="required" for="conference_message_category_id">Message Category</label>
    <div class="input-group">
        <select class="form-control select2 {{ $errors->has('conference_message_category_id') ? 'is-invalid' : '' }}" name="conference_message_category_id" id="conference_message_category_id" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('conference_message_category_id', $cm->conference_message_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <div class="input-group-append">
            <a href="{{ route('admin.conference-message-categories.create') }}" class="btn btn-outline-primary" target="_blank" title="Create New Category">
                <i class="fas fa-plus mr-1"></i> Add New Category
            </a>
        </div>
    </div>
    @if($errors->has('conference_message_category_id'))
        <div class="invalid-feedback d-block">{{ $errors->first('conference_message_category_id') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="variant">Variant / Sub-title</label>
    <input class="form-control {{ $errors->has('variant') ? 'is-invalid' : '' }}" type="text" name="variant" id="variant"
           value="{{ old('variant', $cm->variant ?? '') }}" placeholder="Optional – e.g. Chief Patron, Patron, Convener, Opening, Closing">
    <small class="form-text text-muted">Displays as a badge on the speaker/leader message card.</small>
    @if($errors->has('variant'))
        <div class="invalid-feedback">{{ $errors->first('variant') }}</div>
    @endif
</div>

<div class="form-group">
    <label class="required" for="person_name">Person Name</label>
    <input class="form-control {{ $errors->has('person_name') ? 'is-invalid' : '' }}" type="text" name="person_name" id="person_name"
           value="{{ old('person_name', $cm->person_name ?? '') }}" required>
    @if($errors->has('person_name'))
        <div class="invalid-feedback">{{ $errors->first('person_name') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="designation">Designation / Role</label>
    <input class="form-control {{ $errors->has('designation') ? 'is-invalid' : '' }}" type="text" name="designation" id="designation"
           value="{{ old('designation', $cm->designation ?? '') }}">
    @if($errors->has('designation'))
        <div class="invalid-feedback">{{ $errors->first('designation') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="affiliation">Affiliation</label>
    <input class="form-control {{ $errors->has('affiliation') ? 'is-invalid' : '' }}" type="text" name="affiliation" id="affiliation"
           value="{{ old('affiliation', $cm->affiliation ?? '') }}">
    @if($errors->has('affiliation'))
        <div class="invalid-feedback">{{ $errors->first('affiliation') }}</div>
    @endif
</div>

<div class="form-group">
    <label class="required" for="message">Message</label>
    <textarea class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}" name="message" id="message" rows="8" required>{{ old('message', $cm->message ?? '') }}</textarea>
    <small class="form-text text-muted">Plain text. Line breaks are preserved on the website.</small>
    @if($errors->has('message'))
        <div class="invalid-feedback">{{ $errors->first('message') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="profile_url">Profile URL</label>
    <input class="form-control {{ $errors->has('profile_url') ? 'is-invalid' : '' }}" type="url" name="profile_url" id="profile_url"
           value="{{ old('profile_url', $cm->profile_url ?? '') }}">
    @if($errors->has('profile_url'))
        <div class="invalid-feedback">{{ $errors->first('profile_url') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="photo">Photo</label>
    <div class="needsclick dropzone {{ $errors->has('photo') ? 'is-invalid' : '' }}" id="photo-dropzone"></div>
    @if($errors->has('photo'))
        <div class="invalid-feedback">{{ $errors->first('photo') }}</div>
    @endif
</div>

<div class="form-group">
    <label for="sort_order">Sort Order</label>
    <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" name="sort_order" id="sort_order"
           value="{{ old('sort_order', $cm->sort_order ?? 0) }}">
    @if($errors->has('sort_order'))
        <div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>
    @endif
</div>

<div class="form-group">
    <label>
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $cm->is_published ?? true) ? 'checked' : '' }}>
        Published (show on website)
    </label>
</div>