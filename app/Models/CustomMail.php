<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomMail extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['subject','mail_body','user_id','publication_status'];
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['subject', 'user_id', 'publication_status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
