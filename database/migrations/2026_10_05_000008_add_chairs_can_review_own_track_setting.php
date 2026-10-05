<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * chairs_can_review_own_track: 'true' lets a Track or Sub-Track Chair be assigned as a
 * reviewer in a track they chair (when reviewers are short). Off by default; a chair who
 * reviews a paper never decides on it (ChairScope::conflictWith).
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'chairs_can_review_own_track'], ['value' => 'false']);
    }

    public function down(): void
    {
        Setting::where('key', 'chairs_can_review_own_track')->delete();
    }
};
