<?php
namespace Database\Seeders;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        $admin_permissions = Permission::all();
        Role::findOrFail(1)->permissions()->sync($admin_permissions->pluck('id'));
        $user_permissions = $admin_permissions->filter(function ($permission) {
            return substr($permission->title, 0, 5) != 'user_' && substr($permission->title, 0, 5) != 'role_' && substr($permission->title, 0, 11) != 'permission_';
        });
        Role::findOrFail(2)->permissions()->sync($user_permissions);

        // Chairs review the reviewers' evaluations and decide; they never author a
        // review themselves, so no role below gets both review_submit and decision_make.
        $chair_permissions = [
            'admin_dashboard',
            'profile',
            'profile_edit',
            'paper_access',
            'paper_show',
            'review_access',
            'review_assign',
            'decision_access',
            'decision_make',
        ];

        $role_permissions = [
            // Author: register, submit abstracts, edit their own until the deadline,
            // and pay. Every gate below is one an author-facing screen actually
            // checks; anything not listed keeps the admin menu hidden from them.
            //
            //   admin_dashboard  reach the panel at all
            //   profile          "My Profile" in the menu
            //   profile_edit     edit own profile, confirm student status
            //   paper_access     the Papers menu item (menu.blade.php) and the
            //                    papers list; PaperController takes the role-3
            //                    branch first, so they still only see their own
            //   paper_create     submit a new abstract
            //   paper_show       open one of their papers
            //   paper_edit       revise it while it is pending and the window is open
            //
            // Deliberately withheld: paper_delete (authors never remove a submission)
            // and abstract_review (the approve/reject control on papers/show).
            // Payment needs no permission: those routes are gated by auth only.
            3 => [
                'admin_dashboard',
                'profile',
                'profile_edit',
                'paper_access',
                'paper_create',
                'paper_show',
                'paper_edit',
            ],
            // Reviewer
            4 => [
                'admin_dashboard',
                'profile',
                'profile_edit',
                'paper_show',
                'review_access',
                'review_submit',
            ],
            5 => $chair_permissions, // Track Chair
            6 => $chair_permissions, // Sub-Track Chair
            // TPC Chair: final approval and full visibility only — cannot assign
            // reviewers, cannot review, cannot enter a decision.
            7 => [
                'admin_dashboard',
                'profile',
                'profile_edit',
                'paper_access',
                'paper_show',
                'review_access',
                'decision_access',
                'final_approval',
            ],
        ];

        foreach ($role_permissions as $role_id => $titles) {
            Role::findOrFail($role_id)
                ->permissions()
                ->sync(Permission::whereIn('title', $titles)->pluck('id'));
        }
    }
}
