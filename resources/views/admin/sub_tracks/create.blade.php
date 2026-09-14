@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">Add Sub-Track</div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.sub-tracks.store') }}">
            @csrf

            <div class="form-group">
                <label class="required" for="track_id">Track*</label>
                <select class="form-control {{ $errors->has('track_id') ? 'is-invalid' : '' }}" name="track_id" id="track_id" required>
                    <option value="">Select Track</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ old('track_id') == $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                    @endforeach
                </select>
                @if($errors->has('track_id'))
                    <div class="invalid-feedback">{{ $errors->first('track_id') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="name">Sub-Track Name*</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                       name="name" id="name" value="{{ old('name') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <div class="form-group mb-0">
                <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Create Sub-Track</button>
                <a href="{{ route('admin.sub-tracks.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
