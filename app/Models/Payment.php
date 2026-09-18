<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'user_id',
        'amount',
        'currency_code',
        'cus_name',
        'cus_email',
        'cus_address',
        'cus_city',
        'cus_state',
        'cus_postcode',
        'cus_country',
        'cus_phone',
        'response_type',
        'service_type',
        'reff_id',
        'status',
        'getaway',
        'message'
    ];
    
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'amount', 'currency_code', 'status', 'reff_id', 'getaway', 'response_type', 'service_type'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
