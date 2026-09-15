@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header font-weight-bold">
        Edit Message Category
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.conference-message-categories.update', [$conferenceMessageCategory->id]) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="required" for="name">Category Name</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $conferenceMessageCategory->name) }}" required>
                <small class="form-text text-muted">e.g. Chief Patron &amp; Patron, Organizing Chair, General Chair, TPC Chair, Chief Guest</small>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $conferenceMessageCategory->sort_order) }}">
                @if($errors->has('sort_order'))
                    <div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>
                @endif
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $conferenceMessageCategory->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active (Visible in dropdown &amp; on website)
                    </label>
                </div>
            </div>

            <div class="form-group mt-4">
                <button class="btn btn-info" type="submit">
                    Update Category
                </button>
                <a class="btn btn-secondary ml-2" href="{{ route('admin.conference-message-categories.index') }}">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection