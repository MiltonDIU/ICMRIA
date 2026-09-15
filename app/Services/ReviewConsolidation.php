<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperEvaluation;
use Illuminate\Support\Collection;

/**
 * Everything the reviewers said about one paper, brought together for the chair
 * (requirement document, Phase 5: "Track Chairs review evaluation reports for each
 * paper").
 *
 * Reviewers are numbered in the order they were assigned, leaving out anyone who
 * declined. The number is how reviewers appear to one another in the discussion, so it
 * has to hold still while they talk; assignment order does, order of submission would
 * not.
 *
 * Expects the paper's reviewerAssignments with their evaluations; loads them lazily
 * otherwise.
 */
class ReviewConsolidation
{
    /** Averages at least this far apart count as disagreement even when the recommendations agree. */
    public const SCORE_SPREAD = 2.0;

    private const POSITIVE = ['strong_accept', 'accept'];
    private const NEGATIVE = ['reject', 'strong_reject'];

    private Paper $paper;

    public function __construct(Paper $paper)
    {
        $this->paper = $paper;
    }

    public static function for(Paper $paper): self
    {
        return new self($paper);
    }

    /** Reviewers still on the paper, in assignment order. */
    public function assignments(): Collection
    {
        return $this->paper->reviewerAssignments
            ->where('status', '!=', 'declined')
            ->sortBy('id')
            ->values();
    }

    /**
     * The submitted evaluations, each with its reviewer's number.
     *
     * @return Collection<int, array{number: int, assignment: \App\Models\PaperReviewerAssignment, evaluation: PaperEvaluation}>
     */
    public function submitted(): Collection
    {
        return $this->assignments()
            ->map(fn ($assignment, $index) => [
                'number' => $index + 1,
                'assignment' => $assignment,
                'evaluation' => $assignment->evaluation,
            ])
            ->filter(fn ($row) => $row['evaluation'] && $row['evaluation']->isSubmitted())
            ->values();
    }

    /** The number a reviewer goes by on this paper, or null if they are not one of its reviewers. */
    public function reviewerNumber(int $userId): ?int
    {
        $index = $this->assignments()->search(fn ($assignment) => (int) $assignment->reviewer_id === $userId);

        return $index === false ? null : $index + 1;
    }

    public function activeCount(): int
    {
        return $this->assignments()->count();
    }

    public function submittedCount(): int
    {
        return $this->submitted()->count();
    }

    public function minimum(): int
    {
        return app(ReviewerMatcher::class)->minimumReviewers();
    }

    /** Enough evaluations are in for a chair to decide. */
    public function isReady(): bool
    {
        return $this->submittedCount() >= $this->minimum();
    }

    /** @return array<string, float|null> the mean of each criterion, plus 'overall' */
    public function averages(): array
    {
        $evaluations = $this->submitted()->pluck('evaluation');
        $averages = [];

        foreach (array_keys(PaperEvaluation::CRITERIA) as $field) {
            $averages[$field] = $evaluations->isEmpty() ? null : round($evaluations->avg($field), 1);
        }

        $overall = $evaluations->map(fn ($evaluation) => $evaluation->averageScore())->filter(fn ($v) => $v !== null);
        $averages['overall'] = $overall->isEmpty() ? null : round($overall->avg(), 1);

        return $averages;
    }

    /** @return array<string, int> how many reviewers gave each recommendation, strongest first */
    public function recommendationTally(): array
    {
        $given = $this->submitted()->map(fn ($row) => $row['evaluation']->recommendation)->countBy();

        return collect(PaperEvaluation::RECOMMENDATIONS)
            ->map(fn ($label, $key) => $given->get($key, 0))
            ->filter()
            ->all();
    }

    /**
     * Why the evaluations disagree, in words a chair can act on; empty when they do not.
     * The document's own example is "one Accept and one Reject".
     *
     * @return array<int, string>
     */
    public function conflicts(): array
    {
        $rows = $this->submitted();
        $reasons = [];

        $positive = $rows->first(fn ($row) => in_array($row['evaluation']->recommendation, self::POSITIVE, true));
        $negative = $rows->first(fn ($row) => in_array($row['evaluation']->recommendation, self::NEGATIVE, true));

        if ($positive && $negative) {
            $reasons[] = sprintf('Reviewer %d recommends %s while Reviewer %d recommends %s.',
                $positive['number'], PaperEvaluation::RECOMMENDATIONS[$positive['evaluation']->recommendation],
                $negative['number'], PaperEvaluation::RECOMMENDATIONS[$negative['evaluation']->recommendation]);
        }

        $scores = $rows->map(fn ($row) => $row['evaluation']->averageScore())->filter(fn ($v) => $v !== null);

        if ($scores->count() >= 2 && $scores->max() - $scores->min() >= self::SCORE_SPREAD) {
            $reasons[] = sprintf('Average scores range from %.1f to %.1f.', $scores->min(), $scores->max());
        }

        return $reasons;
    }

    public function hasConflict(): bool
    {
        return $this->conflicts() !== [];
    }
}
