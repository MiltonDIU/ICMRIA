<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * attendance_change_enabled: 'true' lets authors change who attends after submitting the
 * abstract, up to paying (ProceedingsRules::attendanceChangeAllowed). On by default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(['key' => 'attendance_change_enabled'], ['value' => 'true']);
    }

    public function down(): void
    {
        Setting::where('key', 'attendance_change_enabled')->delete();
    }
};
