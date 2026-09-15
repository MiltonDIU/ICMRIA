<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One message in a paper's internal discussion. Never shown to the authors.
 */
class PaperDiscussionMessage extends Model
{
    protected $fillable = [
        'paper_id',
        'user_id',
        'body',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
