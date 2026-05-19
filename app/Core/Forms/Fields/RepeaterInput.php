<?php

namespace App\Core\Forms\Fields;

use App\Core\Forms\FormField;

class RepeaterInput extends FormField
{
    protected ?string $type = 'repeater';
    protected array $subFields = [];
    protected int $maxItems = 10;

    /**
     * Adiciona um campo à linha repetível.
     *
     * @param FormField $field
     * @return $this
     */
    public function subField(FormField $field): static
    {
        $this->subFields[$field->getKey()] = $field;
        return $this;
    }

    /**
     * Adiciona múltiplos campos à linha repetível.
     *
     * @param FormField[] $fields
     * @return $this
     */
    public function subFields(array $fields): static
    {
        foreach ($fields as $field) {
            $this->subField($field);
        }
        return $this;
    }

    public function getSubFields(): array
    {
        return $this->subFields;
    }

    /**
     * Define o número máximo de itens.
     *
     * @param int $max
     * @return $this
     */
    public function setMaxItems(int $max): static
    {
        if ($max < 1) {
            throw new \InvalidArgumentException('maxItems must be at least 1');
        }
        $this->maxItems = $max;
        return $this;
    }

    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['subFields'] = array_values(array_map(
            fn(FormField $f) => $f->toArray(),
            $this->subFields
        ));
        $arr['maxItems'] = $this->maxItems;
        return $arr;
    }
}
