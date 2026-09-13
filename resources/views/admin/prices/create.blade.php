@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.price.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.prices.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('cruds.price.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <p class="help-block">
                        {{ $errors->first('name') }}
                    </p>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.price.fields.name_helper') }}
                </p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                        <label for="price">{{ trans('cruds.price.fields.price') }}*</label>
                        <input type="number" id="price" name="price" class="form-control" value="{{ old('price', '') }}" step="0.01" required>
                        @if($errors->has('price'))
                            <p class="help-block">{{ $errors->first('price') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('currency') ? 'has-error' : '' }}">
                        <label for="currency">Currency*</label>
                        <select id="currency" name="currency" class="form-control" required>
                            <option value="BDT" {{ old('currency') == 'BDT' ? 'selected' : '' }}>BDT (৳ - Bangladeshi Taka)</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($ - US Dollar)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('registration_type') ? 'has-error' : '' }}">
                        <label for="registration_type">Registration Stage*</label>
                        <select id="registration_type" name="registration_type" class="form-control" required>
                            <option value="early_bird" {{ old('registration_type') == 'early_bird' ? 'selected' : '' }}>Early Bird</option>
                            <option value="regular" {{ old('registration_type') == 'regular' ? 'selected' : '' }}>Regular / Standard</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group {{ $errors->has('amenities') ? 'has-error' : '' }}">
                <label for="amenities">{{ trans('cruds.price.fields.amenities') }}
                    <span class="btn btn-info btn-xs select-all">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all">{{ trans('global.deselect_all') }}</span></label>
                <select name="amenities[]" id="amenities" class="form-control select2" multiple="multiple">
                    @foreach($amenities as $id => $amenities)
                        <option value="{{ $id }}" {{ in_array($id, old('amenities', [])) ? 'selected' : '' }}>{{ $amenities }}</option>
                    @endforeach
                </select>
                @if($errors->has('amenities'))
                    <p class="help-block">
                        {{ $errors->first('amenities') }}
                    </p>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.price.fields.amenities_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>
@endsection
