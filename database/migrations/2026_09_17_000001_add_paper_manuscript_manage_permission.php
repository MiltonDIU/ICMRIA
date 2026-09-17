<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Dedicated permission for administrative manuscript management/override.
     * Attached to SuperAdmin (role 1) and Admin (role 2).
     */
    public function up(): void
    {
        $permission = Permission::updateOrCreate(
            ['title' => 'paper_manuscript_manage'],
            ['title' => 'paper_manuscript_manage']
        );

        // Kept off by default for all roles; must be explicitly enabled via Role Permissions if administrative override is needed.
    }

    public function down(): void
    {
        Permission::where('title', 'paper_manuscript_manage')->delete();
    }
};