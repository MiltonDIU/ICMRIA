<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataBank extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    public $table = 'data_banks';

    public const IS_SUBSCRIBE_SELECT = [
        '1' => 'Yes',
        '0' => 'No',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'email',
        'is_subscribe',
        'name',
        'unsubscribe_link',
        'data_bank_category_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function data_bank_categories()
    {
        return $this->belongsToMany(DataBankCategory::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'data_bank_category_id', 'is_subscribe'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
