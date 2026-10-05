<?php

namespace App\Mail;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a chair that the authors of a paper accepted with minor revisions have uploaded
 * the revised manuscript, which waits for them to approve or send back.
 */
class RevisionSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;
    public $chair;

    public function __construct(Paper $paper, User $chair)
    {
        $this->paper = $paper->load(['track', 'subTrack', 'cameraReady']);
        $this->chair = $chair;
    }

    public function build()
    {
        return $this->to($this->chair->email)
                    ->subject('Revised manuscript to check: ' . $this->paper->submission_id . ' – ICMRIA 2027')
                    ->view('mail.revision_submitted');
    }
}
