# Issues — Service Order Ticket System

> Generated from: [`docs/06-ticket-system/06-prd.md`](06-prd.md)
> Published locally — issue tracker integration TBD

---

## S-01: Foundation — Schema + Model + Role

**Labels:** `backend`, `database`, `migration`, `role`
**Milestone:** M1
**Estimate:** 1h
**Dependencies:** None

### Description

Create the database schema, Eloquent model, and role infrastructure to support the Ticket system. This issue lays the groundwork for all subsequent slices.

### Tasks

- [ ] **Migration:** Create `tickets` table with columns:
  - `id` UUID PK
  - `description` TEXT, required
  - `client_id` FK → clients, required
  - `service_type_id` FK → service_types, required
  - `priority` ENUM: `low`/`normal`/`high`/`urgent`, default `normal`
  - `location_id` FK → locations, nullable
  - `status` ENUM: `open`/`in_progress`/`converted`/`cancelled`, default `open`
  - `ticket_manager_id` FK → users
  - `service_order_id` FK → service_orders, nullable
  - timestamps (`created_at`, `updated_at`)
- [ ] **Model [`Ticket`](../../app/Features/Tickets/Models/Ticket.php):** Define relations:
  - `client()` → belongsTo Client
  - `serviceType()` → belongsTo ServiceType
  - `location()` → belongsTo Location (nullable)
  - `ticketManager()` → belongsTo User
  - `serviceOrder()` → belongsTo ServiceOrder (nullable)
- [ ] **Factory:** `TicketFactory` with state helpers for each status
- [ ] **RoleSeeder:** Add `ticket_manager` to role list
- [ ] **RolePermissionSeeder:** Grant `create` + `view` + `update` + `delete` on `TICKETS` resource for `ticket_manager`; grant `view` + `convert` for `manager`; grant all for `admin`
- [ ] **BasePolicy:** No new helper needed — existing role checks suffice
- [ ] **Sidebar config:** Add "Tickets" navigation entry visible to `ticket_manager`, `manager`, `admin`

### Acceptance Criteria

- [ ] `php artisan migrate` executes without errors
- [ ] `php artisan migrate:rollback` reverts the migration
- [ ] `php artisan db:seed` creates `ticket_manager` role + permissions
- [ ] `Ticket::factory()->create()` produces a valid record
- [ ] `Ticket::with('client', 'serviceType', 'ticketManager')->first()` loads all relations
- [ ] Sidebar shows "Tickets" for `ticket_manager`, `manager`, `admin` roles

---

## S-02: Create + List Tickets — Full Vertical

**Labels:** `backend`, `api`, `frontend`, `form`
**Milestone:** M2
**Estimate:** 3h
**Dependencies:** S-01

### Description

Implement the complete end-to-end flow for creating and listing tickets. A `ticket_manager` can open the Tickets page, see an empty list, click "Create Ticket", fill the form (description, client, service type, priority, location), submit, and see the new ticket appear in the list with priority badge and status indicator. Manager/admin sees all tickets; ticket_manager sees only own tickets.

### Tasks

- [ ] **`StoreTicketRequest`:** Validation rules:
  - `description`: required, string, max: 5000
  - `client_id`: required, uuid, exists: clients,id
  - `service_type_id`: required, uuid, exists: service_types,id
  - `priority`: required, enum: low, normal, high, urgent
  - `location_id`: nullable, uuid, exists: locations,id
- [ ] **`TicketResource`:** Shape matching PRD spec (client, service_type, priority, location, status, ticket_manager, service_order, created_at, updated_at)
- [ ] **`TicketService::create(data)`:** Creates ticket with status `open`, assigns `ticket_manager_id` from authenticated user, dispatches event (if any)
- [ ] **`TicketController` (API):** `index` (scoped by role — own vs all), `store`, `show`
- [ ] **`TicketPageController` (Web):** Renders Inertia page with tickets list data, eager loads relations
- [ ] **Route registration:** `api.php` + `web.php` following existing pattern
- [ ] **`TicketFormSchema`:** Field definitions:
  - `description` → TextareaInput (required)
  - `client_id` → SearchableSelect (required, loads from clients endpoint)
  - `service_type_id` → SelectInput (required, loads from service-types endpoint)
  - `priority` → SelectInput (required, enum options)
  - `location_id` → SearchableSelect (nullable, loads from locations endpoint)
- [ ] **Frontend `Index.jsx`:** Ticket list page with:
  - Filters (status, priority, client, date range)
  - Sortable columns (description, client, priority, status, created_at)
  - Status badges with color coding (open=blue, in_progress=yellow, converted=green, cancelled=red)
  - Priority indicators (low=grey, normal=blue, high=orange, urgent=red)
  - "Create Ticket" button opening a WorkspaceDrawer with create form
