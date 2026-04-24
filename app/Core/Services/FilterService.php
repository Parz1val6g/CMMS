<?php

namespace App\Core\Services;

use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    public function apply(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $search) => $this->search($q, $search))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['from_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['sort'] ?? null, fn($q, $sort) => $this->sort($q, $sort));
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

    public function sort(Builder $query, string $sort): Builder
    {
        [$column, $direction] = str_contains($sort, ':')
            ? explode(':', $sort)
            : [$sort, 'asc'];

        return $query->orderBy($column, in_array($direction, ['asc', 'desc']) ? $direction : 'asc');
    }

    public function paginate(Builder $query, int $perPage = 15, int $page = 1)
    {
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
