<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * One message in a paper's internal discussion. Never shown to the authors.
 */
class PaperDiscussionMessage extends Model
{
    use LogsActivity;

    protected $fillable = [
        'paper_id',
        'user_id',
        'body',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'user_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
