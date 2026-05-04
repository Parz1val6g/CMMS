<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class EmailInput extends FormField
{
    protected ?string $type = 'email';

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for email fields
        $this->validationTiming = 'both'; // Validate on blur + debounce while typing
        $this->helperText = 'Enter a valid email address (example@company.com)';
        $this->helpExamples = ['example@company.com', 'john.doe@example.com'];
    }
}