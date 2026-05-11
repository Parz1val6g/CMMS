<?php

namespace App\Core\Forms\Fields;

use App\Core\Forms\FormField;

class ToggleInput extends FormField
{
    protected ?string $type = 'toggle';
    protected array $options = [];

    public function setOptions(array $options): static
    {
        if (empty($options)) {
            throw new \InvalidArgumentException('ToggleInput must have at least one option');
        }
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['options'] = $this->options;
        return $arr;
    }
}
