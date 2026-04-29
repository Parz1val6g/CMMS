<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Shared\Models\User;
use App\Shared\Models\Role;
use Database\Seeders\RoleSeeder;
use Database\Seeders\GeographicDataSeeder;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    protected User $user;
    protected User $admin;
    protected User $manager;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions, geographic data
        $this->seed([
            RoleSeeder::class,
            GeographicDataSeeder::class,
        ]);

        // Create default test user (manager role)
        $this->user = User::factory()->create();
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $this->user->roles()->attach($managerRole);
        }

        // Create additional test users for authorization tests
        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();
        $this->worker = User::factory()->create();

        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $workerRole = Role::where('name', 'worker')->first();

        if ($adminRole) $this->admin->roles()->attach($adminRole);
        if ($managerRole) $this->manager->roles()->attach($managerRole);
        if ($workerRole) $this->worker->roles()->attach($workerRole);
    }

    /**
     * Create a user with specified role(s)
     * @param string|array $roles - Single role name or array of role names
     */
    protected function createUser($roles = 'manager'): User
    {
        $user = User::factory()->create();

        foreach ((array)$roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->roles()->attach($role);
            }
        }

        return $user;
    }

    /**
     * Authenticate request as specific user (using Sanctum)
     */
    protected function actingAsUser(User $user = null): static
    {
        return $this->actingAs($user ?? $this->user, 'sanctum');
    }
}
