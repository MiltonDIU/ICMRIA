<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The formal decision on a paper (requirement document, Phase 5). A track chair enters
 * it; the TPC Chair approves it or returns it; only then are the authors told.
 */
class PaperDecision extends Model
{
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
        'note_to_authors',
        'note_to_tpc',
        'decided_by',
        'decided_at',
        'approved_by',
        'approved_at',
        'return_note',
        'notified_at',
    ];

    protected $casts = [
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

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
