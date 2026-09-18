<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Gate;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
class RolesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('role_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::all();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $permissions = Permission::all()->pluck('title', 'id');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create($request->all());
        $role->permissions()->sync($request->input('permissions', []));
        $this->logPermissionChange($role, []);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        abort_if(Gate::denies('role_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $permissions = Permission::all()->pluck('title', 'id');

        $role->load('permissions');

        return view('admin.roles.edit', compact('permissions', 'role'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $before = $role->permissions->pluck('title')->all();

        $role->update($request->all());
        $role->permissions()->sync($request->input('permissions', []));
        $this->logPermissionChange($role, $before);

        return redirect()->route('admin.roles.index');
    }

    /**
     * Permissions live on a pivot table and a sync fires no model event. Granting a role
     * the right to decide on papers is worth a trace, so it is written by hand; only what
     * moved is recorded, since a full list of sixty permissions reads as nothing at all.
     */
    private function logPermissionChange(Role $role, array $before): void
    {
        $after = $role->load('permissions')->permissions->pluck('title')->all();

        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        if (!$added && !$removed) {
            return;
        }

        activity()
            ->performedOn($role)
            ->event('updated')
            ->withChanges(['attributes' => [
                'permissions_added' => implode(', ', $added) ?: '—',
                'permissions_removed' => implode(', ', $removed) ?: '—',
            ]])
            ->log('Permissions changed');
    }

    public function show(Role $role)
    {
        abort_if(Gate::denies('role_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $role->load('permissions');

        return view('admin.roles.show', compact('role'));
    }

    public function destroy(Role $role)
    {
        abort_if(Gate::denies('role_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $role->delete();

        return back();
    }

    public function massDestroy(MassDestroyRoleRequest $request)
    {
        Role::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
