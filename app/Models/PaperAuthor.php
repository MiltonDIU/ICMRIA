<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaperAuthor extends Model
{
    use LogsActivity;

    use SoftDeletes;

    protected $fillable = [
        'paper_id',
        'name',
        'designation',
        'department',
        'institution',
        'country_id',
        'email',
        'is_presenting_author',
        'is_corresponding_author',
        'author_order',
        'is_student',
        'is_attending',
        'price_id',
    ];

    protected $casts = [
        'is_presenting_author' => 'boolean',
        'is_corresponding_author' => 'boolean',
        'is_student' => 'boolean',
        // Only attending authors are charged the registration fee (PricingService).
        'is_attending' => 'boolean',
    ];

    /**
     * is_student follows the delegate category: choosing the Student tier marks the author
     * as a student, any other tier clears it. The category is what the fee is charged on,
     * so the two can never disagree and no separate student tick is needed.
     */
    protected static function booted(): void
    {
        static::saving(function (PaperAuthor $author) {
            if ($author->price_id) {
                $author->is_student = Price::where('id', $author->price_id)->value('category') === 'student';
            }
        });
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paper_id', 'name', 'email', 'institution', 'is_corresponding_author', 'is_presenting_author', 'is_attending'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
