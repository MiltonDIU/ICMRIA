<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Brings a profile's payment status up to date after one of its papers is paid.
 *
 * The same rule OneCardPaymentController::success applies after an online payment, so a
 * verified transfer leaves the profile exactly as a gateway payment would: fully paid
 * when every approved paper is paid, partially paid when some are, and a registration
 * ID issued on the first payment.
 */
class PaymentSync
{
    public static function refreshProfile(User $user): void
    {
        $profile = $user->profile;

        if (!$profile) {
            return;
        }

        $approved = Paper::where('user_id', $user->id)->where('status', 'approved')->count();
        $paid = Paper::where('user_id', $user->id)->where('status', 'approved')->where('payment_status', '1')->count();

        if ($approved === 0 || $paid >= $approved) {
            $status = 1;
        } else {
            $status = $paid > 0 ? 2 : 0;
        }

        $update = ['payment_status' => (string) $status, 'updated_at' => now()];

        if ($status > 0 && empty($profile->registration_id)) {
            $update['registration_id'] = IdGeneratorService::generateRegistrationId();
        }

        DB::table('profiles')->where('id', $profile->id)->update($update);
    }
}
