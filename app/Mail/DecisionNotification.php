<?php

namespace App\Mail;

use App\Models\Paper;
use App\Services\ReviewConsolidation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The decision on a paper, sent to its corresponding author with the reviewers'
 * feedback. Reviewer names, recommendations and confidential comments are never
 * included.
 */
class DecisionNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;

    public function __construct(Paper $paper)
    {
        $this->paper = $paper->load(['user', 'track', 'subTrack', 'decision', 'reviewerAssignments.evaluation']);
    }

    public function build()
    {
        return $this->to($this->paper->user->email)
                    ->subject('Decision on ' . $this->paper->submission_id . ': ' . $this->paper->decision->label() . ' – ICMRIA 2027')
                    ->view('mail.decision_notification', [
                        'decision' => $this->paper->decision,
                        'review' => ReviewConsolidation::for($this->paper),
                    ]);
    }
}
