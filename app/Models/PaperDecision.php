<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * The formal decision on a paper (requirement document, Phase 5). A track chair enters
 * it; the TPC Chair approves it or returns it; only then are the authors told.
 */
class PaperDecision extends Model
{
    use LogsActivity;

    /** The three outcomes a chair may choose, in the document's words. */
    public const DECISIONS = [
        'accept' => 'Accept',
        'minor_revisions' => 'Accept with Minor Revisions',
        'reject' => 'Reject',
    ];

    public const STATUSES = [
        'pending_approval' => 'Awaiting TPC approval',
        'approved' => 'Approved',
        'returned' => 'Returned to chair',
    ];

    protected $fillable = [
        'paper_id',
        'decision',
        'status',
        'round',
        'note_to_authors',
        'decided_by',
        'decided_at',
        'approved_by',
        'approved_at',
        'notified_at',
    ];

    protected $casts = [
        'round' => 'integer',
        'decided_at' => 'datetime',
        'approved_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function label(): string
    {
        return self::DECISIONS[$this->decision] ?? $this->decision;
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function comments()
    {
        return $this->hasMany(PaperDecisionComment::class)->orderBy('created_at')->orderBy('id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'decision', 'status', 'round', 'decided_by', 'decided_at', 'approved_by', 'approved_at', 'notified_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
