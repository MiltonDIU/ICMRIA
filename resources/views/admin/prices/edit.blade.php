@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.price.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.prices.update", [$price->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('cruds.price.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($price) ? $price->name : '') }}" required>
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
                    <div class="form-group {{ $errors->has('early_bird_price') ? 'has-error' : '' }}">
                        <label for="early_bird_price">Early Bird Price*</label>
                        <input type="number" id="early_bird_price" name="early_bird_price" class="form-control" value="{{ old('early_bird_price', $price->early_bird_price ?? '') }}" step="0.01" required>
                        @if($errors->has('early_bird_price'))
                            <p class="help-block">{{ $errors->first('early_bird_price') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('regular_price') ? 'has-error' : '' }}">
                        <label for="regular_price">Regular / Late Price*</label>
                        <input type="number" id="regular_price" name="regular_price" class="form-control" value="{{ old('regular_price', $price->regular_price ?? '') }}" step="0.01" required>
                        @if($errors->has('regular_price'))
                            <p class="help-block">{{ $errors->first('regular_price') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('currency') ? 'has-error' : '' }}">
                        <label for="currency">Currency*</label>
                        <select id="currency" name="currency" class="form-control" required>
                            <option value="BDT" {{ (old('currency', $price->currency ?? '') == 'BDT') ? 'selected' : '' }}>BDT (৳ - Bangladeshi Taka)</option>
                            <option value="USD" {{ (old('currency', $price->currency ?? '') == 'USD') ? 'selected' : '' }}>USD ($ - US Dollar)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                <label for="category">Registration Category*</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Select --</option>
                    @foreach(\App\Services\PricingService::CATEGORIES as $category)
                        <option value="{{ $category }}" {{ (old('category', $price->category ?? '') == $category) ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
                <p class="helper-block">Fees are matched on this category. A price without one is never charged to anyone.</p>
                @if($errors->has('category'))
                    <p class="help-block">{{ $errors->first('category') }}</p>
                @endif
            </div>
            <div class="form-group {{ $errors->has('amenities') ? 'has-error' : '' }}">
                <label for="amenities">{{ trans('cruds.price.fields.amenities') }}
                    <span class="btn btn-info btn-xs select-all">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all">{{ trans('global.deselect_all') }}</span></label>
                <select name="amenities[]" id="amenities" class="form-control select2" multiple="multiple">
                    @foreach($amenities as $id => $amenities)
                        <option value="{{ $id }}" {{ (in_array($id, old('amenities', [])) || isset($price) && $price->amenities->contains($id)) ? 'selected' : '' }}>{{ $amenities }}</option>
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