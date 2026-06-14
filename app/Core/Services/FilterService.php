<?php

namespace App\Core\Services;

use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    public function apply(Builder $query, array $filters = [], array $searchColumns = [], array $enumOrders = []): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $search) => $this->search($q, $search, $searchColumns))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['from_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['sort'] ?? null, fn($q, $sort) => $this->sort($q, $sort, $enumOrders));
    }

    public function search(Builder $query, string $term, array $columns = []): Builder
    {
        if (empty($columns)) {
            return $query;
        }

        return $query->where(function ($q) use ($term, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$term}%");
            }
        });
    }

    public function sort(Builder $query, string $sort, array $enumOrders = []): Builder
    {
        [$column, $direction] = str_contains($sort, ':')
            ? explode(':', $sort)
            : [$sort, 'asc'];

        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        if (isset($enumOrders[$column])) {
            $values = implode("','", $enumOrders[$column]);
            return $query->orderByRaw("FIELD($column, '$values') $direction");
        }

        return $query->orderBy($column, $direction);
    }

    public function paginate(Builder $query, int $perPage = 15, int $page = 1)
    {
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Apply an array of advanced filter rules (from the AdvancedFilterBuilder UI).
     *
     * Each rule is: { field: string, operator: string, value: string }
     * Rules are combined with AND or OR logic depending on the $logic parameter.
     *
     * @param  Builder  $query
     * @param  array    $rules         Array of rule objects from the frontend
     * @param  string   $logic         'and' | 'or'  — how rules are combined
     * @param  array    $allowedFields Whitelist of column names that may be filtered
     */
    public function applyAdvanced(Builder $query, array $rules, string $logic = 'and', array $allowedFields = []): Builder
    {
        if (empty($rules)) {
            return $query;
        }

        $isOr = strtolower($logic) === 'or';

        $query->where(function (Builder $q) use ($rules, $allowedFields, $isOr) {
            foreach ($rules as $rule) {
                $field    = $rule['field']    ?? null;
                $operator = $rule['operator'] ?? null;
                $value    = $rule['value']    ?? null;

                // Security: skip rules whose field is not in the allowedFields whitelist
                if (!$field || !$operator) {
                    continue;
                }

                if (!empty($allowedFields) && !in_array($field, $allowedFields, true)) {
                    continue;
                }

                $m = $isOr ? 'orWhere' : 'where';
                match ($operator) {
                    'contains'         => $q->$m($field, 'LIKE', '%' . $value . '%'),
                    'does_not_contain' => $q->$m($field, 'NOT LIKE', '%' . $value . '%'),
                    'is'               => $q->$m($field, '=', $value),
                    'is_not'           => $q->$m($field, '!=', $value),
                    'starts_with'      => $q->$m($field, 'LIKE', $value . '%'),
                    'ends_with'        => $q->$m($field, 'LIKE', '%' . $value),
                    'is_empty'         => $q->$m(fn($sq) => $sq->whereNull($field)->orWhere($field, '=', '')),
                    'is_not_empty'     => $q->$m(fn($sq) => $sq->whereNotNull($field)->where($field, '!=', '')),
                    default            => null,
                };
            }
        });

        return $query;
    }
}
