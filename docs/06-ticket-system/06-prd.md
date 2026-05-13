# PRD — Service Order Ticket System

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Data** | 2026-05-12 |
| **Versão** | 1.0 |
| **Status** | Aprovado |
| **Prioridade** | Alta |

---

## Problem Statement

When a citizen or external entity calls the company requesting a service (e.g., fixing a pothole, tree pruning), there is currently no structured way to capture that request before a Service Order is created. Operators must either create a full Service Order on the spot — requiring SO-only fields (sectors, manager, workflow_type) that may not yet be known — or lose the request details entirely.

This causes:
- Lost leads and unregistered service requests
- Premature Service Order creation with incomplete data
- No separation between the intake role (ticket_manager) and the execution planning role (manager/admin)
- No visibility into pending requests vs converted work orders

---

## Solution

Create a **Ticket system** — a lightweight pre-Service Order intake mechanism. A `ticket_manager` role receives phone/email requests and creates Tickets with: description, client, service type, priority, and location. No SO-specific fields required.

A manager or admin can later **convert** a Ticket into a full Service Order via a modal that pre-fills ticket data and lets them add SO-only fields (sectors, manager, workflow_type). After conversion, the Ticket becomes read-only and links to the resulting Service Order.

---

## User Stories

1. As a **ticket_manager**, I want to create a ticket with description, client, service type, priority, and location, so that I can capture a service request without needing SO-specific fields.
2. As a **ticket_manager**, I want to view a list of my tickets sorted by priority and creation date, so that I can track pending requests.
3. As a **ticket_manager**, I want to edit my own tickets, so that I can correct or update request details before conversion.
4. As a **ticket_manager**, I want to cancel my own tickets, so that I can discard requests that are no longer relevant.
5. As a **manager/admin**, I want to view all tickets across all ticket_managers, so that I have full visibility into incoming requests.
6. As a **manager/admin**, I want to convert a ticket into a Service Order via a pre-filled modal, so that I can act on requests efficiently without retyping data.
7. As a **manager/admin**, I want the conversion modal to include only SO-specific fields (sectors, manager, workflow_type), so that the form is minimal and focused.
8. As a **manager/admin**, I want the converted ticket to become read-only and link to the resulting Service Order, so that I can trace back from SO to the original request.
9. As a **manager/admin**, I want to change a ticket's status from open to in_progress, so that I can signal that a request is being reviewed.
10. As a **system**, I want tickets to follow a strict workflow (open → in_progress → converted/cancelled), so that status is always predictable and auditable.
11. As a **ticket_manager**, I want to see the linked Service Order reference on a converted ticket, so that I know my request was actioned.
12. As a **admin**, I want full CRUD access to all tickets regardless of ownership, so that I can manage the entire pipeline.
13. As a **developer**, I want the ticket feature to follow the existing DDD structure (`app/Features/Tickets/`), so that the codebase remains consistent.
14. As a **ticket_manager**, I want the ticket index page to show status badges, priority indicators, and creation date, so that I can scan pending requests quickly.

---

## Implementation Decisions

### Deep Modules

#### 1. TicketService — lifecycle orchestrator

Encapsulates the entire ticket lifecycle behind a minimal interface:

| Method | Description |
|--------|-------------|
| `create(data)` | Validates input, creates Ticket with `open` status, dispatches event |
| `update(ticket, data)` | Validates status allows edits (not converted/cancelled), applies changes |
| `cancel(ticket)` | Validates not already converted, transitions to `cancelled` |
| `convertToServiceOrder(ticket, soData)` | Validates ticket is `open`/`in_progress`, creates ServiceOrder, links ticket via `service_order_id`, transitions to `converted`, dispatches `TicketConvertedEvent` |

Internal complexity:
- Status transition validation (state machine enforcement)
- Transaction wrapping for `convertToServiceOrder` (create SO + update ticket in one DB transaction)
- Event dispatching for audit trail

#### 2. TicketPolicy — authorization gate

Scoped authorization following the existing `BasePolicy` pattern:

| Gate | ticket_manager | manager | admin |
|------|---------------|---------|-------|
| viewAny | own only | all | all |
| view | own only | all | all |
| create | yes | yes | yes |
| update | own only (if not converted) | all (if not converted) | all |
| delete | own only (if not converted) | all (if not converted) | all |
| convert | no | yes | yes |

#### 3. TicketFormSchema — form configuration

Returns structured field array consumed by the frontend form builder:
- `description` → TextareaInput (required)
- `client_id` → SearchableSelect, loads from clients endpoint (required)
- `service_type_id` → SelectInput, loads from service-types endpoint (required)
- `priority` → SelectInput, enum: low/normal/high/urgent (required, default: normal)
- `location_id` → SearchableSelect, loads from locations endpoint (nullable)

#### 4. ConversionFlow — ticket-to-SO pipeline

