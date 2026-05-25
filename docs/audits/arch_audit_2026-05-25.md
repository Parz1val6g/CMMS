# Architectural Audit — 2026-05-25

**Codebase:** Laravel 12 + React 19 + Inertia.js — CMMS  
**Gate:** UC1 completion gate  
**Result:** 2 CRITICAL · 4 HIGH · 2 MEDIUM · 1 LOW

---

## Findings

| ID | Severity | Principle | Location | Summary |
|---|---|---|---|---|
| C-1 | CRITICAL | P1 Single Source of Truth | `app/Core/Services/CacheManager.php:51–63` | `invalidatePattern()` reads cache files from disk via `file_get_contents()` instead of using the Cache facade |
| C-2 | CRITICAL | P7 Data Access Pattern | `app/Features/Dashboard/Controllers/Api/DashboardController.php:53` | `DB::table('user_roles')->count()` — raw query builder in controller bypassing Model layer |
| H-1 | HIGH | P2 Domain Org + P7 Data Access | `app/Features/Dashboard/Controllers/Api/DashboardController.php` | 488-line god controller with 7 inline role dashboards; no `DashboardService` exists |
| H-2 | HIGH | P4 No Cross-Domain Imports | `resources/js/Features/EntityPortal`, `ManagerPortal`, `Dashboard` | Portal pages import components directly from sibling features (`LoanOrders`, `ServiceOrders`, `Tasks`) |
| H-3 | HIGH | P5 Pattern Consistency | `resources/js/Hooks/useClientLocations.js`, `ClientLocationManager.jsx`, `ClientCreateModal.jsx` | Direct `fetch()` calls bypass the established `useFetch`/`useForm` composable layer |
| H-4 | HIGH | P11 Dependency Currency | `composer.json:10` | `"inertiajs/inertia-laravel": "*"` — wildcard will pull breaking major versions on `composer update` |
| M-1 | MEDIUM | P5 Pattern Consistency | `app/Features/Equipments/Models/EquipmentRevision.php:52,60,68` | Status compared with raw strings `'approved'`/`'pending'`/`'rejected'`; no `EquipmentRevisionStatus` enum |
| M-2 | MEDIUM | P1 Single Source of Truth | `app/Core/Services/CacheManager.php:66` | `Cache::forget($pattern)` on non-file path treats a partial key as an exact key — semantically incorrect |
| L-1 | LOW | P6 No Duplication | `*Drawer.jsx` (6 files) | `Field()` helper component defined locally in each drawer with minor style variations |

## Suggested New Principle

**P12 — Explicit Workflow Orchestration:** Multi-step domain workflows must be traceable from a single entry point. The cascade completion chain (WorkLog → MiniTask → Task → ServiceOrder) is split across 3 listeners in 3 feature directories with no single visible entry point.

## Passing Areas

- P3 Dependency Layering ✓
- P5 TransactionHandler pattern ✓ (7+ services)
- P5 Resource pattern ✓ (all controllers)
- P5 Base trait on Models ✓
- P8 No monolithic files ✓
- P10 External interface compliance ✓
- P4 Backend cross-feature imports ✓ (justified by cascade domain)
