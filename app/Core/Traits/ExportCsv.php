<?php

namespace App\Core\Traits;

trait ExportCsv
{
    public static function exportToCsv(array $items, array $columns): string
    {
        $csv = implode(',', $columns) . "\n";

        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $column) {
                $value = data_get($item, $column);
                $row[] = is_string($value) ? '"' . str_replace('"', '""', $value) . '"' : $value;
            }
            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }
}
