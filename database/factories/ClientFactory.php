<?php

namespace Database\Factories;

use App\Features\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            // user_id must be provided via state() or seeder
            'nif' => fake()->unique()->numerify('#########'),
        ];
    }
}
