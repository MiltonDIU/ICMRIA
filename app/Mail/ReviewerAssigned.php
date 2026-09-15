<?php

namespace App\Mail;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewerAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;
    public $reviewer;

    public function __construct(Paper $paper, User $reviewer)
    {
        $this->paper = $paper->load(['track', 'subTrack']);
        $this->reviewer = $reviewer;
    }

    public function build()
    {
        return $this->to($this->reviewer->email)
                    ->subject('Review Request: ' . \Illuminate\Support\Str::limit($this->paper->title, 60) . ' – ICMRIA 2027')
                    ->view('mail.reviewer_assigned');
    }
}
