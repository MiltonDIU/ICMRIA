<?php

namespace App\Mail;

use App\Models\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbstractAccepted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Paper $paper)
    {
        $this->paper = $paper->load(['user.profile', 'track', 'subTrack']);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    /** Recipients come from the sender: Paper::notificationRecipients(). */
    public function build()
    {
        return $this->subject('Abstract Accepted – ICMRIA 2027')
                    ->view('mail.abstract_accepted');
    }
}
