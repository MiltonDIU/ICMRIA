<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Removes the registration fees that used to live in the settings table.
 *
 * Fees moved onto the prices table (one row per delegate category, carrying both the
 * early-bird and the regular amount) and PricingService has read them from there since.
 * The old per-currency keys stayed behind, unused, where they could still be edited
 * under Settings and leave someone wondering which figure the site actually charges.
 *
 * Only the fee amounts go. selected_domain_discount and special_discount_is_true stay:
 * they are the domain-discount switch and amount, which PricingService still reads.
 */
return new class extends Migration
{
    private const LEGACY_KEYS = [
        'usd_earlybird_price', 'usd_regular_price', 'usd_participant_price',
        'eur_earlybird_price', 'eur_regular_price', 'eur_participant_price',
        'inr_earlybird_price', 'inr_regular_price', 'inr_participant_price',
        'saarc_earlybird_price', 'saarc_regular_price',
        'bdt_earlybird_price', 'bdt_regular_price', 'bdt_participant_price',
        'bdt_student_earlybird_price', 'bdt_student_regular_price',
        'event_price', 'early_registration_event_price',
    ];

    public function up(): void
    {
        Setting::whereIn('key', self::LEGACY_KEYS)->delete();
    }

    /**
     * Nothing to restore: these rows were read by no code, and the fees they once held
     * are in the prices table, where the amounts may since have changed. Bringing back
     * stale figures would be worse than leaving them out.
     */
    public function down(): void
    {
    }
};
