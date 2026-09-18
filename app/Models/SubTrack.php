<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTrack extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['track_id', 'name'];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public function assignments()
    {
        return $this->hasMany(TrackAssignment::class);
    }

    public function chairs()
    {
        return $this->hasMany(TrackAssignment::class)->chairs();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['track_id', 'name'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
