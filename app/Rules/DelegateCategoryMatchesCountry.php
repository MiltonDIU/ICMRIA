<?php

namespace App\Rules;

use App\Models\Country;
use App\Models\Price;
use App\Services\PricingService;
use Illuminate\Contracts\Validation\Rule;

/**
 * Stops someone picking a fee tier their country does not qualify for, e.g. a
 * delegate from Germany selecting the BDT student rate.
 */
class DelegateCategoryMatchesCountry implements Rule
{
    protected $countryId;
    protected $countryName;
    protected $allowed = [];

    public function __construct($countryId)
    {
        $this->countryId = $countryId;
    }

    public function passes($attribute, $value)
    {
        $this->countryName = Country::where('id', $this->countryId)->value('name');
        $category = Price::where('id', $value)->value('category');

        // A missing country or an unknown price is reported by their own rules;
        // don't pile a second, more confusing message on top.
        if (!$this->countryName || !$category) {
            return true;
        }

        $this->allowed = PricingService::allowedCategoriesFor($this->countryName);

        return in_array($category, $this->allowed, true);
    }

    public function message()
    {
        $names = Price::whereIn('category', $this->allowed)->pluck('name')->implode(', ');

        return "Delegates from {$this->countryName} must register under: {$names}.";
    }
}
