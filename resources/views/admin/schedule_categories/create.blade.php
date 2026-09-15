@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header font-weight-bold">
        Add New Schedule Session Category
    </div>

    <div class="card-body">
        <form action="{{ route('admin.schedule-categories.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Regular Session, Technical Session, Keynote Session" required>
                        @if($errors->has('name'))
                            <p class="help-block text-danger">{{ $errors->first('name') }}</p>
                        @endif
                        <small class="form-text text-muted">The primary label used across conference schedules.</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('color') ? 'has-error' : '' }}">
                        <label for="color">Badge Color</label>
                        <div class="input-group">
                            <input type="color" id="color_picker" class="form-control" style="width: 50px; height: 38px; padding: 2px; cursor: pointer;" value="{{ old('color', '#00396B') }}" onchange="document.getElementById('color').value = this.value">
                            <input type="text" id="color" name="color" class="form-control" value="{{ old('color', '#00396B') }}" placeholder="#00396B">
                        </div>
                        @if($errors->has('color'))
                            <p class="help-block text-danger">{{ $errors->first('color') }}</p>
                        @endif
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('sort_order') ? 'has-error' : '' }}">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" step="1">
                        @if($errors->has('sort_order'))
                            <p class="help-block text-danger">{{ $errors->first('sort_order') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief description of sessions belonging to this category">{{ old('description') }}</textarea>
                @if($errors->has('description'))
                    <p class="help-block text-danger">{{ $errors->first('description') }}</p>
                @endif
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label font-weight-bold" for="is_active">
                        Active (Display in dropdowns and schedules)
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save mr-1"></i> Save Session Category
                </button>
                <a class="btn btn-outline-secondary ml-2" href="{{ route('admin.schedule-categories.index') }}">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
