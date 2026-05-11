<?php

namespace App\Core\Traits;

use App\Core\Services\NumeratorService;
use Illuminate\Support\Facades\App;

trait HasAutoReference
{
    public static function bootHasAutoReference(): void
    {
        static::creating(function (self $model) {
            $column = $model->referenceColumn();
            if (empty($model->{$column})) {
                $service = App::make(NumeratorService::class);
                $model->{$column} = $service->format(
                    $model->referenceInitials(),
                    $model->getTable()
                );
            }
        });
    }

    protected function referenceColumn(): string
    {
        return 'reference';
    }

    protected function referenceInitials(): string
    {
        return strtoupper(substr(class_basename(static::class), 0, 2));
    }
}
