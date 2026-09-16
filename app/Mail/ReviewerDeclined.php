<?php

namespace App\Mail;

use App\Models\PaperReviewerAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a chair that a reviewer has declined a paper in their scope, so someone can be
 * assigned in their place promptly.
 */
class ReviewerDeclined extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $assignment;
    public $chair;

    public function __construct(PaperReviewerAssignment $assignment, User $chair)
    {
        $this->assignment = $assignment->load(['paper.track', 'paper.subTrack', 'paper.reviewerAssignments', 'reviewer']);
        $this->chair = $chair;
    }

    public function build()
    {
        return $this->to($this->chair->email)
                    ->subject('Reviewer declined: ' . $this->assignment->paper->submission_id . ' needs a replacement – ICMRIA 2027')
                    ->view('mail.reviewer_declined');
    }
}
