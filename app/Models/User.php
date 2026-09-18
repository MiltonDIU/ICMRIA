<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Carbon\Carbon;
use Hash;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use LogsActivity;

    use SoftDeletes, Notifiable, HasApiTokens, HasFactory;

    public $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
        'email_verified_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'email_verified_at',
        'research_keywords',
    ];

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function schedules()
    {
        return $this->belongsToMany(Schedule::class);
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'research_keywords' => 'array',
    ];
    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }
    public function profile(){
        return $this->hasOne(Profile::class,'user_id','id');
    }

    public function feedback()
    {
        return $this->hasMany(Schedule::class);
    }
    
        public function payment(){
        return $this->hasMany(Payment::class,'user_id','id');
    }
    public function referral_user(){
        return $this->belongsTo(Referral::class,'user_id','id');
    }
    public function attendance(){
        return $this->hasMany(Attendance::class,'user_id','id');
    }

    public function paper()
    {
        return $this->hasOne(Paper::class, 'user_id', 'id');
    }

    public function papers()
    {
        return $this->hasMany(Paper::class, 'user_id', 'id');
    }

    public function trackAssignments()
    {
        return $this->hasMany(TrackAssignment::class);
    }

    /** Tracks this user chairs as a whole, i.e. every sub-track under them. */
    public function chairedTracks()
    {
        return $this->belongsToMany(Track::class, 'track_assignments')
            ->wherePivot('role', 'chair')
            ->wherePivotNull('sub_track_id');
    }

    /** Sub-tracks this user chairs individually. */
    public function chairedSubTracks()
    {
        return $this->belongsToMany(SubTrack::class, 'track_assignments')
            ->wherePivot('role', 'chair')
            ->wherePivotNotNull('sub_track_id');
    }

    /**
     * All expertise recorded for this user across their personal research profile
     * and every track they review for, deduplicated.
     *
     * @return array<int, string>
     */
    public function allExpertise(): array
    {
        $trackExpertise = $this->trackAssignments()
            ->where('role', 'reviewer')
            ->get()
            ->flatMap(fn ($a) => \App\Services\SubmissionRules::splitKeywords($a->expertise))
            ->all();

        return \App\Services\SubmissionRules::splitKeywords(array_merge(
            \App\Services\SubmissionRules::splitKeywords($this->research_keywords),
            $trackExpertise
        ));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'research_keywords'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

}
