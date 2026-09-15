<?php

namespace App\Mail;

use App\Models\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManuscriptReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;

    public function __construct(Paper $paper)
    {
        $this->paper = $paper->load(['user.profile', 'track', 'subTrack']);
    }

    public function build()
    {
        $replaced = $this->paper->manuscript_status === 'revised';

        return $this->to($this->paper->user->email)
                    ->subject(($replaced ? 'Revised Manuscript Received' : 'Manuscript Received')
                        . ' – ICMRIA 2027')
                    ->view('mail.manuscript_received');
    }
}
