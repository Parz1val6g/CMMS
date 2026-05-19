# GitHub Sync Status — Team Responsible Feature

**Synced at:** 2026-05-13T17:11 UTC

## Issues Closed

| Issue | Title | Status | Summary |
|-------|-------|--------|---------|
| [#64](https://github.com/Parz1val6g/CMMS/issues/64) | ISSUE-001: Foundation — Schema + Role + Model | ✅ Closed | Migration added `responsible_id` to teams, `responsible()` relation on Team model, `team_manager` role seeded, permissions + `isTeamManager()` helper in BasePolicy |
| [#65](https://github.com/Parz1val6g/CMMS/issues/65) | ISSUE-002: Create Team with Responsible — Full CRUD Path | ✅ Closed | TeamService::create() with Worker/role auto-creation in TransactionHandler, TeamFormSchema with SearchableSelect, TeamResource with `whenLoaded('responsible')`, API + Web controllers |
| [#66](https://github.com/Parz1val6g/CMMS/issues/66) | ISSUE-003: Update Team + Policy Scoping | ✅ Closed | TeamService::update() with responsible change logic (Worker dedup), TeamPolicy::view()/update() with team_manager scoping, TeamSeeder assigns responsible_id |
| [#67](https://github.com/Parz1val6g/CMMS/issues/67) | ISSUE-004: Tests — Integração | ✅ Closed | 19 tests across 3 suites (TeamServiceTest, TeamApiTest, TeamPoliciesTest) — all GREEN |

## Test Results

```
Tests:    19 passed (36 assertions)
Duration: 3.50s
```

## Implementation Report

See [`docs/03-team-responsibles/03-audit-report.md`](03-audit-report.md) for the full audit resolution and implementation details.
