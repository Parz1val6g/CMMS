<?php

namespace App\Core\Forms;

class FieldCondition
{
    private const ALLOWED_OPERATORS = ['==', '!=', '<', '>', '<=', '>='];

    public function __construct(
        public readonly string $field,
        public readonly string $operator,
        public readonly mixed $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!in_array($this->operator, self::ALLOWED_OPERATORS)) {
            throw new \InvalidArgumentException(
                "Operador inválido: '{$this->operator}'. Operadores permitidos: " . 
                implode(', ', self::ALLOWED_OPERATORS)
            );
        }
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }

    public function evaluate(mixed $fieldValue): bool
    {
        return match ($this->operator) {
            '==' => $fieldValue === $this->value,
            '!=' => $fieldValue !== $this->value,
            '<' => $fieldValue < $this->value,
            '>' => $fieldValue > $this->value,
            '<=' => $fieldValue <= $this->value,
            '>=' => $fieldValue >= $this->value,
            default => false,
        };
    }
}
