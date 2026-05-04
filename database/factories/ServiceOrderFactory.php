<?php

namespace Database\Factories;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\Priority;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        $executionDate = fake()->dateTimeBetween('-3 months', '+1 month');
        // Avoid DST spring-forward gap (Europe/Lisbon: Mar last Sun 01:00-02:00)
        if ($executionDate->format('Y-m-d') === '2026-03-29' && $executionDate->format('H') === '01') {
            $executionDate->modify('+1 hour');
        }

        return [
            // client_id, manager_id, location_id, service_type_id must be provided via state()
            'process'        => 'OS/' . fake()->year() . '/' . fake()->unique()->numberBetween(1000, 9999),
            'priority'       => fake()->randomElement(Priority::cases())->value,
            'execution_date' => $executionDate,
            'status'         => fake()->randomElement(ServiceOrderStatus::cases())->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $a) => ['status' => ServiceOrderStatus::PENDING->value]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $a) => ['status' => ServiceOrderStatus::IN_PROGRESS->value]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $a) => ['status' => ServiceOrderStatus::COMPLETED->value]);
    }

    public function urgent(): static
    {
        return $this->state(fn(array $a) => ['priority' => Priority::URGENT->value]);
    }
}
