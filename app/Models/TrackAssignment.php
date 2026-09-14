<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Places a user in a track or sub-track. A null sub_track_id means the whole
 * track, which is how an overall Track Chair is recorded.
 */
class TrackAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'sub_track_id',
        'role',
        // What this person covers in this particular scope. Kept per assignment
        // because someone can chair or review in two unrelated fields.
        'expertise',
    ];

    protected $casts = [
        'expertise' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function subTrack()
    {
        return $this->belongsTo(SubTrack::class);
    }

    public function scopeChairs($query)
    {
        return $query->where('role', 'chair');
    }

    /** Assignments covering a whole track rather than one sub-track. */
    public function scopeWholeTrack($query)
    {
        return $query->whereNull('sub_track_id');
    }
}
