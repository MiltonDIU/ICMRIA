<?php

namespace App\Mail;

use App\Models\Paper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the authors their paper is confirmed for the proceedings ($kind "confirmed"),
 * that the camera-ready files need changes ($kind "changes"), or how a chair judged the
 * revised manuscript ($kind "revision_approved" / "revision_changes"). Recipients come
 * from the sender: Paper::notificationRecipients().
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
        $subject = match ($this->kind) {
            'confirmed' => 'Confirmed for Proceedings: ',
            'revision_approved' => 'Revised manuscript approved: ',
            'revision_changes' => 'Revised manuscript needs changes: ',
            default => 'Camera-ready changes requested: ',
        } . $this->paper->submission_id;

        return $this->subject($subject . ' – ICMRIA 2027')
                    ->view('mail.camera_ready_update');
    }
}
