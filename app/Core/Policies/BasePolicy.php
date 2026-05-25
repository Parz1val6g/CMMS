<?php

namespace App\Core\Policies;

use App\Shared\Models\User;

class BasePolicy
{
    /**
     * Per-request permission cache — avoids N+1 on listing endpoints.
     * @var array<string, bool>
     */
    private array $permCache = [];

    public function before(User $user, string $ability): ?bool
    {
        // No hardcoded admin bypass — admin is verified like any other role.
        // The seeder gives admin all resources × all actions, so hasPermission()
        // will return true for admin. This makes permissions data-driven.
        return null;
    }

    /**
     * Returns the active_role from session, or null for token-based (API) requests.
     */
    private function getActiveRole(): ?string
    {
        return request()->hasSession() ? request()->session()->get('active_role') : null;
    }

    /**
     * Admin check respects active_role: a user who selected a non-admin role
     * does not get admin powers for that session.
     */
    protected function isAdmin(User $user): bool
    {
        $activeRole = $this->getActiveRole();

        if ($activeRole !== null && $activeRole !== 'admin') {
            return false;
        }

        $key = 'admin:' . $user->id;

        if (!isset($this->permCache[$key])) {
            $this->permCache[$key] = $user->roles()->where('name', 'admin')->exists();
        }

        return $this->permCache[$key];
    }

    protected function isOwner(User $user, ?User $owner): bool
    {
        return $owner !== null && $user->id === $owner->id;
    }

    /**
     * Permission check scoped to active_role.
     * If no active_role is set (e.g. token-based API), checks all roles.
     */
    protected function hasPermission(User $user, string $action, string $resource): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $activeRole = $this->getActiveRole();
        $key = $user->id . ':' . ($activeRole ?? 'all') . ':' . $action . '@' . $resource;

        if (!isset($this->permCache[$key])) {
            $query = $user->roles();

            if ($activeRole) {
                $query = $query->where('name', $activeRole);
            }

            $this->permCache[$key] = $query->whereHas('permissions', function ($q) use ($action, $resource) {
                $q->where('resource', $resource)->where('action', $action);
            })->exists();
        }

        return $this->permCache[$key];
    }

    protected function isManagerScoped(User $user, ?User $manager): bool
    {
        return $manager !== null
            && $user->id === $manager->id
            && $this->hasRole($user, 'manager');
    }

    /**
     * Role check respects active_role: a user with [manager, sector_manager] who
     * selected 'manager' does not pass hasRole($user, 'sector_manager').
     */
    protected function hasRole(User $user, string $role): bool
    {
        $activeRole = $this->getActiveRole();

        if ($activeRole !== null && $activeRole !== $role) {
            return false;
        }

        $key = 'role:' . $user->id . ':' . $role;

        if (!isset($this->permCache[$key])) {
            $this->permCache[$key] = $user->roles()->where('name', $role)->exists();
        }

        return $this->permCache[$key];
    }

    protected function isSectorManager(User $user): bool
    {
        return $this->hasRole($user, 'sector_manager');
    }

    protected function isTeamManager(User $user): bool
    {
        return $this->hasRole($user, 'team_manager');
    }
}
