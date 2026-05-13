# PRD — Team Responsible Feature

## Problem Statement

Teams (equipas) are organisational units within Sectors (setores). Currently, a Team has no responsible person assigned — it only has a name and a sector. There is no way to know who leads or manages a given team, which creates ambiguity in task assignment, accountability, and operational communication.

Sectors already have a `head_id` pointing to the responsible User. Teams need the same concept.

## Solution

Add a `responsible_id` foreign key on the `teams` table pointing to `users`, establishing a clear responsible person for every team. This user must be a Worker of that same team and hold both `worker` and `team_manager` roles. The `team_manager` role has scoped permissions to view and update only the team they are responsible for.

## User Stories

1. As a **sector_manager**, I want to assign a responsible person when creating a team, so that every team has clear leadership from day one.

2. As a **sector_manager**, I want to change the responsible person of a team, so that I can reorganise leadership when needed.

3. As a **team_manager**, I want to view my team's details, so that I can see who is in my team and understand my responsibilities.

4. As a **team_manager**, I want to update my team's information (name, workers), so that I can keep my team's data current without needing a sector_manager.

5. As a **team_manager**, I want to view the workers in my team, so that I can manage my team members effectively.

6. As a **team_manager**, I want to be automatically added as a Worker of my team, so that I am consistently recognised as both a member and leader.

7. As a **sector_manager**, I want to remove a responsible person from a team without removing them as a Worker, so that the person stays in the team but loses management duties.

8. As a **user**, I want to see the responsible person's name displayed in the team list, so that I know who to contact about team matters.

9. As an **admin**, I want full CRUD access to all teams regardless of assignment, so that I can oversee the entire organisation.

## Implementation Decisions

### Schema Change

A new migration adds `responsible_id` (UUID, FK → `users`, NOT NULL, `cascadeOnDelete`) to the `teams` table.

### New Role: `team_manager`

A new role `team_manager` is added, consistent with the existing naming pattern (`sector_manager`, `equipment_manager`, etc). This role receives `view` and `update` permissions on the `TEAMS` resource. These permissions are enforced at the Policy level to be scoped to the user's own team only.

### User Eligibility

The responsible person must be a User who:
- Has the `worker` role
- Has the `team_manager` role
- Is a Worker of that same team (Worker record exists with matching `user_id` and `team_id`)

Because `Worker.user_id` is UNIQUE, a user can only be a Worker — and therefore a responsible person — for one team.

### TeamService Logic

**Creating a team with a responsible person:**
1. Validate the user is not already a Worker of another team
2. Create the Team with `responsible_id`
3. Assign `team_manager` and `worker` roles to the user if not already present
4. Create a Worker record linking the user to the team

**Updating a team's responsible person:**
1. If the new user is already a Worker of this team → update `responsible_id` only
2. If not → create Worker record and assign roles
3. Previous responsible person: only clear `responsible_id`, their Worker record remains

**Removing a responsible person:** Only nullify `responsible_id`. The Worker record stays — the person remains a team member.

### Authorization (TeamPolicy)

The existing `TeamPolicy` gains a scoped check for `team_manager` similar to the existing `sector_manager` and `supervisor` checks. When a `team_manager` attempts to update a team, the policy verifies `$team->responsible_id === $user->id`. The `BasePolicy` gets a corresponding `isTeamManager()` helper.

### API Contract (TeamResource)

```php
'responsible' => $this->whenLoaded('responsible', fn() => [
    'id' => $this->responsible->id,
    'name' => $this->responsible->first_name . ' ' . $this->responsible->last_name,
]),
```

The `responsible` field is included only when loaded, keeping responses lightweight by default.

### Form Schema

The existing `TeamFormSchema` adds a `SelectInput` for `responsible_id`:
- **Label:** "Responsável"
- **Required:** Yes
- **Options:** Users with the `worker` role who are not already a Worker of another team
- **UI Component:** `SearchableSelect`

### Team Index (Web)

The `TeamPageController` adds a "Responsável" column to the teams index table. The query eagerly loads `responsible` alongside `sector`.

### Seeders

All relevant seeders are updated:
- `RoleSeeder` — adds `'team_manager'` to the role list
- `UserSeeder` — creates one test user with the `team_manager` role
- `RolePermissionSeeder` — grants `view` + `update` on `TEAMS` to `team_manager`
- `TeamSeeder` — assigns `responsible_id` on existing teams
- `WorkerSeeder` — ensures the `team_manager` test user has both `worker` and `team_manager` roles

## Testing Decisions

### Testing Philosophy

Tests should verify **external behaviour**, not implementation details. For this feature:
- **API tests** assert correct HTTP status codes, response shapes, and database state
- **Policy tests** assert correct authorisation outcomes for each role
- **Service tests** assert correct side effects (Worker creation, role assignment) via database assertions

### Modules to Test

| Test File | What It Tests | Prior Art |
|-----------|--------------|-----------|
| `tests/Feature/Api/TeamApiTest.php` | CRUD endpoints for teams: create with responsible, update responsible, list with responsible field, auth failures | [`ServiceOrderApiTest`](tests/Feature/Api/ServiceOrderApiTest.php) |
| `tests/Feature/Authorization/TeamPoliciesTest.php` | Policy enforcement: admin can do anything, team_manager can only edit own team, sector_manager scoped to sector, worker cannot edit | [`ServiceOrderPoliciesTest`](tests/Feature/Authorization/ServiceOrderPoliciesTest.php) |
| `tests/Feature/Teams/TeamServiceTest.php` | Service logic: Worker created on team creation, roles assigned, duplicate Worker rejection, responsible removal behaviour | [`CascadeCompletionTest`](tests/Feature/Cascade/CascadeCompletionTest.php) |

### Key Test Cases

**TeamApiTest:**
- Unauthenticated requests return 401
- Creating a team without `responsible_id` returns 422
- Creating a team with valid data returns 201 and creates Worker record
- Creating a team with a user who is already a Worker of another team returns 422
- Updating `responsible_id` creates a new Worker record for the new person
- Listing teams includes the `responsible` field
- Non-admin cannot delete teams

**TeamPoliciesTest:**
- Admin can view/update/delete any team
- `team_manager` can view and update their own team
- `team_manager` cannot update another team
- `sector_manager` can view/update teams in their sector
- `worker` cannot update any team
- Unauthenticated requests return 401

**TeamServiceTest:**
- Creating a team with `responsible_id` creates a Worker record
- Creating a team with `responsible_id` assigns `worker` + `team_manager` roles
- Creating a team with a user who is already a Worker in another team throws ValidationException
- Updating `responsible_id` to a new user creates a new Worker record
- Removing `responsible_id` clears it but keeps the Worker record

## Out of Scope

- Frontend UI components for the team form (handled by existing Inertia + SearchableSelect)
- Notifications when a user is assigned as team responsible
- Audit logging for responsible person changes (handled by existing global AuditObserver)
- Bulk assignment of responsible persons
- Historical tracking of responsible person changes

## Further Notes

- The `team_manager` role name was chosen to follow the existing convention (`sector_manager`, `equipment_manager`, `task_manager`). It was validated during the architectural review.
- All mutations go through `TransactionHandler`, consistent with the rest of the codebase.
- The `Worker.user_id` UNIQUE constraint is leveraged as a natural invariant — a user cannot be a Worker of multiple teams, which aligns with the domain rule that a person leads exactly one team.
- The `responsible_id` is required (NOT NULL) — there is no scenario where a team exists without a responsible person.
- Removing a responsible person (setting to null) is intentionally not allowed at the DB level since the column is NOT NULL. The "remove responsible" flow means reassigning to another user.
