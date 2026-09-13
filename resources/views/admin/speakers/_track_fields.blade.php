@php $sp = $speaker ?? null; @endphp

<div class="form-group {{ $errors->has('track_id') ? 'has-error' : '' }}">
    <label for="track_id">Track (for track-wise grouping of Keynote / Invited speakers)</label>
    <select name="track_id" class="form-control">
        <option value=""> ========= Select One ========== </option>
        @foreach($tracks as $track)
            <option value="{{ $track->id }}" {{ (string) old('track_id', $sp->track_id ?? '') === (string) $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group {{ $errors->has('focus_area') ? 'has-error' : '' }}">
    <label for="focus_area">Focus Area / Session Title</label>
    <input type="text" id="focus_area" name="focus_area" class="form-control" value="{{ old('focus_area', $sp->focus_area ?? '') }}">
    <p class="helper-block">Shown under the speaker when no track is selected — e.g. "AI, Data Science &amp; Smart Systems".</p>
</div>

<div class="form-group {{ $errors->has('affiliation') ? 'has-error' : '' }}">
    <label for="affiliation">Affiliation</label>
    <input type="text" id="affiliation" name="affiliation" class="form-control" value="{{ old('affiliation', $sp->affiliation ?? '') }}">
</div>

<div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
    <label for="country">Country</label>
    <input type="text" id="country" name="country" class="form-control" value="{{ old('country', $sp->country ?? '') }}">
</div>
