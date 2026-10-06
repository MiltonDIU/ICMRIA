<?php

namespace App\Mail;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a chair that an accepted paper's camera-ready manuscript and signed copyright
 * form are both in, and wait for them to approve or send back before the author pays.
 */
class CameraReadySubmitted extends Mailable implements ShouldQueue
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
                    ->subject('Camera-ready files to check: ' . $this->paper->submission_id . ' – ICMRIA 2027')
                    ->view('mail.camera_ready_submitted');
    }
}
