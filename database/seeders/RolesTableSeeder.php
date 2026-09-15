<?php
namespace Database\Seeders;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Role id 3 must stay 3: ~20 call sites gate the author path on
        // $user->roles->contains('id', 3) (PaperController, ProfileController, RegisterController).
        $roles = [
            [
                'id'         => 1,
                'title'      => 'SuperAdmin',
                'created_at' => '2019-09-24 19:16:02',
                'updated_at' => '2019-09-24 19:16:02',
            ],
            [
                'id'         => 2,
                'title'      => 'Admin',
                'created_at' => '2019-09-24 19:16:02',
                'updated_at' => '2019-09-24 19:16:02',
            ],
            [
                'id'         => 3,
                'title'      => 'Author',
                'created_at' => '2019-09-24 19:16:02',
                'updated_at' => '2019-09-24 19:16:02',
            ],
            [
                'id'    => 4,
                'title' => 'Reviewer',
            ],
            [
                'id'    => 5,
                'title' => 'Track Chair',
            ],
            [
                'id'    => 6,
                'title' => 'Sub-Track Chair',
            ],
            [
                'id'    => 7,
                'title' => 'TPC Chair',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }

        // This list is the source of truth for roles. Anything else is removed
        // outright: Role soft-deletes, and a trashed row would both keep granting
        // nothing useful and collide with updateOrCreate on the next seed.
        Role::withTrashed()
            ->whereNotIn('id', array_column($roles, 'id'))
            ->get()
            ->each(function (Role $role) {
                $role->permissions()->detach();
                $role->users()->detach();
                $role->forceDelete();
            });
    }
}
