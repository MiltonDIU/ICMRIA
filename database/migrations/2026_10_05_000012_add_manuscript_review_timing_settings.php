<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Keeping the manuscript a reviewer reads from changing under them:
 *  - manuscript_locks_on_review: once a reviewer holds the paper, the author can no longer
 *    replace the manuscript (an administrator with paper_manuscript_manage still can);
 *  - review_waits_for_manuscript_deadline: reviewers are assigned only after
 *    manuscript_submission_end, when every author's final version is in.
 * Both on by default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'manuscript_locks_on_review'], ['value' => 'true']);
        Setting::firstOrCreate(['key' => 'review_waits_for_manuscript_deadline'], ['value' => 'true']);
    }

    public function down(): void
    {
        Setting::whereIn('key', ['manuscript_locks_on_review', 'review_waits_for_manuscript_deadline'])->delete();
    }
};
