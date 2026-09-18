<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use SoftDeletes, LogsActivity;

    public $table = 'prices';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'category',
        'early_bird_price',
        'regular_price',
        'currency',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    /**
     * The amount payable at a given stage. Defaults to whichever stage the
     * conference is in right now, which is what makes a stored price_id survive
     * the early-bird cut-off without being repointed.
     *
     * @param string|null $stage 'early_bird' or 'regular'
     * @return float
     */
    public function amountFor($stage = null)
    {
        $stage = $stage ?: PricingService::currentStage();

        return (float) ($stage === 'early_bird' ? $this->early_bird_price : $this->regular_price);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return strtoupper((string) $this->currency) === 'USD' ? 'US$ ' : '৳ ';
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency_symbol . number_format($this->amountFor());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'category', 'early_bird_price', 'regular_price', 'currency'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
