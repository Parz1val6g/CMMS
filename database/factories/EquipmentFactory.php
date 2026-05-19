<?php

namespace Database\Factories;

use App\Core\Enums\EquipmentStatus;
use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    private const NAMES = [
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
    ];

    private const BRANDS = [
        'DeWalt', 'Makita', 'Bosch', 'Hilti', 'Stihl',
        'Atlas Copco', 'CAT', 'JCB', 'Volvo', 'Liebherr',
        'Honda', 'Bomag', 'Husqvarna', 'Grundfos', 'IMER',
    ];

    private const DESCRIPTIONS = [
        'Equipamento em boas condições de funcionamento. Revisões em dia conforme plano de manutenção preventiva.',
        'Máquina operacional com desgaste normal de utilização. Última revisão sem anomalias detetadas.',
        'Equipamento com pequenas marcas de uso exterior. Componentes internos em excelente estado.',
        'Unidade pronta para operação imediata. Calibração e afinação verificadas na última inspeção.',
        'Equipamento adquirido em 2021. Histórico de manutenção completo e sem ocorrências graves.',
        'Máquina robusta e fiável. Ideal para trabalhos de média e grande dimensão em obra.',
        'Equipamento compacto e versátil. Adequado para intervenções em zonas de acesso condicionado.',
        'Unidade em perfeito estado de conservação. Todos os sistemas operacionais com desempenho nominal.',
    ];

    public function definition(): array
    {
        return [
            'name'                => fake()->randomElement(self::NAMES) . ' - ' . fake()->randomNumber(4),
            'brand'               => fake()->randomElement(self::BRANDS),
            'model'               => strtoupper(fake()->bothify('??-####')),
            'serial_number'       => strtoupper(fake()->unique()->bothify('??-####-##')),
            'manager_id'          => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'status'              => fake()->randomElement(array_map(fn(EquipmentStatus $s) => $s->value, EquipmentStatus::cases())),
            'is_loanable'         => fake()->boolean(85),
            'revision_interval'   => fake()->randomElement([90, 180, 365, 730]),
            'last_revision_date'  => fake()->dateTimeBetween('-6 months', 'now'),
            'next_revision_date'  => fake()->dateTimeBetween('now', '+6 months'),
            'description'         => fake()->randomElement(self::DESCRIPTIONS),
            'cost_per_hour'       => fake()->randomFloat(2, 0, 150),
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
            'status' => EquipmentStatus::ACTIVE->value,
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
