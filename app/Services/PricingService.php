<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Profile;
use App\Models\Domain;
use App\Models\Price;
use Carbon\Carbon;

/**
 * Fees come from the prices table, keyed by registration category and stage.
 * Settings still supply the dates and the domain-discount switch, but no longer
 * any amount: the old per-currency keys (usd_earlybird_price, bdt_regular_price, ...)
 * could not express the Industry/R&D tier at all.
 */
class PricingService
{
    public const CATEGORIES = ['student', 'academic', 'industry', 'saarc', 'international'];

    /**
     * SAARC member states other than Bangladesh, matched against countries.name in
     * lower case. Bangladesh is deliberately absent: local delegates are billed on
     * the BDT tiers (student / academic / industry), not the USD SAARC rate.
     * All eight names below were verified against the countries table.
     */
    private const SAARC_COUNTRIES = ['afghanistan', 'bhutan', 'india', 'maldives', 'nepal', 'pakistan', 'sri lanka'];

    private const HOST_COUNTRY = 'bangladesh';

    /**
     * Which fee tiers a delegate from this country is entitled to choose.
     *
     * @param string|null $countryName
     * @return array<int, string>
     */
    public static function allowedCategoriesFor($countryName)
    {
        $country = strtolower(trim($countryName ?? ''));

        if ($country === self::HOST_COUNTRY) {
            return ['student', 'academic', 'industry'];
        }

        if (in_array($country, self::SAARC_COUNTRIES, true)) {
            return ['saarc'];
        }

        return ['international'];
    }

    /**
     * Whether early-bird or regular rates currently apply.
     *
     * @return string 'early_bird' or 'regular'
     */
    public static function currentStage()
    {
        $configured = Setting::where('key', 'early_registration_last_date')->value('value');

        try {
            $earlyBirdDateLimit = $configured ? Carbon::parse($configured) : Carbon::parse('2000-01-01');
        } catch (\Exception $e) {
            \Log::warning("PricingService: Invalid early_registration_last_date format: '{$configured}'. Falling back to regular price.");
            $earlyBirdDateLimit = Carbon::parse('2000-01-01');
        }

        return $earlyBirdDateLimit->gt(Carbon::now()) ? 'early_bird' : 'regular';
    }

    /**
     * Work out which fee tier someone falls into when they have not been given an
     * explicit price row: derived from student status and country.
     *
     * @param string|null $countryName
     * @param bool $isStudent
     * @return string one of self::CATEGORIES
     */
    public static function resolveCategory($countryName, $isStudent = false)
    {
        $allowed = self::allowedCategoriesFor($countryName);

        // SAARC and international delegates have only one tier, so there is nothing to pick.
        if (count($allowed) === 1) {
            return $allowed[0];
        }

        // Bangladesh: students get the student tier, everyone else defaults to academic.
        return $isStudent ? 'student' : 'academic';
    }

    /**
     * @return Price|null
     */
    public static function priceFor($category)
    {
        $price = Price::where('category', $category)->first();

        if (!$price) {
            \Log::error("PricingService: No price row for category '{$category}'.");
        }

        return $price;
    }

    /**
     * The price row that applies to a profile or paper author: the one explicitly
     * assigned to them if there is one, otherwise the tier their country and
     * student status put them in.
     *
     * @param \App\Models\Profile|\App\Models\PaperAuthor $holder
     * @param string|null $fallbackCountryName
     * @return Price|null
     */
    public static function priceRowFor($holder, $fallbackCountryName = null)
    {
        if ($holder->price_id && $holder->relationLoaded('price') === false) {
            $holder->load('price');
        }

        if ($holder->price) {
            return $holder->price;
        }

        return self::priceFor(self::resolveCategory(
            $holder->country->name ?? $fallbackCountryName,
            (bool) ($holder->is_student ?? false)
        ));
    }

