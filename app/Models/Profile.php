<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use LogsActivity;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'designation',
        'department',
        'institution',
        'orcid_id',
        'price_id',
        'country_id',
        'registration_id',
        'whatsapp_number',
        'is_author',
        'participation_mode',
        'pay_amount',
        'payment_status',
        'currency',
        'author_list_confirmed',
    ];

    protected $appends = [
        'phone',
    ];

    protected $casts = [
        'is_author' => 'boolean',
        'author_list_confirmed' => 'boolean',
    ];

    public function getPhoneAttribute()
    {
        return $this->whatsapp_number;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'designation', 'department', 'institution', 'orcid_id', 'whatsapp_number', 'participation_mode', 'country_id', 'is_student', 'pay_amount', 'payment_status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
