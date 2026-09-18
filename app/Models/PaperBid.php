<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * A reviewer's stated preference for one paper, given during bidding.
 */
class PaperBid extends Model
{
    use LogsActivity;

    public const WANT = 'want';
    public const CAN = 'can';
    public const NEUTRAL = 'neutral';
    public const CONFLICT = 'conflict';

    /** The four choices, in the words the requirement document uses. */
    public const LABELS = [
        self::WANT => 'Want to Review',
        self::CAN => 'Can Review',
        self::NEUTRAL => 'Neutral',
        self::CONFLICT => 'Conflict',
    ];

    protected $fillable = [
        'paper_id',
        'reviewer_id',
        'preference',
    ];

    /**
     * How strongly a bid pulls a reviewer up the candidate list. No bid counts the
     * same as Neutral; Conflict never reaches the ranking because it refuses outright.
     */
    public static function rank(?string $preference): int
    {
        return match ($preference) {
            self::WANT => 2,
            self::CAN => 1,
            default => 0,
        };
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'reviewer_id', 'preference'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
