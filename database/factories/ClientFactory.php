<?php

namespace Database\Factories;

use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
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
            'user_id' => User::factory(),
            'nif' => fake()->unique()->numerify('#########'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Client $client) {
            $user = $client->user;
            $company = fake()->company();
            $name = explode(' ', $company);
            $user->update([
                'first_name' => $name[0] ?? $company,
                'last_name' => $name[1] ?? 'Lda',
                'email' => 'cliente.' . strtolower(str_replace(' ', '', $company)) . '@example.pt',
            ]);
        });
    }
}
