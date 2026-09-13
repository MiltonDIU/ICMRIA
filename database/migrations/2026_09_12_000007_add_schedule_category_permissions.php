<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Permissions for the "Schedule Categories" (schedule_categories) admin module.
     * Attached to SuperAdmin (role 1) and Admin (role 2).
     */
    public function up(): void
    {
        $titles = [
            'schedule_category_access',
            'schedule_category_create',
            'schedule_category_edit',
            'schedule_category_show',
            'schedule_category_delete',
        ];

        $ids = [];
        foreach ($titles as $title) {
            $ids[] = Permission::updateOrCreate(['title' => $title], ['title' => $title])->id;
        }

        foreach ([1, 2] as $roleId) {
            if ($role = Role::find($roleId)) {
                $role->permissions()->syncWithoutDetaching($ids);
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'schedule_category_access',
            'schedule_category_create',
            'schedule_category_edit',
            'schedule_category_show',
            'schedule_category_delete',
        ])->delete();
    }
};
