<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;

/**
 * A registration fee the author paid outside the online gateway and reported for
 * verification (requirement document, Phase 6: "Author inputs transaction details or
 * uploads payment proof").
 */
class PaperPaymentProof extends Model
{
    use LogsActivity;

    public const METHODS = [
        'bank_transfer' => 'Bank transfer',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'card' => 'Card, paid outside this website',
        'other' => 'Other',
    ];

    public const CURRENCIES = ['BDT', 'USD'];

    public const STATUSES = [
        'submitted' => 'Awaiting verification',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    protected $fillable = [
        'paper_id',
        'user_id',
        'method',
        'transaction_id',
        'amount',
        'currency',
        'paid_on',
        'proof_path',
        'proof_name',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'status', 'transaction_id', 'amount', 'reviewed_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
