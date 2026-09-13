<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use SoftDeletes;

    public $table = 'prices';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'price',
        'registration_type',
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

    public function getFormattedPriceAttribute(): string
    {
        $symbol = strtoupper((string)$this->currency) === 'USD' ? 'US$ ' : 'BDT ';
        return $symbol . number_format($this->price);
    }
}