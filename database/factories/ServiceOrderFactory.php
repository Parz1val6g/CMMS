<?php

namespace Database\Factories;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\ServicesOrdersPriority;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        return [
            'process' => 'OS/' . fake()->year() . '/' . fake()->unique()->numberBetween(1000, 9999),
            'client_id' => Client::factory(),
            'manager_id' => User::factory(),
            'location_id' => Location::factory(),
            'service_type_id' => ServiceType::factory(),
            'priority' => fake()->randomElement(ServicesOrdersPriority::cases())->value,
            'execution_date' => fake()->dateTimeBetween('-3 months', '+1 month'),
            'status' => fake()->randomElement(ServiceOrderStatus::cases())->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $a) => ['status' => ServiceOrderStatus::PENDING->value]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $a) => ['status' => ServiceOrderStatus::IN_PROGRESS->value]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $a) => ['status' => ServiceOrderStatus::COMPLETED->value]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $a) => ['priority' => ServicesOrdersPriority::URGENT->value]);
    }
}