Encapsulated within `TicketService::convertToServiceOrder()`:
1. Validates ticket is in convertible state (`open` or `in_progress`)
2. Pre-fills SO fields from ticket data (description, client_id, service_type_id, priority, location_id)
3. Manager fills SO-only fields (sectors, manager_id, workflow_type)
4. Creates ServiceOrder via `ServiceOrderService::create()`
5. Sets `ticket.service_order_id = so.id`
6. Transitions ticket status to `converted`
7. Dispatches `TicketConvertedEvent`
8. Returns both Ticket and ServiceOrder resources

### Schema

**New table: `tickets`**

| Column | Type | Notes |
|--------|------|-------|
| `id` | UUID PK | |
| `description` | TEXT | Required |
| `client_id` | FK → clients | Required |
| `service_type_id` | FK → service_types | Required |
| `priority` | ENUM | `low`, `normal`, `high`, `urgent` (default: `normal`) |
| `location_id` | FK → locations, nullable | Reuses existing Location model |
| `status` | ENUM | `open`, `in_progress`, `converted`, `cancelled` (default: `open`) |
| `ticket_manager_id` | FK → users | Who created the ticket |
| `service_order_id` | FK → service_orders, nullable | Set after conversion |
| timestamps | | created_at, updated_at |

### Status Workflow

```
open → in_progress → converted → [read-only]
                   ↘ cancelled
```

- `open`: Initial state, editable by creator
- `in_progress`: Manager is reviewing, editable by admin/manager
- `converted`: Terminal state, read-only, linked to ServiceOrder
- `cancelled`: Terminal state, read-only

### API Contracts

```
GET    /api/tickets              → paginated list (filtered by authorization)
POST   /api/tickets              → StoreTicketRequest, returns TicketResource
GET    /api/tickets/{id}         → single ticket with relations
PUT    /api/tickets/{id}         → UpdateTicketRequest
DELETE /api/tickets/{id}         → soft-ish cancel (status → cancelled)
POST   /api/tickets/{id}/convert → conversion payload, returns TicketResource + ServiceOrderResource
```

**StoreTicketRequest validation rules:**
- `description`: required, string, max: 5000
- `client_id`: required, uuid, exists: clients,id
- `service_type_id`: required, uuid, exists: service_types,id
- `priority`: required, enum: low, normal, high, urgent
- `location_id`: nullable, uuid, exists: locations,id

**Conversion payload:**
- `manager_id`: required, uuid, exists: users,id
- `sectors`: required, array, min:1
- `sectors.*.id`: required, uuid, exists: sectors,id
- `workflow_type`: required, enum: standard

**TicketResource shape:**
```json
{
  "id": "uuid",
  "description": "string",
  "client": { "id": "uuid", "name": "string" },
  "service_type": { "id": "uuid", "name": "string" },
  "priority": "low|normal|high|urgent",
  "location": { "id": "uuid", "parish": "string", ... } | null,
  "status": "open|in_progress|converted|cancelled",
  "ticket_manager": { "id": "uuid", "name": "string" },
  "service_order": { "id": "uuid", "reference": "string" } | null,
  "created_at": "timestamp",
  "updated_at": "timestamp"
}
```

### Feature Structure

```
app/Features/Tickets/
├── Models/Ticket.php
├── Controllers/Api/TicketController.php
├── Controllers/Web/TicketPageController.php
├── Requests/StoreTicketRequest.php
├── Requests/UpdateTicketRequest.php
├── Resources/TicketResource.php
├── Services/TicketService.php
├── Policies/TicketPolicy.php
├── Events/TicketConvertedEvent.php
├── TicketFormSchema.php
└── Routes/api.php + web.php

resources/js/Features/Tickets/
├── Pages/Index.jsx
└── Components/
    ├── TicketDrawer.jsx
    └── ConvertToSOModal.jsx

database/migrations/2026_05_12_000001_create_tickets_table.php
```

### Modules to Create

| Module | Type | Description |
|--------|------|-------------|
| `Ticket` | Model | Eloquent model with relations to Client, ServiceType, Location, User (ticket_manager), ServiceOrder |
| `TicketService` | Service | Lifecycle orchestrator: create, update, cancel, convertToServiceOrder |
| `TicketController` | API Controller | RESTful CRUD + convert endpoint |
| `TicketPageController` | Web Controller | Inertia page rendering |
| `StoreTicketRequest` | Request | Creation validation rules |
| `UpdateTicketRequest` | Request | Update validation rules |
| `TicketResource` | Resource | API response transformer |
| `TicketPolicy` | Policy | Authorization gates |
| `TicketConvertedEvent` | Event | Dispatched on successful conversion |
| `TicketFormSchema` | Schema | Form field definitions |
| `Ticket` factory | Factory | Test data factory |
| `Index.jsx` | Frontend Page | Ticket list with filters + WorkspaceDrawer |
| `TicketDrawer.jsx` | Frontend Component | Detail drawer with edit + convert actions |
| `ConvertToSOModal.jsx` | Frontend Component | Conversion modal with SO-only fields |
| Migration | Migration | `create_tickets_table` |

### Modules to Modify

| Module | Change |
|--------|--------|
| `RoleSeeder` / Permission seeder | Add `ticket_manager` role with scoped permissions on `TICKETS` resource |
| Sidebar config | Add Tickets entry for `ticket_manager`, `manager`, and `admin` roles |

