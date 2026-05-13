| `priority` | enum | low/normal/high/urgent |
| `location_id` | FK → locations, nullable | Reuses Location model |
| `status` | enum | open/in_progress/converted/cancelled |
| `ticket_manager_id` | FK → users | Who created the ticket |
| `service_order_id` | FK → service_orders, nullable | Link after conversion |

### 2. Location

Reuses existing Location model via location_id FK.

### 3. Status Workflow

open → in_progress → converted → [read-only]
                   ↘ cancelled

### 4. Conversion Flow

On Ticket detail, "Create Service Order" button opens a modal pre-filled with Ticket data. Manager fills SO-only fields (sectors, manager, workflow_type). On save: ServiceOrder created → ticket.service_order_id = so.id → ticket.status = converted → read-only.

### 5. Authorization

| Role | Permission |
|------|-----------|
| ticket_manager | CRUD own tickets |
| admin / manager | View all + convert to SO |

### 6. Feature Structure

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

resources/js/Features/Tickets/Pages/Index.jsx

database/migrations/2026_05_12_000001_create_tickets_table.php