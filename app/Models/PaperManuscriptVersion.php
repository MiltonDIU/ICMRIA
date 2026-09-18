<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * One manuscript upload. The newest row is the file currently under review;
 * the older ones are what it replaced.
 */
class PaperManuscriptVersion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'paper_id',
        'uploaded_by',
        'version',
        'path',
        'original_name',
        'size',
        'anonymity_confirmed',
    ];

    protected $casts = [
        'anonymity_confirmed' => 'boolean',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'version', 'uploaded_by', 'original_name'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
