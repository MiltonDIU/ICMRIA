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
        'price_id',
    ];

    protected $casts = [
        'is_presenting_author' => 'boolean',
        'is_corresponding_author' => 'boolean',
        'is_student' => 'boolean',
    ];

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
            ->logOnly(['paper_id', 'name', 'email', 'institution', 'is_corresponding_author', 'is_presenting_author'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
