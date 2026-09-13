<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Permissions for the "Messages" (conference_messages) admin module.
     * Attached to SuperAdmin (role 1) and Admin (role 2) so the module is
     * usable on existing databases without re-running the full seeders.
     */
    public function up(): void
    {
        $titles = [
            'conference_message_access',
            'conference_message_create',
            'conference_message_edit',
            'conference_message_show',
            'conference_message_delete',
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
            'conference_message_access',
            'conference_message_create',
            'conference_message_edit',
            'conference_message_show',
            'conference_message_delete',
        ])->delete();
    }
};
