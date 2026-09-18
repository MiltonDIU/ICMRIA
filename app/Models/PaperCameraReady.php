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

    public const STATUSES = [
        'submitted' => 'Submitted',
        'changes_requested' => 'Changes requested',
        'confirmed' => 'Confirmed for Proceedings',
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
        'copyright_path',
        'copyright_name',
        'copyright_uploaded_at',
        'status',
        'admin_note',
        'confirmed_by',
        'confirmed_at',
        'schedule_id',
        'presentation_order',
    ];

    protected $casts = [
        'camera_ready_uploaded_at' => 'datetime',
        'revised_uploaded_at' => 'datetime',
        'copyright_uploaded_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'presentation_order' => 'integer',
    ];

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'status', 'schedule_id', 'presentation_order'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
