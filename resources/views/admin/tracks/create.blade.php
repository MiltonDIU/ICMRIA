@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">Add Track</div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.tracks.store') }}">
            @csrf

            <div class="form-group">
                <label class="required" for="name">Track Name*</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                       name="name" id="name" value="{{ old('name') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
                <small class="form-text text-muted">
                    Chairs and review settings are assigned after the track is created.
                </small>
            </div>

            <div class="form-group mb-0">
                <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Create Track</button>
                <a href="{{ route('admin.tracks.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
