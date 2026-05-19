<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class NumberInput extends FormField
{
    protected ?string $type = 'number';
    protected ?float $min = null;
    protected ?float $max = null;
    protected ?float $step = null;

    public function __construct(string $key)
    {
        parent::__construct($key);
        $this->validationTiming = 'blur';
    }

    public function min(float $min): static
    {
        $this->min = $min;
        return $this;
    }

    public function max(float $max): static
    {
        $this->max = $max;
        return $this;
    }

    public function step(float $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function range(?float $min = null, ?float $max = null): static
    {
        if ($min !== null) $this->min = $min;
        if ($max !== null) $this->max = $max;
        return $this;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        if ($this->min !== null) $data['min'] = $this->min;
        if ($this->max !== null) $data['max'] = $this->max;
        if ($this->step !== null) $data['step'] = $this->step;
        return $data;
    }
}