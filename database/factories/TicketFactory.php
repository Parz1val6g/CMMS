<?php

namespace Database\Factories;

use App\Core\Enums\TicketPriority;
use App\Core\Enums\TicketStatus;
use App\Features\Tickets\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    private const DESCRIPTIONS = [
        'Solicito reparação de pavimento danificado na via pública em frente ao nº 25. O buraco representa perigo para peões e veículos.',
        'Luminária pública apagada há mais de uma semana na Rua do Comércio. Solicitamos substituição urgente da lâmpada.',
        'Contentor de resíduos a transbordar na Praça do Município. Necessário reforço da frequência de recolha neste local.',
        'Árvore de grande porte com ramos partidos sobre a via pública após temporal. Risco de queda iminente.',
        'Tampa de saneamento partida na Avenida da Liberdade, junto à passadeira. Perigo para transeuntes.',
        'Fuga de água visível na conduta da Rua Direita. Caudal significativo a correr pela via há 3 dias.',
        'Solicito pintura de passadeira apagada em frente à Escola Primária. A segurança das crianças está em causa.',
        'Buraco na calçada portuguesa no Largo da Igreja. Vários peões já tropeçaram no local.',
        'Placa de sinalização vertical tombada no cruzamento da Rua Nova com a Travessa do Rossio.',
        'Solicito poda de árvores na Alameda do Jardim Público. Ramos estão a tocar nos fios elétricos.',
        'Sarjeta entupida na Rua da Fonte causa acumulação de água sempre que chove. Necessário desobstrução.',
        'Resíduos volumosos (monos) abandonados junto ao ecoponto do Bairro da Ponte. Solicito recolha urgente.',
    ];

    public function definition(): array
    {
        return [
            // ticket_manager_id must be provided via state() or afterCreating()
            'description' => fake()->randomElement(self::DESCRIPTIONS),
            'priority'    => fake()->randomElement(TicketPriority::cases())->value,
            'status'      => fake()->randomElement(TicketStatus::cases())->value,
        ];
    }

    public function open(): static
    {
        return $this->state(fn(array $a) => ['status' => TicketStatus::OPEN->value]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $a) => ['status' => TicketStatus::IN_PROGRESS->value]);
    }

    public function converted(): static
    {
        return $this->state(fn(array $a) => ['status' => TicketStatus::CONVERTED->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $a) => ['status' => TicketStatus::CANCELLED->value]);
    }
}
