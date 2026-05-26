<?php

namespace App\Core\Forms\Fields;

use App\Core\Forms\FormField;

class DateRangeInput extends FormField
{
    protected ?string $type = 'date-picker';

    public function __construct(string $key)
    {
        parent::__construct($key);
        $this->meta('dateMode', 'range');
    }

    public function setDateMode(string $mode): static
    {
        return $this->meta('dateMode', $mode);
    }

    /** @param string[] $dates YYYY-MM-DD strings */
    public function setBlockedDates(array $dates): static
    {
        return $this->meta('blockedDates', $dates);
    }

    public function setMinDate(string $date): static
    {
        return $this->meta('minDate', $date);
    }

    public function setMaxDate(string $date): static
    {
        return $this->meta('maxDate', $date);
    }
}
