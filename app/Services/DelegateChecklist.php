<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\Profile;
use Illuminate\Support\Collection;

/**
 * The open items a delegate still has to deal with, in the order they have to be dealt
 * with.
 *
 * Shared by the dashboard and the profile page. Both screens greet the same person with
 * the same account, so telling them two different stories about what is outstanding is
 * worse than telling them nothing; keeping the rules in one place is what stops that.
 *
 * Each entry is a claim about state the rest of the portal enforces &mdash; the
 * student-status gate before payment, the payment window, the registration ID issued
 * only once a fee has cleared.
 */
class DelegateChecklist
{
    /**
     * @param Collection<int, Paper> $unpaidPapers approved abstracts still awaiting payment
     * @return array<int, array{title: string, body: string, url: string|null, action: string|null, tone: string}>
     */
    public static function for(?Profile $profile, Collection $unpaidPapers, bool $isPaymentOpen): array
    {
        if (!$profile) {
            return [[
                'tone' => 'warning',
                'title' => 'Your registration is not complete',
                'body' => 'We have an account for you but no registration details yet. Complete the registration form to take part.',
                'url' => route('book-ticket'),
                'action' => 'Complete registration',
            ]];
        }

        $todo = [];

        if (!$profile->is_author && $profile->payment_status != '1') {
            $todo[] = [
                'tone' => $isPaymentOpen ? 'primary' : 'danger',
                'title' => 'Your registration fee is unpaid',
                'body' => $isPaymentOpen
                    ? 'Pay the registration fee to confirm your place. Your registration ID is issued once the payment clears.'
                    : 'The payment window has closed. Contact the organising committee to settle this.',
                'url' => route('my-profile'),
                'action' => 'Review & pay',
            ];
        }

        if ($unpaidPapers->isNotEmpty() && !$profile->author_list_confirmed) {
            $todo[] = [
                'tone' => 'warning',
                'title' => 'Confirm the author list and student status',
                'body' => 'Before you can pay for your approved abstract(s) we need the student status of every listed author. It can only be set once, so check it carefully.',
                'url' => route('papers.index'),
                'action' => 'Confirm on the Abstracts page',
            ];
        } elseif ($unpaidPapers->isNotEmpty()) {
            $count = $unpaidPapers->count();
            $todo[] = [
                'tone' => $isPaymentOpen ? 'primary' : 'danger',
                'title' => $count === 1 ? 'One approved abstract is awaiting payment' : "{$count} approved abstracts are awaiting payment",
                'body' => $isPaymentOpen
                    ? 'Every listed author pays a registration fee. You can settle them all in a single transaction.'
                    : 'The payment window has closed. Contact the organising committee to settle this.',
                'url' => $isPaymentOpen ? route('my-profile') : null,
                'action' => $isPaymentOpen ? 'Review & pay all' : null,
            ];
        }

        if ($profile->payment_status == '1' && !$profile->registration_id) {
            $todo[] = [
                'tone' => 'info',
                'title' => 'Your registration ID is being issued',
                'body' => 'Your payment has cleared. The ID appears here once the organising committee issues it; nothing is needed from you.',
                'url' => null,
                'action' => null,
            ];
        }

        return $todo;
    }
}