- [ ] **`TicketApiTest`:** Create + list tests:
  - Unauthenticated → 401
  - Create with valid data → 201, status=`open`, ticket_manager_id set
  - Create without required fields → 422
  - ticket_manager lists only own tickets (filtered)
  - admin/manager lists all tickets
  - Show single ticket returns full resource

### Acceptance Criteria

- [ ] `POST /api/tickets` with valid data → 201, ticket created with status `open`
- [ ] `POST /api/tickets` missing `client_id` → 422
- [ ] `GET /api/tickets` as ticket_manager → only own tickets
- [ ] `GET /api/tickets` as admin → all tickets
- [ ] `GET /api/tickets/{id}` returns full resource with nested relations
- [ ] Frontend list renders with correct columns, status badges, priority indicators
- [ ] Create drawer opens, form validates, submission creates ticket and updates list
- [ ] `php artisan test --filter=TicketApiTest` — all pass

---

## S-03: Edit + Cancel + Status Workflow

**Labels:** `backend`, `api`, `frontend`, `workflow`
**Milestone:** M3
**Estimate:** 2h
**Dependencies:** S-02

### Description

Implement ticket editing, cancellation, and status workflow transitions. A ticket_manager can edit their own open tickets; admin/manager can edit any non-terminal ticket. Any authorized user can cancel an open/in_progress ticket. Manager/admin can transition a ticket's status to `in_progress`. Terminal states (`converted`, `cancelled`) are read-only.

### Tasks

- [ ] **`UpdateTicketRequest`:** Same validation as StoreTicketRequest; status cannot be changed via update (use dedicated endpoints)
- [ ] **`TicketService::update(ticket, data)`:** Validates ticket is not in terminal state (`converted`/`cancelled`), applies changes; throws `ValidationException` if terminal
- [ ] **`TicketService::cancel(ticket)`:** Validates not `converted`, transitions to `cancelled`; throws `ValidationException` if `converted`
- [ ] **`TicketController::update`:** Calls `TicketService::update`, returns updated resource
- [ ] **`TicketController::destroy`:** Calls `TicketService::cancel` (soft cancel via status), returns 200
- [ ] **Status transition endpoint:** `PATCH /api/tickets/{id}/status` with body `{ "status": "in_progress" }`; validates allowed transitions
- [ ] **Frontend — Edit in drawer:** Open ticket drawer shows edit form for non-terminal tickets; save button calls PUT endpoint
- [ ] **Frontend — Cancel action:** "Cancel" button on drawer with confirmation dialog; disabled for `converted`/`cancelled`
- [ ] **Frontend — Status change:** Manager/admin sees "Mark as in progress" action on `open` tickets
- [ ] **`TicketApiTest`:** Update + cancel tests:
  - Update own ticket → 200
  - Update another's ticket as ticket_manager → 403
  - Update converted ticket → 422
  - Cancel open ticket → 200, status=`cancelled`
  - Cancel converted ticket → 422
  - Transition to `in_progress` → 200
  - Transition from `converted` → 422
- [ ] **`TicketPoliciesTest`:** Edit + cancel gates:
  - ticket_manager can edit own ticket
  - ticket_manager cannot edit another's ticket (403)
  - admin can edit any ticket
  - Worker cannot edit any ticket (403)

### Acceptance Criteria

- [ ] `PUT /api/tickets/{id}` updates allowed fields on non-terminal tickets
- [ ] `PUT /api/tickets/{id}` on converted/cancelled ticket → 422
- [ ] `DELETE /api/tickets/{id}` (cancel) on open ticket → 200, status=`cancelled`
- [ ] `DELETE /api/tickets/{id}` on converted ticket → 422
- [ ] `PATCH /api/tickets/{id}/status` with `in_progress` → 200
- [ ] ticket_manager can edit own ticket, not another's
- [ ] Frontend shows edit form only for non-terminal tickets
- [ ] Frontend cancel button disabled for terminal tickets
- [ ] `php artisan test --filter=TicketApiTest` + `--filter=TicketPoliciesTest` — all pass

---

## S-04: Ticket-to-SO Conversion

**Labels:** `backend`, `api`, `frontend`, `event`
**Milestone:** M4
**Estimate:** 2.5h
**Dependencies:** S-02

### Description

Implement the ticket-to-ServiceOrder conversion flow. On a ticket's detail drawer, a manager/admin sees a "Create Service Order" button. Clicking it opens a modal pre-filled with ticket data (description, client, service_type, priority, location). Manager fills SO-only fields (sectors, manager, workflow_type). On save: ServiceOrder is created, ticket is linked and becomes read-only with status `converted`. A `TicketConvertedEvent` is dispatched.

### Tasks

- [ ] **`TicketService::convertToServiceOrder(ticket, soData)`:** Full pipeline:
  1. Validates ticket is in convertible state (`open` or `in_progress`)
  2. Pre-fills SO data from ticket (description, client_id, service_type_id, priority, location_id)
  3. Merges with SO-only fields from payload (manager_id, sectors, workflow_type)
  4. Creates ServiceOrder via `ServiceOrderService::create()` within a DB transaction
  5. Sets `ticket.service_order_id = so.id`
  6. Transitions ticket status to `converted`
  7. Dispatches `TicketConvertedEvent`
  8. Returns both Ticket and ServiceOrder resources
