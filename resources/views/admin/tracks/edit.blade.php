@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">Edit Track</div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.tracks.update', $track->id) }}">
            @method('PUT')
            @csrf

            <div class="form-group">
                <label class="required" for="name">Track Name*</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                       name="name" id="name" value="{{ old('name', $track->name) }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reviewers_per_paper">Reviewers per Paper</label>
                        <input class="form-control" type="number" min="1" max="10"
                               name="reviewers_per_paper" id="reviewers_per_paper"
                               value="{{ old('reviewers_per_paper', $track->reviewers_per_paper) }}"
                               placeholder="Conference default ({{ \App\Models\Setting::where('key', 'reviewers_per_paper')->value('value') ?? 3 }})">
                        <small class="form-text text-muted">Leave blank to use the conference-wide figure.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_papers_per_reviewer">Maximum Papers per Reviewer</label>
                        <input class="form-control" type="number" min="1" max="100"
                               name="max_papers_per_reviewer" id="max_papers_per_reviewer"
                               value="{{ old('max_papers_per_reviewer', $track->max_papers_per_reviewer) }}"
                               placeholder="Conference default ({{ \App\Models\Setting::where('key', 'max_papers_per_reviewer')->value('value') ?? 10 }})">
                        <small class="form-text text-muted">Leave blank to use the conference-wide figure.</small>
                    </div>
                </div>
            </div>

            <hr>

            <h5 class="mb-1"><strong>Overall Track Chair</strong></h5>
            <p class="text-muted small">Sees and decides on every paper in every sub-track below. Anyone chosen here receives the Track Chair role automatically.</p>

            @if($chairCandidates->isEmpty())
                <div class="alert alert-warning">
                    There are no staff accounts yet. Add the teacher under
                    <a href="{{ route('admin.users.index') }}">Users</a> first, then come back here to place them.
                </div>
            @else
                <div class="form-group">
                    <select name="chairs[]" class="form-control select2" multiple>
                        @foreach($chairCandidates as $candidate)
                            <option value="{{ $candidate->id }}" {{ in_array($candidate->id, old('chairs', $trackChairIds)) ? 'selected' : '' }}>
                                {{ $candidate->name }} &mdash; {{ $candidate->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <h5 class="mb-1"><strong>Sub-Track Chairs</strong></h5>
                <p class="text-muted small">Each one sees and decides only on papers in their own sub-track. Anyone chosen here receives the Sub-Track Chair role automatically.</p>

                @forelse($track->subTracks as $subTrack)
                    @php $selected = old("sub_track_chairs.{$subTrack->id}", $subTrack->chairs->pluck('user_id')->all()); @endphp
                    <div class="form-group">
                        <label for="sub_track_{{ $subTrack->id }}">{{ $subTrack->name }}</label>
                        <select name="sub_track_chairs[{{ $subTrack->id }}][]" id="sub_track_{{ $subTrack->id }}"
                                class="form-control select2" multiple>
                            @foreach($chairCandidates as $candidate)
                                <option value="{{ $candidate->id }}" {{ in_array($candidate->id, $selected) ? 'selected' : '' }}>
                                    {{ $candidate->name }} &mdash; {{ $candidate->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @empty
                    <p class="text-muted">
                        This track has no sub-tracks yet.
                        <a href="{{ route('admin.sub-tracks.create') }}">Add one</a>.
                    </p>
                @endforelse
            @endif

            <div class="form-group mb-0 mt-4">
                <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Save Track</button>
                <a href="{{ route('admin.tracks.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
