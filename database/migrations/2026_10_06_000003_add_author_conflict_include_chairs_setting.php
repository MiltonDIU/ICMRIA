<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * author_conflict_include_chairs (default true, as before): whether authors are offered
 * the track's chairs and co-chairs, as well as its reviewers, when declaring a conflict
 * of interest. Set to false and they name reviewers only; conflicts already declared
 * still apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'author_conflict_include_chairs'], ['value' => 'true']);
    }

    public function down(): void
    {
        Setting::where('key', 'author_conflict_include_chairs')->delete();
    }
};
