<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * paper_authors.is_student now follows the delegate category (PaperAuthor::booted): the
 * Student tier means a student, any other tier does not. The fee was always charged on
 * the category, so this brings the flag on existing rows in line with what they pay.
 * Rows without a category keep their flag, since pricing still falls back to it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE paper_authors pa
            JOIN prices p ON p.id = pa.price_id
            SET pa.is_student = (p.category = 'student')
        ");
    }

    public function down(): void
    {
        // The previous flags were not kept, so there is nothing to restore.
    }
};
