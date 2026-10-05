<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * manual_payment_enabled: 'true' lets authors report a bank or mobile transfer with a
 * receipt for an admin to verify. Off by default, since OneCard takes every payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'manual_payment_enabled'], ['value' => 'false']);
    }

    public function down(): void
    {
        Setting::where('key', 'manual_payment_enabled')->delete();
    }
};
