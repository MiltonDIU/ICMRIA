<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One manuscript upload. The newest row is the file currently under review;
 * the older ones are what it replaced.
 */
class PaperManuscriptVersion extends Model
{
    protected $fillable = [
        'paper_id',
        'uploaded_by',
        'version',
        'path',
        'original_name',
        'size',
        'anonymity_confirmed',
    ];

    protected $casts = [
        'anonymity_confirmed' => 'boolean',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
