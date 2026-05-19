<?php

namespace Database\Factories;

use App\Shared\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    private const ROLES = [
        'admin',
        'manager',
        'equipment_manager',
        'supervisor',
        'worker',
        'client',
        'entidade',
        'task_manager',
        'mini_task_manager',
        'work_log_manager',
        'sector_manager',
        'ticket_manager',
        'team_manager',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(self::ROLES),
        ];
    }
}
