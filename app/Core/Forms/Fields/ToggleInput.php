<?php

namespace App\Core\Forms\Fields;

use App\Core\Forms\FormField;

class ToggleInput extends FormField
{
    protected ?string $type = 'checkbox';
    protected array $options = [];

    public function setOptions(array $options): static
    {
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
        if (!empty($this->options)) {
            $arr['options'] = $this->options;
        }
        return $arr;
    }
}
