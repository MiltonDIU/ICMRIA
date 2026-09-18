<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'reviewers_per_paper', 'max_papers_per_reviewer'];

    public function subTracks()
    {
        return $this->hasMany(SubTrack::class);
    }

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public function assignments()
    {
        return $this->hasMany(TrackAssignment::class);
    }

    /** Chairs of the track as a whole, not of an individual sub-track. */
    public function chairs()
    {
        return $this->hasMany(TrackAssignment::class)->chairs()->wholeTrack();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'reviewers_per_paper', 'max_papers_per_reviewer'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
