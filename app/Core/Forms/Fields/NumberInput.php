<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class NumberInput extends FormField
{
    protected ?string $type = 'number';

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for number fields
        $this->validationTiming = 'blur'; // Validate on blur only
    }

    /**
     * Set min and max constraints via metadata.
     */
    public function range(?int $min = null, ?int $max = null): static
    {
        if ($min !== null) {
            $this->meta('min', $min);
        }
        if ($max !== null) {
            $this->meta('max', $max);
        }
        return $this;
    }
}