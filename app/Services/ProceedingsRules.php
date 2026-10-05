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

    /**
     * Whether registration payment is currently open:
     * 1. Administrator manual switch `is_payment_enabled` is active.
     * 2. `payment_last_date` deadline has not passed.
     * 3. Total paid papers has not reached `max_paid_papers_limit` (if set).
     */
    public static function paymentWindowIsOpen(): bool
    {
        return self::paymentBlockReason() === null;
    }

    public static function paymentIsOpen(): bool
    {
        return self::paymentBlockReason() === null;
    }

    /**
     * Returns null if payments are allowed, or a clear user-facing reason if blocked.
     */
    public static function paymentBlockReason(): ?string
    {
        // 1. Administrator Manual Switch
        if (!self::isPaymentEnabled()) {
            return 'Online payment is temporarily closed by the conference administration.';
        }

        // 2. Payment Deadline
        $deadline = self::dateSetting('payment_last_date');
        if ($deadline && Carbon::now()->gt($deadline)) {
            return 'The payment deadline has passed. Payments are no longer accepted.';
        }

        // 3. Automated Capacity Cap
        $limit = self::maxPaidPapersLimit();
        if ($limit !== null) {
            $paidCount = self::totalPaidPapersCount();
            if ($paidCount >= $limit) {
                return "The conference capacity limit of {$limit} paid papers has been reached. Payment is currently closed.";
            }
        }

        return null;
    }

    /**
     * Whether authors may report a bank or mobile transfer with a receipt for an admin to
     * verify. Off by default: OneCard takes every payment, foreign ones converted from USD
     * at the day's rate, so the gateway is the only route unless the organisers switch
     * this on (setting manual_payment_enabled = 'true').
     */
    public static function manualPaymentEnabled(): bool
    {
        return Setting::where('key', 'manual_payment_enabled')->value('value') === 'true';
    }

    /**
     * Whether authors may change who attends after the abstract is submitted, up to paying
     * (paper page card and the confirm-authors step). Setting attendance_change_enabled;
     * on unless set to 'false', in which case the choices made at submission stand.
     */
    public static function attendanceChangeAllowed(): bool
    {
        return Setting::where('key', 'attendance_change_enabled')->value('value') !== 'false';
    }

    public static function isPaymentEnabled(): bool
    {
        return Setting::where('key', 'is_payment_enabled')->value('value') !== 'false';
    }

    public static function totalPaidPapersCount(): int
    {
        return Paper::where('payment_status', 1)->count();
    }

    public static function maxPaidPapersLimit(): ?int
    {
        $limit = Setting::where('key', 'max_paid_papers_limit')->value('value');

        return ($limit !== null && $limit !== '' && (int) $limit > 0) ? (int) $limit : null;
    }

    /*
     * The chain (requirement document, Phase 6; organisers, 2026-10-05). Each step opens
     * only once the one before it is done, and nothing can be skipped:
     *
     *   accepted -> revision approved by a chair (minor revisions only) -> camera-ready
     *   manuscript -> signed copyright form -> registration fee -> confirmed by admin
     */

    /** Nothing owed on the revision: none was asked for, or a chair approved it. */
    public static function revisionCleared(Paper $paper): bool
    {
        return !self::needsRevision($paper) || $paper->cameraReady?->revision_status === 'approved';
    }

    public static function cameraReadyUnlocked(Paper $paper): bool
    {
        return self::isAccepted($paper) && self::revisionCleared($paper);
    }

    public static function copyrightUnlocked(Paper $paper): bool
    {
        return self::cameraReadyUnlocked($paper) && (bool) $paper->cameraReady?->camera_ready_path;
    }

    /** Both files are in, and the administrator has not sent them back. */
    public static function paymentUnlocked(Paper $paper): bool
    {
        return self::copyrightUnlocked($paper)
            && (bool) $paper->cameraReady?->copyright_path
            && $paper->cameraReady->status !== 'changes_requested';
    }

    /** What the author has to do next, while a step stands between them and payment. */
    public static function paymentLockedReason(Paper $paper): ?string
    {
        return match (true) {
            self::paymentUnlocked($paper) => null,
            !self::isAccepted($paper) => 'The registration fee is paid once your paper is accepted and the camera-ready step is complete.',
            !self::revisionCleared($paper) => 'Your revised manuscript must be approved by the track chair first.',
            !$paper->cameraReady?->camera_ready_path => 'Upload the camera-ready manuscript first.',
            !$paper->cameraReady?->copyright_path => 'Upload the signed copyright transfer form first.',
            default => 'The conference team has asked for changes to your camera-ready files. Upload the corrected files first.',
        };
    }

    /** Whether the registration fee for this paper is due now: the chain has reached it. */
    public static function needsPayment(Paper $paper): bool
    {
        return !self::isPaid($paper) && self::paymentUnlocked($paper);
    }

    /**
     * The author's papers whose fee is due now, with what the chain checks loaded.
     *
     * @return \Illuminate\Support\Collection<int, Paper>
     */
    public static function payablePapersFor(int $userId)
    {
        return Paper::where('user_id', $userId)
            ->with(['decision', 'cameraReady', 'authors'])
            ->get()
            ->filter(fn (Paper $paper) => self::needsPayment($paper))
            ->values();
    }

    /** @return array<string, array{label: string, done: bool}> */
    public static function checklist(Paper $paper): array
    {
        $final = $paper->cameraReady;

        $items = [
            'accepted' => ['label' => 'Paper accepted and authors notified', 'done' => self::isAccepted($paper)],
        ];

        // Accepted on condition: the revised manuscript comes before the camera-ready one,
        // and counts once a chair has approved it (RevisionReviewController).
        if (self::needsRevision($paper)) {
            $items['revision'] = [
                'label' => 'Revised manuscript approved by the track chair (minor revisions)',
                'done' => $final?->revised_path && $final->revision_status === 'approved',
            ];
        }

        // In chain order; each item counts only once everything above it is done.
        return $items + [
            'camera_ready' => ['label' => 'Camera-ready manuscript uploaded', 'done' => self::cameraReadyUnlocked($paper) && (bool) $final?->camera_ready_path],
            'copyright' => ['label' => 'Signed copyright transfer form uploaded', 'done' => self::copyrightUnlocked($paper) && (bool) $final?->copyright_path],
            'payment' => ['label' => 'Registration fee paid', 'done' => self::isPaid($paper)],
        ];
    }

    /** Accepted with minor revisions, so a revised manuscript is owed first. */
    public static function needsRevision(Paper $paper): bool
    {
        return self::isAccepted($paper) && $paper->decision->decision === 'minor_revisions';
    }

    /** The revised manuscript is due by revision_deadline, or the camera-ready deadline when unset. */
    public static function revisionDeadline(): ?Carbon
    {
        return self::dateSetting('revision_deadline') ?? self::cameraReadyDeadline();
    }

    public static function revisionWindowIsOpen(): bool
    {
        $deadline = self::revisionDeadline();

        return !$deadline || Carbon::now()->lte($deadline);
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