---

## Testing Decisions

### Testing Philosophy

Tests should verify **external behaviour**, not implementation details. For each module:
- **API tests** assert correct HTTP status codes, response shapes, and database state
- **Policy tests** assert correct authorisation outcomes for each role
- **Service tests** assert correct side effects (status transitions, event dispatching, linked SO creation) via database assertions
- **Frontend tests** assert DOM rendering, form validation, and modal interactions

### Modules to Test

| Test File | What It Tests | Prior Art |
|-----------|--------------|-----------|
| `tests/Feature/Api/TicketApiTest.php` | CRUD endpoints: list (scoped), create, view, update, delete, convert, auth failures | [`ServiceOrderApiTest`](tests/Feature/Api/ServiceOrderApiTest.php) |
| `tests/Feature/Authorization/TicketPoliciesTest.php` | Policy enforcement: admin full access, manager view-all + convert, ticket_manager own tickets only, worker blocked | [`ServiceOrderPoliciesTest`](tests/Feature/Authorization/ServiceOrderPoliciesTest.php) |
| `tests/Feature/Tickets/TicketServiceTest.php` | Service logic: create transitions to open, cancel transitions to cancelled, convert creates SO + links + transitions to converted, invalid transitions rejected | [`CascadeCompletionTest`](tests/Feature/Cascade/CascadeCompletionTest.php) |
| `tests/Feature/Tickets/TicketConversionTest.php` | Conversion flow: pre-fills from ticket, creates SO with correct data, dispatches event, makes ticket read-only | [`CascadeCompletionTest`](tests/Feature/Cascade/CascadeCompletionTest.php) |
| `resources/js/Features/Tickets/__tests__/Index.test.jsx` | Frontend: ticket list renders, drawer opens, convert modal interaction, form validation | Existing frontend test patterns |

### Key Test Cases

**TicketApiTest:**
- Unauthenticated requests return 401
- ticket_manager can list only own tickets (filtered)
- admin/manager can list all tickets
- Creating a ticket with valid data returns 201 with correct status `open`
- Creating a ticket without required fields returns 422
- ticket_manager can update own ticket
- ticket_manager cannot update another's ticket (403)
- admin can update any ticket
- Converting a ticket returns 200 with SO reference
- Converting an already-converted ticket returns 422
- Cancelling a ticket returns 200 with status `cancelled`
- Cancelling a converted ticket returns 422

**TicketPoliciesTest:**
- Admin can view/update/delete/convert any ticket
- Manager can view all, update any, convert any
- ticket_manager can view own tickets only
- ticket_manager cannot view another's ticket (403)
- ticket_manager cannot convert tickets (403)
- Worker cannot access tickets at all (403)
- Unauthenticated requests return 401

**TicketServiceTest:**
- `create()` sets status to `open` and assigns `ticket_manager_id`
- `update()` modifies allowed fields
- `update()` on converted/cancelled ticket throws exception
- `cancel()` transitions to `cancelled`
- `cancel()` on converted ticket throws exception
- `convertToServiceOrder()` creates ServiceOrder with pre-filled data
- `convertToServiceOrder()` links ticket to SO
- `convertToServiceOrder()` dispatches `TicketConvertedEvent`
- `convertToServiceOrder()` on cancelled ticket throws exception

**TicketConversionTest:**
- Pre-filled fields match ticket data (description, client_id, service_type_id)
- SO-only fields are NOT pre-filled (sectors, manager)
- Resulting ServiceOrder has correct `reference` format
- Ticket becomes read-only after conversion
- `TicketConvertedEvent` carries both ticket ID and SO ID

**Frontend test:**
- Ticket list renders with correct columns (description, client, priority, status, date)
- Status badge displays correct color per status
- Priority indicator displays correct icon per level
- Create form validates required fields
- Convert modal opens with pre-filled data
- Convert modal submits correct payload
- ticket_manager cannot see "Convert" button
- admin/manager can see "Convert" button

---

## Out of Scope

- Loan ticket portal for external entities (separate feature — see roadmap item 7)
- Email/SMS notifications for ticket creation or conversion
- Bulk ticket import or CSV export
- Ticket attachments (can be added later via existing AttachmentService)
- SLA tracking or escalation rules
- Public-facing ticket submission form
- Historical audit log beyond what events provide

---

## Further Notes

- The grill-me session that produced these decisions is documented in [`docs/06-ticket-system/06-grill-me.md`](docs/06-ticket-system/06-grill-me.md).
- The `ticket_manager` role name follows the existing naming convention (`equipment_manager`, `task_manager`, `team_manager`, `sector_manager`).
- All mutations go through transactions, consistent with the rest of the codebase.
- The frontend follows the existing WorkspaceDrawer pattern used by ServiceOrders, Tasks, and MiniTasks.
- The conversion modal reuses the existing ServiceOrder creation modal pattern but filters to SO-only fields.
- Priority enum values (`low`, `normal`, `high`, `urgent`) match the existing `Priority` enum used by ServiceOrders.
- No new `Location` logic is required — the ticket reuses the existing `Location` model via `location_id` FK.
