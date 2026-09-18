<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

class ConferenceMember extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'designation',
        'institution',
        'email',
        'profile_url',
        'is_active'
    ];

    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_conference_member')
                    ->withPivot('role', 'level', 'remarks', 'sort_order')
                    ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'designation', 'institution', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
