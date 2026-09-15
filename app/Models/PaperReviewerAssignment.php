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
        'decline_reason',
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

    public function evaluation()
    {
        return $this->hasOne(PaperEvaluation::class, 'assignment_id');
    }

    /** Assignments that still count against a reviewer's workload. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['invited', 'accepted', 'in_progress']);
    }

    /**
     * Assignments that count towards a paper's reviewers. A declined one does not, so
     * the chair is prompted to put someone in its place.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'declined');
    }
}
