<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionRegistrar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full system access']
        );

        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Administrative access']
        );

        $distributor = Role::updateOrCreate(
            ['slug' => 'distributor'],
            ['name' => 'Distributor', 'description' => 'Default distributor access']
        );

        $salesman = Role::updateOrCreate(
            ['slug' => 'salesman'],
            ['name' => 'Salesman', 'description' => 'Default salesman staff access']
        );

        $registrar = app(PermissionRegistrar::class);
        $registrar->syncPermissions();

        $allPermissionIds = Permission::pluck('id');
        $superAdmin->permissions()->sync($allPermissionIds);
        $admin->permissions()->sync($allPermissionIds);

        $nonRbacPermissionIds = Permission::where('slug', 'not like', 'user-management-%')->pluck('id');
        $distributor->permissions()->sync($nonRbacPermissionIds);
        $salesman->permissions()->sync($nonRbacPermissionIds);
    }
}
