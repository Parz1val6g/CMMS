<?php

namespace App\Core\Traits;

use App\Core\Services\FilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Provides a helper that decodes the JSON advanced-filter payload from the request
 * and delegates to FilterService::applyAdvanced().
 *
 * Usage in controllers:
 *   use FiltersAdvancedRules;
 *   ...
 *   $query = $this->applyAdvancedFilters($request, $query, $this->filterService, ['name', 'status']);
 */
trait FiltersAdvancedRules
{
    protected function applyAdvancedFilters(
        Request        $request,
        Builder        $query,
        FilterService  $filterService,
        array          $allowedFields = []
    ): Builder {
        $raw   = $request->input('adv_filters');
        $logic = $request->input('adv_logic', 'and');

        if (!$raw) {
            return $query;
        }

        $rules = json_decode($raw, true);

        if (!is_array($rules) || empty($rules)) {
            return $query;
        }

        return $filterService->applyAdvanced($query, $rules, $logic, $allowedFields);
    }

    /**
     * Apply advanced filter rules that target a `user` Eloquent relationship.
     * Used by Clients and Workers where display fields (name, email, phone) live on the users table.
     *
     * @param  array  $userFields  Which field names in the rule set map to user columns.
     *                             'name' is handled via CONCAT(first_name, ' ', last_name).
     */
    protected function applyUserRelationshipFilters(
        Request $request,
        Builder $query,
        array   $userFields = ['name', 'email', 'phone']
    ): void {
        $raw   = $request->input('adv_filters');
        $logic = $request->input('adv_logic', 'and');

        if (!$raw) return;

        $rules = json_decode($raw, true);
        if (!is_array($rules) || empty($rules)) return;

        $isOr = strtolower($logic) === 'or';

        foreach ($rules as $rule) {
            $field = $rule['field']    ?? null;
            $op    = $rule['operator'] ?? 'contains';
            $val   = $rule['value']    ?? '';

            if (!$field || !in_array($field, $userFields, true)) continue;

            $method = $isOr ? 'orWhereHas' : 'whereHas';

            $query->$method('user', function (Builder $q) use ($field, $op, $val) {
                if ($field === 'name') {
                    $expr = "CONCAT(first_name, ' ', last_name)";
                    match ($op) {
                        'contains'         => $q->whereRaw("{$expr} LIKE ?",      ['%' . $val . '%']),
                        'does_not_contain' => $q->whereRaw("{$expr} NOT LIKE ?",  ['%' . $val . '%']),
                        'is'               => $q->whereRaw("{$expr} = ?",          [$val]),
                        'is_not'           => $q->whereRaw("{$expr} != ?",         [$val]),
                        'starts_with'      => $q->whereRaw("{$expr} LIKE ?",      [$val . '%']),
                        'ends_with'        => $q->whereRaw("{$expr} LIKE ?",      ['%' . $val]),
                        'is_empty'         => $q->whereRaw("TRIM({$expr}) = ''"),
                        'is_not_empty'     => $q->whereRaw("TRIM({$expr}) != ''"),
                        default            => $q->whereRaw("{$expr} LIKE ?",      ['%' . $val . '%']),
                    };
                } else {
                    match ($op) {
                        'contains'         => $q->where($field, 'LIKE',     '%' . $val . '%'),
                        'does_not_contain' => $q->where($field, 'NOT LIKE', '%' . $val . '%'),
                        'is'               => $q->where($field, '=',         $val),
                        'is_not'           => $q->where($field, '!=',        $val),
                        'starts_with'      => $q->where($field, 'LIKE',     $val . '%'),
                        'ends_with'        => $q->where($field, 'LIKE',     '%' . $val),
                        'is_empty'         => $q->where(fn($sq) => $sq->whereNull($field)->orWhere($field, '=', '')),
                        'is_not_empty'     => $q->where(fn($sq) => $sq->whereNotNull($field)->where($field, '!=', '')),
                        default            => $q->where($field, 'LIKE',     '%' . $val . '%'),
                    };
                }
            });
        }
    }
}