- [ ] **`TicketController::convert`:** New endpoint `POST /api/tickets/{id}/convert`; authorization via `TicketPolicy::convert()`
- [ ] **Conversion validation:** Payload requires `manager_id`, `sectors` (array, min:1), `workflow_type` (enum: standard)
- [ ] **`TicketConvertedEvent`:** Carries `ticket_id` and `service_order_id`; can be listened to for future notifications
- [ ] **`TicketPolicy::convert()` gate:** `ticket_manager` = false; `manager`/`admin` = true
- [ ] **Frontend `ConvertToSOModal.jsx`:**
  - Triggered by "Create Service Order" button on ticket drawer
  - Shows pre-filled read-only fields from ticket (description, client, service_type, priority, location)
  - Shows editable SO-only fields (sectors multi-select, manager searchable select, workflow_type select)
  - On submit: calls `POST /api/tickets/{id}/convert`
  - On success: closes modal, updates ticket drawer to show `converted` status + SO reference link
- [ ] **`TicketConversionTest`:** Dedicated test file:
  - Conversion creates ServiceOrder with correct pre-filled data
  - SO-only fields are NOT pre-filled from ticket
  - Ticket `service_order_id` is set after conversion
  - Ticket status becomes `converted`
  - `TicketConvertedEvent` dispatched with correct IDs
  - Converting an already-converted ticket → 422
  - Converting a cancelled ticket → 422
  - ticket_manager cannot convert (403)

### Acceptance Criteria

- [ ] `POST /api/tickets/{id}/convert` with valid payload → 200, SO created, ticket `converted`
- [ ] `POST /api/tickets/{id}/convert` as ticket_manager → 403
- [ ] Converted ticket shows `service_order` reference in resource
- [ ] Pre-filled SO fields match ticket data
- [ ] Converting already-converted/cancelled ticket → 422
- [ ] Frontend shows "Create Service Order" button for manager/admin only
- [ ] Frontend modal pre-fills ticket data, shows SO-only fields editable
- [ ] After conversion, ticket drawer shows `converted` status + link to SO
- [ ] `php artisan test --filter=TicketConversionTest` — all pass

---

## S-05: Authorization + Edge Cases Tests

**Labels:** `testing`
**Milestone:** M5
**Estimate:** 1.5h
**Dependencies:** S-03, S-04

### Description

Implement comprehensive authorization and edge case tests covering all roles, all gates, and all boundary conditions. Ensures the ticket system is secure against unauthorized access and invalid state transitions.

### Tasks

- [ ] Create [`tests/Feature/Authorization/TicketPoliciesTest.php`](../../tests/Feature/Authorization/TicketPoliciesTest.php):
  - Admin can view/update/delete/convert any ticket
  - Manager can view all, update any, convert any ticket
  - Manager can transition ticket to `in_progress`
  - ticket_manager can view own tickets only
  - ticket_manager cannot view another's ticket (403)
  - ticket_manager can create tickets
  - ticket_manager can update own tickets
  - ticket_manager cannot update another's ticket (403)
  - ticket_manager cannot convert tickets (403)
  - ticket_manager can cancel own tickets
  - ticket_manager cannot cancel another's ticket (403)
  - Worker cannot access any ticket endpoint (403)
  - Unauthenticated requests return 401
  - Prior art: [`ServiceOrderPoliciesTest`](../../tests/Feature/Authorization/ServiceOrderPoliciesTest.php)
- [ ] Edge case tests:
  - Double conversion (convert already-converted ticket) → 422
  - Cancel already-cancelled ticket → 422 (idempotent or error?)
  - Cancel converted ticket → 422
  - Convert cancelled ticket → 422
  - Update ticket after conversion → 422
  - List filtering: verify ticket_manager cannot access another's ticket via direct ID
  - Delete (cancel) a ticket as worker → 403
  - Create ticket with non-existent `client_id` → 422
  - Create ticket with invalid `priority` value → 422
  - Prior art: [`CascadeCompletionTest`](../../tests/Feature/Cascade/CascadeCompletionTest.php)

### Acceptance Criteria

- [ ] `php artisan test --filter=TicketPoliciesTest` — all pass
- [ ] All edge cases covered and passing
- [ ] `php artisan test` — no existing tests broken (zero regressions)

---

## Dependency Graph

```
S-01 (Foundation)
  └── S-02 (Create + List)
        ├── S-03 (Edit + Cancel + Status)
        │     └── S-05 (Auth + Edge Cases)
        └── S-04 (Conversion)
              └── S-05 (Auth + Edge Cases)
```

All slices are **AFK** — no human-in-the-loop decisions required. Each slice is independently grabbable once its dependencies are merged.
