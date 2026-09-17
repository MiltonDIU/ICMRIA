<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('paper_authors', 'is_corresponding_author')) {
            Schema::table('paper_authors', function (Blueprint $table) {
                $table->boolean('is_corresponding_author')->default(false)->after('is_presenting_author');
            });

            // Backfill existing papers: if paper->is_corresponding_author was true, mark primary author (order 1) as corresponding
            DB::statement("
                UPDATE paper_authors pa
                JOIN papers p ON p.id = pa.paper_id
                SET pa.is_corresponding_author = 1
                WHERE pa.author_order = 1 AND p.is_corresponding_author = 1
            ");
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paper_authors', 'is_corresponding_author')) {
            Schema::table('paper_authors', function (Blueprint $table) {
                $table->dropColumn('is_corresponding_author');
            });
        }
    }
};
