<?php

namespace App\Mail;

use App\Models\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the author their paper is confirmed for the proceedings ($kind "confirmed"),
 * or that the camera-ready files need changes ($kind "changes").
 */
class CameraReadyUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $paper;
    public $kind;

    public function __construct(Paper $paper, string $kind)
    {
        $this->paper = $paper->load(['user', 'cameraReady']);
        $this->kind = $kind;
    }

    public function build()
    {
        $subject = $this->kind === 'confirmed'
            ? 'Confirmed for Proceedings: ' . $this->paper->submission_id
            : 'Camera-ready changes requested: ' . $this->paper->submission_id;

        return $this->to($this->paper->user->email)
                    ->subject($subject . ' – ICMRIA 2027')
                    ->view('mail.camera_ready_update');
    }
}
