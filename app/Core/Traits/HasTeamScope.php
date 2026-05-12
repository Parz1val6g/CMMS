<?php

namespace App\Core\Traits;

use App\Features\MiniTasks\Models\MiniTask;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasTeamScope
{
    /**
     * Scope query to records belonging to teams supervised by the given user.
     * For supervisor role — resolves teams through mini_tasks where user is supervisor.
     */
    public function scopeForSupervisor(Builder $query, User $user): Builder
    {
        $teamIds = DB::table('mini_tasks_workers_teams')
            ->join('mini_tasks', 'mini_tasks.id', '=', 'mini_tasks_workers_teams.mini_task_id')
            ->where('mini_tasks.supervisor_id', $user->id)
            ->whereNotNull('mini_tasks_workers_teams.team_id')
            ->distinct()
            ->pluck('mini_tasks_workers_teams.team_id');

        return $query->whereIn('team_id', $teamIds);
    }
}
