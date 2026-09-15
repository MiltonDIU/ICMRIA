<?php

namespace App\Mail;

use App\Models\Paper;
use App\Models\PaperReviewerAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DiscussionOpened extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;
    public $assignment;
    public $reviewer;

    public function __construct(Paper $paper, PaperReviewerAssignment $assignment)
    {
        $this->paper = $paper;
        $this->assignment = $assignment;
        $this->reviewer = $assignment->reviewer;
    }

    public function build()
    {
        return $this->to($this->reviewer->email)
                    ->subject('Discussion opened: ' . $this->paper->submission_id . ' – ICMRIA 2027')
                    ->view('mail.discussion_opened');
    }
}
