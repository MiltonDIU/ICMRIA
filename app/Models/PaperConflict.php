<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A conflict of interest an author declares against a reviewer or an institution,
 * so that reviewer assignment can steer around it.
 */
class PaperConflict extends Model
{
    protected $fillable = [
        'paper_id',
        'declared_by_user_id',
        'conflicted_user_id',
        'conflicted_institution',
        'note',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function declaredBy()
    {
        return $this->belongsTo(User::class, 'declared_by_user_id');
    }

    public function conflictedUser()
    {
        return $this->belongsTo(User::class, 'conflicted_user_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->conflictedUser?->name
            ?: ($this->conflicted_institution ?: 'Unnamed conflict');
    }
}
