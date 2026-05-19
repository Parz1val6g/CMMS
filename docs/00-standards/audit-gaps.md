# Project Audit — Gaps & Issues

Generated: 2026-05-14

---

## Implementation Status vs. Roadmap

All 8 roadmap items from `docs/00-standards/roadmap.md` are implemented. Cross-reference below.

| # | Feature | Docs Folder | Tests | Audit | Status |
|---|---------|-------------|-------|-------|--------|
| 1 | Cost Per Hour | `01-cost-per-hour/` | 34 tests / 50 assertions | ✅ Implementation report | **DONE** |
| 2 | Equipment Extensions | `02-equipment-ext/` | 8/8 tests | ✅ 15 issues closed | **DONE** |
| 3 | Team Responsible | `03-team-responsibles/` | 19 tests / 36 assertions | ✅ Gate: PROCEED | **DONE** |
| 4 | Loan Extraction | `04-loan-extraction/` | Full suite | ✅ 2 BLOCKED resolved | **DONE** |
| 5 | Location Auto-Fill | `05-location-auto-fill/` | 7 scenarios | ✅ Gate: PROCEED | **DONE** |
| 6 | Ticket System | `06-ticket-system/` | Present | ❌ No audit | **DONE (unaudited)** |
| 7 | Entity Loans | `07-entity-loans/` | Present | ❌ No audit | **DONE (unaudited)** |
| 8 | Mini-Tasks Estimates | `08-mini-tasks-estimates/` | Present | ❌ No audit | **DONE (unaudited)** |

---

## Gaps Found

### GAP-001: CLAUDE.md is outdated — references non-existent frontend infrastructure

**Severity:** Medium
**File:** `CLAUDE.md`

The CLAUDE.md (used by AI assistants) references several frontend layers that **do not exist** in the codebase:

| What CLAUDE.md says | What actually exists |
|---------------------|---------------------|
| Pinia stores in `resources/js/stores/` (authStore, clientStore, taskStore, uiStore, settingsStore) | **No Pinia at all.** Pinia is not in `package.json`. |
| `useFetch` / `useForm` composables in `resources/js/services/api/` | **No composables.** State is Inertia page props + React `useState`. |
| API services layer in `resources/js/services/api/` | **No services directory.** API calls use raw `fetch()` with CSRF tokens. |

**Actual frontend state management:**
1. Inertia page props (`usePage().props`) — primary data source
2. React `useState` — local component state
3. React Context — `ToastContext`, `ErrorBoundary`
4. Refs — `savingRef`, `abortRef`, `snapshotRef` for mutable values

**Actual API communication:** Raw `fetch()` with `X-CSRF-TOKEN` header and `X-Requested-With: XMLHttpRequest`. Only auth pages (Login) use Inertia's `useForm()`.

**Fix:** Update CLAUDE.md to reflect the real frontend architecture.

---

### GAP-002: No CI/CD pipeline

**Severity:** Low
**Detail:** No `.github/` directory exists. No GitHub Actions, no deployment workflows, no automated test runs. All testing is local via `composer test` and `vitest`.

**Impact:** Tests are never run automatically on push/PR. Manual discipline required to run tests before merging.

---

### GAP-003: No production Dockerfile — DEV DOCKER RESOLVED

**Severity:** Low (dev resolved)
**Detail:** Dev Docker environment added (`Dockerfile`, `docker-compose.yml`, `docker/entrypoint.sh`, `.env.docker.example`). Production Dockerfile not yet created.

**Impact:** Development is containerized. Production deployment still requires manual setup or a prod-specific Dockerfile.

---

### GAP-004: `duration_minutes` column diverges between MySQL and SQLite

**Severity:** Low
**File:** `database/migrations/*_create_work_logs_table.php`

The `work_logs.duration_minutes` column is defined as:
- **MySQL:** `GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, started_at, completed_at)) STORED`
- **SQLite (testing):** Plain nullable integer

This creates a schema divergence between production and test environments. SQLite cannot reproduce the generated column behavior, so the column value must be set manually in tests.

**Impact:** Tests may pass on SQLite but fail on MySQL if the application logic relies on the generated column being auto-populated, or vice versa. No test coverage exists for the MySQL generated column behavior.

---

### GAP-005: Attachment legacy FK columns not removed after polymorphic migration

**Severity:** Low
**Files:**
- `database/migrations/2026_05_14_100030_*_refactor_attachments.php`
- `app/Features/Equipments/Models/Attachment.php`

The attachments table was refactored from dedicated FK columns (`service_order_id`, `mini_task_id`) to polymorphic (`attachable_type` + `attachable_id`). However:

1. The old columns (`service_order_id`, `mini_task_id`) were **not dropped** — they still exist in the table
2. The `equipment_id` column was added alongside polymorphic columns
3. The `Attachment` model retains a dedicated `equipment()` relationship (line 29) alongside the polymorphic `attachable()` (line 44)

This is not a bug — all queries use the polymorphic relationship. The old columns are dead weight.

**Fix:** A future migration should drop `service_order_id`, `mini_task_id`, and `equipment_id` from the attachments table if polymorphic is confirmed as the permanent approach. Alternatively, document the dual-relationship design.

---

### GAP-006: Features 06/07/08 lack architectural audit reports

**Severity:** Medium
**Detail:** Features 06 (Ticket System), 07 (Entity Loans), and 08 (Mini-Tasks Estimates) have PRDs, grill-me questionnaires, and issue definitions — but **no audit reports**. The 04-loan-extraction audit caught 2 BLOCKED items (cancel guard contradiction + WorkflowType enum deletion risk) that would have caused runtime errors. Similar risks may exist in:

| Feature | Risk Area | Docs Present | Audit Present |
|---------|-----------|-------------|---------------|
| 06 — Tickets | Ticket→SO conversion edge cases, double-convert, cancel-converted | PRD + Grill-Me + Issues | ❌ |
| 07 — Entity Loans | Entity vs Client on loan_orders, permission matrix, availability overlaps | PRD + Grill-Me + Issues | ❌ |
| 08 — Mini-Tasks | Locked workers UX, XOR constraint with multi-row pivot, equipment reservation/release | PRD + Grill-Me + Issues | ❌ |

**Impact:** Bugs from untested edge cases may surface in production. Comparison: the 04-loan-extraction audit found 2 blockers + 4 warns in an otherwise well-planned feature.

**Recommendation:** Run architectural audits on all three features following the same template used for 03-team-responsibles and 04-loan-extraction.

---

## Summary

| Category | Count |
|----------|-------|
| Roadmap items fully implemented | 8/8 |
| Features with full audit | 5/8 |
| Features without audit | 3/8 (06, 07, 08) |
| Active gaps (medium severity) | 2 (CLAUDE.md outdated, missing audits) |
| Active gaps (low severity) | 4 (no CI/CD, no prod Docker, MySQL/SQLite divergence, dead FK columns) |
| BLOCKED items | 0 |
