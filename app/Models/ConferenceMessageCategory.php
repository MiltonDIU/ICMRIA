<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ConferenceMessageCategory extends Model
{
    use LogsActivity;

    public $table = 'conference_message_categories';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function messages()
    {
        return $this->hasMany(ConferenceMessage::class, 'conference_message_category_id')->orderBy('sort_order')->orderBy('id');
    }

    public function publishedMessages()
    {
        return $this->hasMany(ConferenceMessage::class, 'conference_message_category_id')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'sort_order', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
