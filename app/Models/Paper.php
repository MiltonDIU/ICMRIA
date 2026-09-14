<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paper extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'submission_id',
        'track_id',
        'sub_track_id',
        'title',
        'abstract',
        'keywords',
        'manuscript_path',
        'manuscript_original_name',
        'manuscript_uploaded_at',
        'manuscript_status',
        'mode_of_participation',
        'is_corresponding_author',
        'has_multiple_authors',
        'status',
        'payment_status',
        'pay_amount',
        'currency',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'reviewed_at' => 'datetime',
        'manuscript_uploaded_at' => 'datetime',
        'user_id' => 'integer',
        'payment_status' => 'integer',
        'has_multiple_authors' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function subTrack()
    {
        return $this->belongsTo(SubTrack::class, 'sub_track_id');
    }

    public function authors()
    {
        return $this->hasMany(PaperAuthor::class)->orderBy('author_order');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewHistory()
    {
        return $this->hasMany(PaperReview::class)->latest();
    }

    public function conflicts()
    {
        return $this->hasMany(PaperConflict::class);
    }

    public function manuscriptVersions()
    {
        return $this->hasMany(PaperManuscriptVersion::class)->orderByDesc('version');
    }

    public function reviewerAssignments()
    {
        return $this->hasMany(PaperReviewerAssignment::class);
    }

    public function hasManuscript(): bool
    {
        return $this->manuscript_path !== null;
    }
}
