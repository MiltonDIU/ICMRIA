<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * review_requires_manuscript: 'true' stops reviewers being assigned to a paper, by hand
 * or automatically, until its full manuscript is uploaded (SubmissionRules).
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'review_requires_manuscript'], ['value' => 'true']);
    }

    public function down(): void
    {
        Setting::where('key', 'review_requires_manuscript')->delete();
    }
};
