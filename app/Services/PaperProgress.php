<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperProgressEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Records each step a paper takes (PaperProgressEvent::STEPS), so the organisers can
 * report who completed what and when. A failure to record never stops the step itself.
 */
class PaperProgress
{
    /**
     * @param int|null $userId who took the step; the signed-in user when left out
     * @param string|null $reference an outside identifier; a second record with the same
     *                               paper, step and reference is skipped
     */
    public static function record(Paper|int $paper, string $step, ?string $note = null, ?int $userId = null, ?string $reference = null): void
    {
        $paperId = $paper instanceof Paper ? $paper->id : $paper;

        try {
            if ($reference !== null && PaperProgressEvent::where(['paper_id' => $paperId, 'step' => $step, 'reference' => $reference])->exists()) {
                return;
            }

            PaperProgressEvent::create([
                'paper_id' => $paperId,
                'step' => $step,
                'user_id' => $userId ?? Auth::id(),
                'note' => $note,
                'reference' => $reference,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Paper progress could not be recorded', ['paper' => $paperId, 'step' => $step, 'error' => $e->getMessage()]);
        }
    }
}
