<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keywords and expertise become JSON arrays.
 *
 * Both sides of the automatic reviewer matching now hold a list rather than one
 * comma-joined string, so overlap can be asked of the database directly
 * (JSON_OVERLAPS, MEMBER OF) instead of splitting text on every comparison. A
 * keyword containing a comma no longer breaks the value either.
 *
 * Existing rows are split on commas and written back as arrays.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convert('papers', 'keywords');
        $this->convert('track_assignments', 'expertise');
    }

    public function down(): void
    {
        $this->revert('papers', 'keywords', 'string');
        $this->revert('track_assignments', 'expertise', 'text');
    }

    private function convert(string $table, string $column): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
        Schema::table($table, fn (Blueprint $t) => $t->json($column)->nullable());

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update([
                $column => json_encode($this->toList($row->{$column})),
            ]);
        }
    }

    private function revert(string $table, string $column, string $type): void
    {
        $rows = DB::table($table)->select('id', $column)->get();

        Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
        Schema::table($table, function (Blueprint $t) use ($column, $type) {
            $type === 'text' ? $t->text($column)->nullable() : $t->string($column)->nullable();
        });

        foreach ($rows as $row) {
            $list = json_decode((string) $row->{$column}, true) ?: [];
            DB::table($table)->where('id', $row->id)->update([
                $column => implode(', ', $list),
            ]);
        }
    }

    /** @return array<int, string> */
    private function toList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        // Already an array of its own, from a partially applied run.
        $decoded = json_decode((string) $value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
};
