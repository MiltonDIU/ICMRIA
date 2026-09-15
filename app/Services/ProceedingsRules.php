<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\Setting;
use Carbon\Carbon;

/**
 * What an accepted paper needs before it is "Confirmed for Proceedings" (requirement
 * document, Phase 6), and when authors may still act.
 *
 * A paper counts as accepted only once the authors have been told: the decision is
 * Accept or Accept with Minor Revisions, approved by the TPC Chair, and notified. Before
 * that the author has no reason to prepare a camera-ready version.
 */
class ProceedingsRules
{
    public static function isAccepted(Paper $paper): bool
    {
        $decision = $paper->decision;

        return $decision
            && $decision->isApproved()
            && $decision->notified_at !== null
            && $decision->decision !== 'reject';
    }

    /** The authors have been told the paper is rejected, so no fee is due for it. */
    public static function isRejected(Paper $paper): bool
    {
        $decision = $paper->decision;

        return $decision && $decision->isApproved() && $decision->decision === 'reject';
    }

    public static function isPaid(Paper $paper): bool
    {
        return (string) $paper->payment_status === '1';
    }

    public static function cameraReadyDeadline(): ?Carbon
    {
        return self::dateSetting('camera_ready_deadline') ?? self::dateSetting('registration_close_date');
    }

    public static function cameraReadyWindowIsOpen(): bool
    {
        $deadline = self::cameraReadyDeadline();

        return !$deadline || Carbon::now()->lte($deadline);
    }

    /** Same deadline the online gateway checks before taking a payment. */
    public static function paymentWindowIsOpen(): bool
    {
        $deadline = self::dateSetting('payment_last_date');

        return !$deadline || Carbon::now()->lte($deadline);
    }

    /**
     * Whether the registration fee for this paper is still to be settled. The online
     * Pay button already offers payment once the abstract is approved, so a reported
     * transfer is accepted from the same point.
     */
    public static function needsPayment(Paper $paper): bool
    {
        return !self::isPaid($paper)
            && !self::isRejected($paper)
            && ($paper->status === 'approved' || self::isAccepted($paper));
    }

    /** @return array<string, array{label: string, done: bool}> */
    public static function checklist(Paper $paper): array
    {
        $final = $paper->cameraReady;

        return [
            'accepted' => ['label' => 'Paper accepted and authors notified', 'done' => self::isAccepted($paper)],
            'camera_ready' => ['label' => 'Camera-ready manuscript uploaded', 'done' => (bool) $final?->camera_ready_path],
            'copyright' => ['label' => 'Signed copyright transfer form uploaded', 'done' => (bool) $final?->copyright_path],
            'payment' => ['label' => 'Registration fee verified', 'done' => self::isPaid($paper)],
        ];
    }

    /** @return array<int, string> the checklist items still outstanding */
    public static function missing(Paper $paper): array
    {
        return collect(self::checklist($paper))
            ->reject(fn ($item) => $item['done'])
            ->pluck('label')
            ->values()
            ->all();
    }

    private static function dateSetting(string $key): ?Carbon
    {
        $value = Setting::where('key', $key)->value('value');

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
