<?php

namespace App\Http\Requests;

use App\Models\Price;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePriceRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('price_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'        => [
                'required',
            ],
            'early_bird_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'regular_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'currency'    => [
                'nullable',
                'string',
            ],
            'category'    => [
                'required',
                'in:' . implode(',', \App\Services\PricingService::CATEGORIES),
            ],
            'amenities.*' => [
                'integer',
            ],
            'amenities'   => [
                'array',
            ],
        ];
    }
}
