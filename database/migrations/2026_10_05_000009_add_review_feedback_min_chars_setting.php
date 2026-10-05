<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * review_feedback_min_chars: the shortest "feedback for authors" a reviewer may submit.
 * 0 (the default) only requires it not to be empty, replacing the fixed 50-character rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'review_feedback_min_chars'], ['value' => '0']);
    }

    public function down(): void
    {
        Setting::where('key', 'review_feedback_min_chars')->delete();
    }
};
