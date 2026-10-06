<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A step a paper took along the chain, and who took it. Written through
 * App\Services\PaperProgress, never edited afterwards.
 */
class PaperProgressEvent extends Model
{
    public const UPDATED_AT = null;

    /** Every step, in the order a paper normally goes through them. */
    public const STEPS = [
        'abstract_submitted' => 'Abstract submitted',
        'abstract_approved' => 'Abstract approved',
        'abstract_rejected' => 'Abstract rejected',
        'manuscript_uploaded' => 'Full manuscript uploaded',
        'decision_notified' => 'Decision sent to authors',
        'revision_uploaded' => 'Revised manuscript uploaded',
        'revision_approved' => 'Revised manuscript approved by chair',
        'revision_changes_requested' => 'Revised manuscript sent back by chair',
        'camera_ready_uploaded' => 'Camera-ready manuscript uploaded',
        'copyright_uploaded' => 'Copyright form uploaded',
        'attendance_updated' => 'Attending authors changed',
        'payment_reported' => 'Transfer payment reported',
        'payment_verified' => 'Transfer payment verified',
        'payment_rejected' => 'Transfer payment rejected',
        'payment_online' => 'Paid online',
        'camera_ready_approved' => 'Camera-ready files approved',
        'camera_ready_changes_requested' => 'Camera-ready files sent back',
        'confirmed' => 'Confirmed for Proceedings',
    ];

    protected $fillable = ['paper_id', 'step', 'user_id', 'note', 'reference', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::STEPS[$this->step] ?? $this->step;
    }
}
