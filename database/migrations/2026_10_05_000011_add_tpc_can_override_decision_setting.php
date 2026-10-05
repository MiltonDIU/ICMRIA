<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * tpc_can_override_decision: 'true' lets the TPC Chair set a paper's decision themselves
 * (Accept, Minor Revisions or Reject) and approve it on Final Approval. Off by default,
 * as the requirement document has the Track Chair recommend and the TPC Chair approve.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'tpc_can_override_decision'], ['value' => 'false']);
    }

    public function down(): void
    {
        Setting::where('key', 'tpc_can_override_decision')->delete();
    }
};
