<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * author_conflict_declaration_enabled: 'false' stops authors naming conflicts of interest
 * themselves (ConflictCandidates::authorsMayDeclare). On by default, as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'author_conflict_declaration_enabled'], ['value' => 'true']);
    }

    public function down(): void
    {
        Setting::where('key', 'author_conflict_declaration_enabled')->delete();
    }
};
