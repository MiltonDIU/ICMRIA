<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One paper handed to one reviewer.
 */
class PaperReviewerAssignment extends Model
{
    protected $fillable = [
        'paper_id',
        'reviewer_id',
        'assigned_by',
        'assignment_source',
        'status',
        'match_score',
        'assigned_at',
        'responded_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** Assignments that still count against a reviewer's workload. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['invited', 'accepted', 'in_progress']);
    }
}
