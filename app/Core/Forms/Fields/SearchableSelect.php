<?php

namespace App\Core\Forms\Fields;

use App\Core\Forms\FormField;

class SearchableSelect extends FormField
{
    protected ?string $type = 'searchable-select';
    protected array $options = [];

    public function __construct(string $key)
    {
        parent::__construct($key);
        $this->validationTiming = 'blur';
    }

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
        $arr['options'] = $this->options;
        return $arr;
    }
}