    /**
     * Calculate the cost for a single abstract
     *
     * @param Profile $profile The user's profile
     * @param \App\Models\Paper|null $paper The paper being checked out
     * @return array Contains base_price, discount, final_price, currency, stage, authors_count
     */
    public static function calculatePaperCost(Profile $profile, ?\App\Models\Paper $paper = null)
    {
        $stage = self::currentStage();
        $countryName = $profile->country->name ?? '';

        $profilePrice = self::priceRowFor($profile);

        $basePrice = $profilePrice ? $profilePrice->amountFor($stage) : 0.0;
        $currencyCode = strtoupper($profilePrice->currency ?? 'USD');
        $finalPrice = self::applyDomainDiscount($profile, $basePrice);

        $totalBasePrice = 0;
        $totalFinalPrice = 0;
        $authorFees = [];

        if ($paper !== null) {
            $authors = $paper->authors()->get();
            $authorCount = max(1, $authors->count());

            foreach ($authors as $author) {
                $authorPrice = self::priceRowFor($author, $countryName);

                // A paper carries one currency and one total, so an author whose own
                // tier is priced in a different currency is billed at the registering
                // author's rate rather than producing an uncomparable sum.
                //
                // Provisional, agreed with the organisers 2026-09-14: a foreign
                // co-author therefore does not pay their own country's rate. Revisit
                // by adding a configurable exchange rate if that becomes a problem.
                if ($authorPrice && strtoupper($authorPrice->currency) === $currencyCode) {
                    $authorBase = $authorPrice->amountFor($stage);
                    $fee = self::applyDomainDiscount($profile, $authorBase);
                } else {
                    $authorBase = $basePrice;
                    $fee = $finalPrice;
                }

                $totalBasePrice += $authorBase;
                $totalFinalPrice += $fee;
                $authorFees[$author->id] = $fee;
            }
        } else {
            $authorCount = 1;
            $totalBasePrice = $basePrice;
            $totalFinalPrice = $finalPrice;
        }

        return [
            'base_price' => $totalBasePrice,
            'discount' => $totalBasePrice - $totalFinalPrice,
            'final_price' => $totalFinalPrice,
            'individual_base_price' => $authorCount > 0 ? ($totalBasePrice / $authorCount) : $basePrice,
            'individual_discount' => ($authorCount > 0 ? ($totalBasePrice / $authorCount) : $basePrice) - ($authorCount > 0 ? ($totalFinalPrice / $authorCount) : $finalPrice),
            'individual_final_price' => $authorCount > 0 ? ($totalFinalPrice / $authorCount) : $finalPrice,
            'currency' => $currencyCode,
            'stage' => $stage,
            'authors_count' => $authorCount,
            'author_fees' => $authorFees
        ];
    }

    /**
     * Calculate the cost for a participant (non-author)
     *
     * @param Profile $profile
     * @return array Contains final_price and currency
     */
    public static function calculateParticipantPrice(Profile $profile)
    {
        $price = self::priceRowFor($profile);

        return [
            'final_price' => $price ? $price->amountFor() : 0.0,
            'currency' => strtoupper($price->currency ?? 'USD')
        ];
    }

    /**
     * Recalculates the total amount due for a user and updates their profile.
     * This is the "Source of Truth" for profiles.pay_amount.
     *
     * @param Profile $profile
     * @return void
     */
    public static function updateProfileTotalDue(Profile $profile)
    {
        if ($profile->payment_status == '1') {
            return;
        }

        $totalAmount = 0;
        $currency = 'USD'; // Default

        if (!$profile->is_author) {
            // Logic for Participant Only
            $pricing = self::calculateParticipantPrice($profile);
            $totalAmount = $pricing['final_price'];
            $currency = $pricing['currency'];
        } else {
            // Logic for Author (Sum of all their unpaid papers)
            $papers = \App\Models\Paper::where('user_id', $profile->user_id)
                ->where(function($q) {
                    $q->whereNull('payment_status')
                      ->orWhere('payment_status', '!=', '1');
                })
                ->get();

            foreach ($papers as $paper) {
                $paperPricing = self::calculatePaperCost($profile, $paper);
                $totalAmount += $paperPricing['final_price'];
                $currency = $paperPricing['currency'];
            }
        }

        $profile->update([
            'pay_amount' => $totalAmount,
            'currency' => $currency
        ]);
    }

    /**
     * Users on an approved email domain pay a flat negotiated rate, but only where
     * that rate is actually lower than the tier they would otherwise pay.
     *
     * @return float
     */
    private static function applyDomainDiscount(Profile $profile, $basePrice)
    {
        $settings = Setting::pluck('value', 'key');

        if (($settings['special_discount_is_true'] ?? 'false') !== 'true') {
            return $basePrice;
        }

        $userEmail = $profile->user->email ?? '';
        $emailParts = explode('@', $userEmail);
        $domain = end($emailParts);

        $allowedDomains = Domain::where('status', 1)->pluck('domain_name')->toArray();

        if (!in_array($domain, $allowedDomains)) {
            return $basePrice;
        }

        $flatDomainDiscountPrice = (float) ($settings['selected_domain_discount'] ?? $basePrice);

        return $flatDomainDiscountPrice < $basePrice ? $flatDomainDiscountPrice : $basePrice;
    }
}
