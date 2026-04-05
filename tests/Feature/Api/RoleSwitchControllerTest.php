<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSwitchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createUserWithRoles(array $roles): User
    {
        $user = User::create([
            'name' => 'Role User',
            'email' => 'roles@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'locale' => 'en',
            'active_role' => 'student',
        ]);

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'student');
            $user->assignRole($roleName);
        }

        return $user;
    }

    public function test_roles_endpoint_returns_active_and_available_roles(): void
    {
        $user = $this->createUserWithRoles(['Student', 'HR']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/roles')
            ->assertOk()
            ->assertJsonPath('data.active_role', 'student')
            ->assertJsonPath('data.available_roles.0', 'student')
            ->assertJsonPath('data.available_roles.1', 'hr');
    }

    public function test_switch_role_updates_active_role_when_role_is_assigned(): void
    {
        $user = $this->createUserWithRoles(['Student', 'HR']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/roles/switch', [
            'role' => 'hr',
        ])->assertOk()
            ->assertJsonPath('data.active_role', 'hr');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'active_role' => 'hr',
        ]);
    }

    public function test_switch_role_rejects_roles_the_user_does_not_have(): void
    {
        $user = $this->createUserWithRoles(['Student']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/roles/switch', [
            'role' => 'organization',
        ])->assertForbidden();
    }
}
