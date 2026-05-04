<?php

namespace Database\Factories;

use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Escavadora CAT 320',
                'Compressor de Ar Atlas Copco',
                'Martelo Pneumático',
                'Bomba de Água Diesel',
                'Gerador 250 kVA',
                'Retroescavadora JCB',
                'Vibrador de Placas',
                'Broca Rotativa',
                'Serrador Circular',
                'Betoneira 350L',
                'Grua Telescópica 25T',
                'Pá Carregadora Volvo',
                'Cilindro Compressor 20T',
                'Cortador de Asfalto',
                'Perfurador Pneumático',
            ]) . ' - ' . $this->faker->randomNumber(4),
            'serial_number' => strtoupper($this->faker->unique()->bothify('??-####-##')),
            'manager_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance', 'archived']),
            'is_loanable' => $this->faker->boolean(85), // 85% likely to be loanable
            'revision_interval_days' => $this->faker->randomElement([90, 180, 365, 730]), // 3 months, 6 months, 1 year, 2 years
            'last_revision_date' => (function () {
                $dt = $this->faker->dateTimeBetween('-6 months', 'now');
                if ($dt->format('Y-m-d') === '2026-03-29' && $dt->format('H') === '01') {
                    $dt->modify('+1 hour');
                }
                return $dt;
            })(),
            'next_revision_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'description' => $this->faker->sentence(10),
        ];
    }

    /**
     * Equipment is loanable
     */
    public function loanable(): self
    {
        return $this->state([
            'is_loanable' => true,
        ]);
    }

    /**
     * Equipment is NOT loanable (company tools only)
     */
    public function notLoanable(): self
    {
        return $this->state([
            'is_loanable' => false,
        ]);
    }

    /**
     * Equipment in active status
     */
    public function active(): self
    {
        return $this->state([
            'status' => 'active',
        ]);
    }

    /**
     * Equipment requiring revision soon
     */
    public function needsRevision(): self
    {
        return $this->state([
            'next_revision_date' => now()->addDays(5),
        ]);
    }
}
