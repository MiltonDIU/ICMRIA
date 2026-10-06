<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * An accepted paper's final files and its confirmation for the proceedings
 * (requirement document, Phase 6).
 */
class PaperCameraReady extends Model
{
    use LogsActivity;

    public $table = 'paper_camera_ready';

    /** submitted -> (chair) approved or changes_requested -> (fee paid) confirmed */
    public const STATUSES = [
        'submitted' => 'Awaiting chair check',
        'changes_requested' => 'Changes requested',
        'approved' => 'Files approved',
        'confirmed' => 'Confirmed for Proceedings',
    ];

    /** Where a revised manuscript (Accept with Minor Revisions) stands with the chairs. */
    public const REVISION_STATUSES = [
        'pending' => 'Awaiting chair check',
        'approved' => 'Approved by chair',
        'changes_requested' => 'Changes requested by chair',
    ];

    protected $fillable = [
        'paper_id',
        'camera_ready_path',
        'camera_ready_name',
        'camera_ready_uploaded_at',
        'revised_path',
        'revised_name',
        'revised_uploaded_at',
        'revision_summary',
        'revision_status',
        'revision_note',
        'revision_reviewed_by',
        'revision_reviewed_at',
        'copyright_path',
        'copyright_name',
        'copyright_uploaded_at',
        'status',
        'admin_note',
        'files_reviewed_by',
        'files_reviewed_at',
        'confirmed_by',
        'confirmed_at',
        'schedule_id',
        'presentation_order',
    ];

    protected $casts = [
        'camera_ready_uploaded_at' => 'datetime',
        'revised_uploaded_at' => 'datetime',
        'revision_reviewed_at' => 'datetime',
        'copyright_uploaded_at' => 'datetime',
        'files_reviewed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'presentation_order' => 'integer',
    ];

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /** A chair (or an administrator) has approved the camera-ready files. */
    public function filesApproved(): bool
    {
        return in_array($this->status, ['approved', 'confirmed'], true);
    }

    /** Both files are in and nobody has looked at them yet. */
    public function awaitingCheck(): bool
    {
        return $this->status === 'submitted' && $this->camera_ready_path && $this->copyright_path;
    }

    public function filesReviewedBy()
    {
        return $this->belongsTo(User::class, 'files_reviewed_by');
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function revisionReviewedBy()
    {
        return $this->belongsTo(User::class, 'revision_reviewed_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'status', 'revision_status', 'schedule_id', 'presentation_order'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
