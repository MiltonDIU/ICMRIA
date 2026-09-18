<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Speaker extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, LogsActivity;

    public $table = 'speakers';

    protected $appends = [
        'photo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'slug',
        'show_home',
        'serial',
        'speaker_type_id',
        'track_id',
        'focus_area',
        'affiliation',
        'country',
        'twitter',
        'facebook',
        'linkedin',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
        'full_description',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(50)->height(50);
    }
    public function events()
    {
        return $this->belongsToMany(Event::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'speaker_id', 'id');
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
    public function speakerType(){
        return $this->belongsTo(SpeakerType::class,'speaker_type_id','id');
    }
    public function track(){
        return $this->belongsTo(Track::class, 'track_id', 'id');
    }
    public function guestCategories()
    {
        return $this->belongsToMany(GuestCategory::class);
    }

    public function syncGuestCategory($guestCategories)
    {
        $this->guestCategories()->sync($guestCategories);
    }
    public function schedule()
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'speaker_type_id', 'track_id', 'focus_area', 'affiliation', 'country', 'show_home', 'serial'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
