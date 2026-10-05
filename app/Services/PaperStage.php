<?php

namespace App\Services;

use App\Models\Paper;

/**
 * Where a paper really stands along the whole chain, as one label: abstract, manuscript,
 * review, decision, revision, camera-ready, copyright, fee and confirmation.
 *
 * papers.status only records the abstract screening (pending / approved / rejected), so a
 * paper accepted after review could still read "pending" there. This reads every step
 * instead, most advanced first.
 *
 * For the author ($forAuthor) the committee's internal steps stay hidden until the
 * decision email has gone: they see "Under review" rather than "Awaiting TPC approval".
 */
class PaperStage
{
    /** @return array{label: string, style: string} style is a Bootstrap badge colour */
    public static function for(Paper $paper, bool $forAuthor = false): array
    {
        $paper->loadMissing(['decision', 'cameraReady']);
        $decision = $paper->decision;
        $final = $paper->cameraReady;

        if ($paper->status === 'rejected') {
            return self::stage('Abstract not accepted', 'danger');
        }

        if ($decision && $decision->isApproved() && $decision->decision === 'reject' && ($decision->notified_at || !$forAuthor)) {
            return self::stage('Rejected after review', 'danger');
        }

        if ($final?->isConfirmed()) {
            return self::stage('Confirmed for Proceedings', 'success');
        }

        if (ProceedingsRules::isAccepted($paper)) {
            return self::acceptedStage($paper, $final);
        }

        // A decision exists but the authors have not been told yet.
        if ($decision && !$forAuthor) {
            return match (true) {
                $decision->isApproved() => self::stage('Decision approved — email not sent', 'info'),
                $decision->status === 'returned' => self::stage('Decision returned to chair', 'warning'),
                default => self::stage('Decision awaiting TPC approval', 'info'),
            };
        }

        $underReview = $paper->reviewerAssignments()->where('status', '!=', 'declined')->exists();
        if ($underReview || $decision) {
            return self::stage('Under review', 'primary');
        }

        if ($paper->manuscript_path) {
            return self::stage('Manuscript submitted', 'secondary');
        }

        return $paper->status === 'approved'
            ? self::stage('Abstract approved — manuscript due', 'secondary')
            : self::stage('Abstract submitted', 'light');
    }

    private static function acceptedStage(Paper $paper, $final): array
    {
        $label = $paper->decision->decision === 'minor_revisions' ? 'Accepted (minor revisions)' : 'Accepted';

        if (ProceedingsRules::needsRevision($paper) && !ProceedingsRules::revisionCleared($paper)) {
            return match ($final?->revision_status) {
                'pending' => self::stage($label . ' — revision with the chair', 'info'),
                'changes_requested' => self::stage($label . ' — revision changes requested', 'warning'),
                default => self::stage($label . ' — revision due', 'warning'),
            };
        }

        return match (true) {
            !$final?->camera_ready_path => self::stage($label . ' — camera-ready due', 'warning'),
            !$final->copyright_path => self::stage($label . ' — copyright form due', 'warning'),
            $final->status === 'changes_requested' => self::stage($label . ' — camera-ready changes requested', 'warning'),
            // Camera-ready and copyright are both in; only the fee is outstanding.
            !ProceedingsRules::isPaid($paper) => self::stage($label . ' — registration fee due', 'warning'),
            default => self::stage($label . ' — paid, awaiting confirmation', 'info'),
        };
    }

    private static function stage(string $label, string $style): array
    {
        return ['label' => $label, 'style' => $style];
    }
}
