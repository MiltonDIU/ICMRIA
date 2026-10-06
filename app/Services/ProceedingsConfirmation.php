<?php

namespace App\Services;

use App\Mail\CameraReadyUpdate;
use App\Models\Paper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Confirms a paper for the proceedings as soon as nothing is outstanding. The fee is the
 * last step (the files are approved before it), so this runs when a payment comes in:
 * online through OneCard, or a transfer verified by an administrator. It also runs when
 * files are approved for a paper already paid under the earlier order of steps.
 */
class ProceedingsConfirmation
{
    /** @return bool whether the paper was confirmed now */
    public static function confirmIfReady(Paper|int $paper, ?string $note = null): bool
    {
        $paper = $paper instanceof Paper ? $paper : Paper::find($paper);
        if (!$paper) {
            return false;
        }

        $paper->load(['decision', 'cameraReady']);
        $final = $paper->cameraReady;

        if (!$final || $final->status !== 'approved' || ProceedingsRules::missing($paper)) {
            return false;
        }

        $final->update(['status' => 'confirmed', 'confirmed_by' => auth()->id(), 'confirmed_at' => now()]);
        PaperProgress::record($paper, 'confirmed', $note);

        try {
            Mail::to($paper->notificationRecipients())->queue(new CameraReadyUpdate($paper->fresh(), 'confirmed'));
        } catch (\Exception $e) {
            Log::error('Confirmation mail failed', ['paper' => $paper->id, 'error' => $e->getMessage()]);
        }

        return true;
    }
}
