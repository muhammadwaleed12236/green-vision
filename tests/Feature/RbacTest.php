<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacTest extends TestCase
{
    public function test_user_creation_via_rbac(): void
    {
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $adminUser = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'usertype' => 'admin',
        ]);

        $role = Role::updateOrCreate(
            ['slug' => 'test-role'],
            ['name' => 'Test Role']
        );

        $response = $this->actingAs($adminUser)->post(route('rbac.users.store'), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('rbac.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
        ]);
    }

    public function test_user_status_toggling_via_rbac(): void
    {
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $adminUser = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'usertype' => 'admin',
        ]);

        $targetUser = User::factory()->create([
            'status' => 'active',
            'role_id' => $superAdminRole->id,
        ]);

        $response = $this->actingAs($adminUser)->get(route('rbac.users.toggle-status', $targetUser));

        $response->assertRedirect(route('rbac.users.index'));
        $this->assertEquals('inactive', $targetUser->fresh()->status);
    }

    public function test_user_password_reset_via_rbac(): void
    {
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $adminUser = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'usertype' => 'admin',
        ]);

        $targetUser = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        $response = $this->actingAs($adminUser)->post(route('rbac.users.reset-password', $targetUser), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('rbac.users.index'));
        $this->assertTrue(Hash::check('newpassword123', $targetUser->fresh()->password));
    }
}
