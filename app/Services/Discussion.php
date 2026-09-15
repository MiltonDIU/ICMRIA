<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperDiscussionMessage;
use App\Models\User;

/**
 * Who takes part in a paper's internal discussion (requirement document, Phase 5), and
 * how they appear to one another.
 *
 * The committee can always join: the chairs of the paper's track or sub-track, the TPC
 * Chair and administrators, unless they are conflicted on the paper. A reviewer joins
 * only after submitting their own evaluation, so nothing they read here can shape it,
 * and reviewers see one another by number rather than by name.
 */
class Discussion
{
    public static function canTakePart(Paper $paper, User $user): bool
    {
        return self::isCommittee($paper, $user) || self::isReviewerWhoSubmitted($paper, $user);
    }

    public static function isCommittee(Paper $paper, User $user): bool
    {
        if (!$paper->track_id || !$user->can('decision_access')) {
            return false;
        }

        $scope = ChairScope::for($user);

        return $scope->canManage($paper->track_id, $paper->sub_track_id) && $scope->conflictWith($paper) === null;
    }

    public static function isReviewerWhoSubmitted(Paper $paper, User $user): bool
    {
        return $paper->reviewerAssignments->contains(fn ($assignment) =>
            (int) $assignment->reviewer_id === (int) $user->id
            && $assignment->status !== 'declined'
            && $assignment->evaluation
            && $assignment->evaluation->isSubmitted()
        );
    }

    /** How the author of a message is shown to the person reading it. */
    public static function speakerLabel(PaperDiscussionMessage $message, User $viewer, ReviewConsolidation $review, bool $viewerIsCommittee): string
    {
        if ((int) $message->user_id === (int) $viewer->id) {
            return 'You';
        }

        $number = $review->reviewerNumber((int) $message->user_id);

        if ($number !== null) {
            return $viewerIsCommittee
                ? "Reviewer {$number} (" . ($message->user->name ?? '—') . ')'
                : "Reviewer {$number}";
        }

        return 'Committee: ' . ($message->user->name ?? '—');
    }
}
