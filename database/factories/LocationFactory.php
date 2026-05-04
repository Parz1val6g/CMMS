<?php

namespace Database\Factories;

use App\Features\Locations\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            // parish_id must be provided via state() or seeder
            'postal_code'    => sprintf('%04d-%03d', fake()->numberBetween(1000, 9999), fake()->numberBetween(1, 999)),
            'street_address' => fake()->streetName() . ', nº ' . fake()->numberBetween(1, 500),
            'landmark'       => fake()->text(30),
            'latitude'       => fake()->latitude(36.9, 42.2),
            'longitude'      => fake()->longitude(-9.5, -6.2),
        ];
    }
}
