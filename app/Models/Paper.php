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
        'discussion_opened_at',
        'discussion_opened_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'reviewed_at' => 'datetime',
        'manuscript_uploaded_at' => 'datetime',
        'discussion_opened_at' => 'datetime',
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

    /**
     * The author the conference writes to (requirement document, Phase 5: decisions go
     * "to corresponding authors"). Submission marks exactly one author row, but a paper
     * created before that flag existed has none, so the submitting account stands in.
     */
    public function correspondingAuthor()
    {
        return $this->hasOne(PaperAuthor::class)->where('is_corresponding_author', 1);
    }

    /** Where a notification about this paper goes. */
    public function notificationEmail(): ?string
    {
        return $this->correspondingAuthor?->email ?: $this->user?->email;
    }

    /** Who that notification is addressed to. */
    public function notificationName(): ?string
    {
        return $this->correspondingAuthor?->name ?: $this->user?->name;
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

    public function cameraReady()
    {
        return $this->hasOne(PaperCameraReady::class);
    }

    public function paymentProofs()
    {
        return $this->hasMany(PaperPaymentProof::class)->latest();
    }

    /**
     * Papers the authors have been told are accepted: an approved and notified decision of
     * Accept or Accept with Minor Revisions. Kept in step with ProceedingsRules::isAccepted.
     */
    public function scopeAccepted($query)
    {
        return $query->whereHas('decision', function ($q) {
            $q->where('status', 'approved')
              ->whereNotNull('notified_at')
              ->where('decision', '!=', 'reject');
        });
    }

    /** Comments between the chairs and the TPC Chair, across every round. */
    public function decisionComments()
    {
        return $this->hasMany(PaperDecisionComment::class)->orderBy('created_at')->orderBy('id');
    }

    public function decision()
    {
        return $this->hasOne(PaperDecision::class);
    }

    public function discussionMessages()
    {
        return $this->hasMany(PaperDiscussionMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function discussionOpenedBy()
    {
        return $this->belongsTo(User::class, 'discussion_opened_by');
    }

    public function bids()
    {
        return $this->hasMany(PaperBid::class);
    }

    /**
     * Papers still in the running for review. A rejected abstract goes no further, so
     * it is neither offered for bidding nor handed to reviewers.
     */
    public function scopeUnderConsideration($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'rejected');
        });
    }

    public function hasManuscript(): bool
    {
        return $this->manuscript_path !== null;
    }
}
