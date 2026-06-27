<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('created_at', 'desc')->get();
        return view('rbac.roles.index', compact('roles'));
    }

    public function create()
    {
        $registrar = app(PermissionRegistrar::class);
        $modules = $registrar->analyzeAndGenerate();
        $permissions = Permission::all()->groupBy('module');
        return view('rbac.roles.create', compact('modules', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->permissions) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('rbac.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->loadCount('users', 'permissions');
        $modules = app(PermissionRegistrar::class)->analyzeAndGenerate();
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('rbac.roles.show', compact('role', 'modules', 'permissions', 'rolePermissions'));
    }

    public function edit(Role $role)
    {
        $registrar = app(PermissionRegistrar::class);
        $modules = $registrar->analyzeAndGenerate();
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('rbac.roles.edit', compact('role', 'modules', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->permissions) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('rbac.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'super-admin') {
            return redirect()->route('rbac.roles.index')->with('error', 'Cannot delete Super Admin role.');
        }
        $role->permissions()->detach();
        $role->delete();
        return redirect()->route('rbac.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function duplicate(Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name . ' (Copy)',
            'slug' => Str::slug($role->name . '-copy'),
            'description' => $role->description,
        ]);
        $newRole->permissions()->sync($role->permissions->pluck('id'));
        return redirect()->route('rbac.roles.index')->with('success', 'Role duplicated successfully.');
    }
}
