<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Permissions for the "Message Categories" (conference_message_categories) admin module.
     * Attached to SuperAdmin (role 1) and Admin (role 2).
     */
    public function up(): void
    {
        $titles = [
            'conference_message_category_access',
            'conference_message_category_create',
            'conference_message_category_edit',
            'conference_message_category_show',
            'conference_message_category_delete',
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
            'conference_message_category_access',
            'conference_message_category_create',
            'conference_message_category_edit',
            'conference_message_category_show',
            'conference_message_category_delete',
        ])->delete();
    }
};