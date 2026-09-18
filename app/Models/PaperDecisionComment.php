<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * One comment in the exchange between a paper's chairs and the TPC Chair. Never shown to
 * authors or reviewers, and never edited: a later thought is a new comment.
 */
class PaperDecisionComment extends Model
{
    use LogsActivity;

    public const ROLES = [
        'chair' => 'Chair',
        'tpc' => 'TPC Chair',
    ];

    /** What the comment came with. */
    public const KINDS = [
        'comment' => 'Comment',
        'decision' => 'With the decision',
        'returned' => 'Returned to the chair',
        'approved' => 'With the approval',
    ];

    protected $fillable = [
        'paper_id',
        'paper_decision_id',
        'user_id',
        'author_role',
        'kind',
        'round',
        'body',
    ];

    protected $casts = [
        'round' => 'integer',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function decision()
    {
        return $this->belongsTo(PaperDecision::class, 'paper_decision_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'paper_decision_id', 'user_id', 'author_role', 'kind', 'round'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
