<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\PermissionRegistrar;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $registrar = app(PermissionRegistrar::class);
        $modules = $registrar->analyzeAndGenerate();
        $permissions = Permission::all()->groupBy('module');
        return view('rbac.permissions.index', compact('modules', 'permissions'));
    }

    public function sync()
    {
        $registrar = app(PermissionRegistrar::class);
        $modules = $registrar->syncPermissions();
        $count = Permission::count();
        return redirect()->route('rbac.permissions.index')
            ->with('success', "Permissions synced successfully. Total: {$count} permissions across " . count($modules) . " modules.");
    }
}
