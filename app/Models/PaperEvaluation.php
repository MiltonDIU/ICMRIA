<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One reviewer's evaluation of one paper (requirement document, Phase 4). A draft until
 * submitted_at is set; after that it is the record the chair decides on.
 */
class PaperEvaluation extends Model
{
    /** The three scored criteria, each 1 to 5, in the document's words. */
    public const CRITERIA = [
        'originality' => 'Originality & Novelty',
        'soundness' => 'Technical Soundness & Methodology',
        'relevance' => 'Relevance to Conference Theme',
    ];

    /**
     * The reviewer's overall recommendation. It advises; the chair's decision in
     * Phase 5 is a separate, three-way choice.
     */
    public const RECOMMENDATIONS = [
        'strong_accept' => 'Strong Accept',
        'accept' => 'Accept',
        'borderline' => 'Borderline',
        'reject' => 'Reject',
        'strong_reject' => 'Strong Reject',
    ];

    protected $fillable = [
        'assignment_id',
        'paper_id',
        'reviewer_id',
        'originality',
        'soundness',
        'relevance',
        'recommendation',
        'feedback_for_authors',
        'confidential_comments',
        'manuscript_version',
        'submitted_at',
    ];

    protected $casts = [
        'originality' => 'integer',
        'soundness' => 'integer',
        'relevance' => 'integer',
        'manuscript_version' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** The mean of the three scores, or null while any is missing. */
    public function averageScore(): ?float
    {
        $scores = [$this->originality, $this->soundness, $this->relevance];

        return in_array(null, $scores, true) ? null : round(array_sum($scores) / 3, 1);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function assignment()
    {
        return $this->belongsTo(PaperReviewerAssignment::class, 'assignment_id');
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
