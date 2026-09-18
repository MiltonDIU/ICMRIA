<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ConferenceMessage extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    public $table = 'conference_messages';

    protected $appends = [
        'photo',
    ];

    protected $fillable = [
        'conference_message_category_id',
        'variant',
        'person_name',
        'designation',
        'affiliation',
        'message',
        'profile_url',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published'                    => 'boolean',
        'sort_order'                      => 'integer',
        'conference_message_category_id' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ConferenceMessageCategory::class, 'conference_message_category_id');
    }

    public function conferenceMessageCategory()
    {
        return $this->belongsTo(ConferenceMessageCategory::class, 'conference_message_category_id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(120)->height(120);
    }

    public function getPhotoAttribute()
    {
        $file = $this->getMedia('photo')->last();

        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
        }

        return $file;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['conference_message_category_id', 'variant', 'person_name', 'designation', 'affiliation', 'sort_order', 'is_published'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
