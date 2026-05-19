<?php

namespace Database\Factories;

use App\Core\Enums\LoanOrderStatus;
use App\Features\LoanOrders\Models\LoanOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanOrder>
 */
class LoanOrderFactory extends Factory
{
    protected $model = LoanOrder::class;

    private const DESCRIPTIONS = [
        'Pedido de empréstimo de compressor e martelo pneumático para demolição de estrutura na Rua Direita.',
        'Empréstimo de betoneira 350L para obras de requalificação do Largo da Igreja Matriz.',
        'Empréstimo de gerador portátil para evento solidário na Praça do Município.',
        'Empréstimo de bomba de água submersível para operações de escoamento na zona baixa da cidade.',
        'Empréstimo de cortadora de asfalto para trabalhos de repavimentação na Avenida da Europa.',
        'Empréstimo de vibrador de placas para compactação de passeios na Rua Principal.',
        'Empréstimo de compressor Atlas Copco XAS 185 para obra de saneamento na Zona Industrial.',
        'Empréstimo de motobomba diesel Honda WT40X para prevenção de incêndios florestais.',
        'Pedido de empréstimo de perfuradora para trabalhos de sondagem no Bairro do Castelo.',
        'Empréstimo de placa vibratória para compactação de base de pavimento em estrada rural.',
    ];

    public function definition(): array
    {
        return [
            // entity_id created inline for test backward-compatibility;
            // manager_id must be provided via state() or create()
            'entity_id'   => \App\Features\Entities\Models\Entity::factory()->create()->id,
            'status'      => LoanOrderStatus::PENDING->value,
            'description' => fake()->randomElement(self::DESCRIPTIONS),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $a) => ['status' => LoanOrderStatus::PENDING->value]);
    }

    public function approved(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => LoanOrderStatus::APPROVED->value,
            'approved_at' => now(),
        ]);
    }

    public function checkedOut(): static
    {
        return $this->state(fn(array $a) => [
            'status'         => LoanOrderStatus::CHECKED_OUT->value,
            'checked_out_at' => now(),
            'approved_at'    => now()->subDay(),
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn(array $a) => [
            'status'         => LoanOrderStatus::RETURNED->value,
            'returned_at'    => now(),
            'checked_out_at' => now()->subDays(20),
            'approved_at'    => now()->subDays(21),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $a) => [
            'status'       => LoanOrderStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);
    }
}
