<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * reviewer_chair_can_decide: 'true' lets a chair who reviewed a paper still enter its
 * decision. Off by default, so another chair decides (ChairScope::conflictWith).
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'reviewer_chair_can_decide'], ['value' => 'false']);
    }

    public function down(): void
    {
        Setting::where('key', 'reviewer_chair_can_decide')->delete();
    }
};
