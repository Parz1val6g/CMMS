<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $search) => $this->filterSearch($q, $search))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['from_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['sort'] ?? null, fn($q, $sort) => $this->applySorting($q, $sort));
    }

    protected function filterSearch(Builder $query, string $search): Builder
    {
        $searchColumns = $this->getSearchableColumns();

        if (empty($searchColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $q->orWhere($column, 'LIKE', "%{$search}%");
            }
        });
    }

    protected function applySorting(Builder $query, string $sort): Builder
    {
        [$column, $direction] = str_contains($sort, ':')
            ? explode(':', $sort)
            : [$sort, 'asc'];

        $allowed = $this->getSortableColumns();
        $column = in_array($column, $allowed, true) ? $column : 'created_at';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        return $query->orderBy($column, $direction);
    }

    protected function getSearchableColumns(): array
    {
        return [];
    }

    protected function getSortableColumns(): array
    {
        return ['created_at', 'updated_at', 'status', 'priority'];
    }
}
