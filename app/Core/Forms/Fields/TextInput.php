<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class TextInput extends FormField
{
    protected ?string $type = 'text';

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for text fields
        $this->validationTiming = 'blur'; // Validate on blur
    }
}