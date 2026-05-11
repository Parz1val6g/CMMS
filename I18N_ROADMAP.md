# i18n Roadmap — ERP Gestão

> Generated: 2026-05-05
> Total hardcoded strings identified: **~500**

---

## TIER 1 — Core Infrastructure (Do First)

Foundation layers. Every other tier depends on them.

| # | Scope | Target Files | What to Extract | String Count |
|---|-------|-------------|-----------------|:------------:|
| 1.1 | **Backend: Enum Labels** | `app/Core/Enums/*.php` | `label()` methods returning English strings — all 10 enums | **~40** |
| 1.2 | **Backend: HTTP Error Messages** | `app/Exceptions/Handler.php` | `getMessage()` return strings (401/403/404/422/500) | **5** |
| 1.3 | **Frontend: Lang Directory** | `resources/lang/en/` | Create `en` dictionary files beyond the existing 3-key `auth.php` | **Struct only** |

---

## TIER 2 — Form Schemas (High Volume)

All `app/Features/*/Schemas/*FormSchema.php` files use `->setLabel()`, `->helperText()`, `->helpExamples()`, `->setPlaceholder()`, and `FormSchema::make('Title')` with hardcoded text.

| File | Form Titles (Portuguese) | Labels/Help Text (English) | Count |
|------|------------------------|---------------------------|:-----:|
| `ServiceOrderFormSchema.php` | `Nova Ordem de Serviço`, `Editar Ordem de Serviço` | Process, Description, Client, Equipment, Service Type, Priority, Photo, Upload Photo, Location, Parish/Freguesia, Street, Reference Point, Postal Code, Map Coordinates, Coordinates, Core Details, Workflow Type, Status, Execution Date + helper text | **~40** |
| `EquipmentFormSchema.php` | `Novo Equipamento`, `Editar Equipamento` | Name, Brand, Model, Serial Number, Available for Loan, Revision Interval (days), Description, Status + helper text + helpExamples | **~20** |
| `ClientFormSchema.php` | `Novo Cliente`, `Editar Cliente` | NIF, First Name, Last Name, Email, Phone + helper text + helpExamples + placeholder | **~15** |
| `MaterialFormSchema.php` | `Novo Material`, `Editar Material` | Name, Unit, Stock Quantity + helper text + helpExamples | **~10** |
| `SectorFormSchema.php` | `Novo Sector`, `Editar Sector` | Name, Head | **~6** |
| `LocationFormSchema.php` | `Nova Localização`, `Editar Localização` | Street Address, Postal Code, Parish, Landmark, Coordinates | **~12** |
| `TaskFormSchema.php` | `Nova Tarefa`, `Editar Tarefa` | Name, Description, Service Order, Sectors, Status + helper text | **~15** |
| `TeamFormSchema.php` | `Nova Equipa`, `Editar Equipa` | Name, Sector | **~6** |
| `WorkerFormSchema.php` | `Novo Trabalhador`, `Editar Trabalhador` | First Name, Last Name, Email, Phone, Team + helper text + placeholder + helpExamples | **~15** |
| `WorkLogFormSchema.php` | `Novo Registo de Trabalho`, `Editar Registo de Trabalho` | Description, Mini-Task, Started At, Completed At, Status | **~12** |
| `MiniTaskFormSchema.php` | `Nova Mini-Tarefa`, `Editar Mini-Tarefa` | Description, Task, Workers, Teams | **~10** |
| `ServiceTypeFormSchema.php` | `Novo Tipo de Serviço`, `Editar Tipo de Serviço` | Name, Description | **~6** |

**Total Form Schema strings: ~170**

---

## TIER 3 — Frontend Pages

| Scope | File | Hardcoded Strings | Count |
|-------|------|-------------------|:-----:|
| **Sidebar** | `resources/js/Components/SideBar/index.jsx` | Section labels (Operacional, Entidades, etc.), nav items (Dashboard, Ordens Serviço, etc.), "Dev" badge | **~25** |
| **App Layout** | `resources/js/Layouts/AppLayout.jsx` | Footer: `"© {year} ERP Gestão — All rights reserved."` | **1** |
| **Login** | `resources/js/Features/Authentication/Pages/Login.jsx` | "Sign In", "Signing in...", "Email or Username", "Password", "Create an account" | **~8** |
| **Register** | `resources/js/Features/Authentication/Pages/Register.jsx` | "Create Account", "Start using the platform", "Full Name", "Work Email", "Confirm Password", "Terms of Service", "Privacy Policy", etc. | **~18** |
| **Dashboard** | `resources/js/Features/Dashboard/Pages/Dashboard.jsx` | "Dashboard Operacional", "Ordens em Curso", "Tarefas Pendentes", "Equipas no Terreno", "Horas Registadas Hoje", "Ordens Críticas", etc. | **~15** |
| **ServiceOrders** | `resources/js/Features/ServiceOrders/Pages/Index.jsx` | Breadcrumbs, Kanban columns, Drawer tabs, field labels, error toasts (mixed PT/EN) | **~30** |
| **Settings** | `resources/js/Features/Settings/Pages/Settings.jsx` | Tab labels (My Details, Password, Admin Settings, Account), all form labels, validation hints, delete account flow, toast messages, "coming soon" | **~50** |
| **Profile** | `resources/js/Features/Profile/Pages/Profile.jsx` | "Profile", "User Information", "Name:", "Email:", "Role:", "Status:", "Back" | **~8** |
| **All CRUD Index pages** | `resources/js/Features/{Feature}/Pages/Index.jsx` (15+ feature pages) | Breadcrumb: `"X Management"`, Title: `"X"` | **~30** |
| **Dev/WIP pages** | Notifications, Export, Analytics | "Work in Progress", "Dev Preview", "The notification center is being developed...", etc. | **~9** |

**Total Frontend Pages: ~195**

---

## TIER 4 — Shared Components

| Component | File | Hardcoded Strings | Count |
|-----------|------|-------------------|:-----:|
| **DataManager** | `resources/js/Components/DataManager/index.jsx` | "Delete Failed", "Table", "Kanban", "New {name}", "Delete {name}", "Deleting...", "Cancel", "Are you sure?", "OK" + error messages | **~15** |
| **EditPanel** | `resources/js/Components/DataManager/EditPanel.jsx` | "Edit {entityName}", "Saving...", "Save Changes", "Cancel", "Remove", "Update Failed", "Close" | **~10** |
| **FilterBar** | `resources/js/Components/DataManager/filterbar.jsx` | "Search...", "Newest", "Oldest", "Advanced", "Export CSV", "Search:", "Date:", "From Date", "To Date", "Apply", "Clear", "All", tooltips + error toast (Portuguese) | **~18** |
| **Modal** | `resources/js/Components/Common/Modal.jsx` | "Cancel", "Saving...", "Save {entityName}", "Close", toast messages, "Create {entityName}" | **~10** |
| **DialogModal** | `resources/js/Components/Common/DialogModal.jsx` | "OK", "Cancel", "Confirm", "Close" | **~5** |
| **Topbar** | `resources/js/Components/Common/Topbar.jsx` | `"Notificações"` (aria-label), `"Perfil"` (link text) | **2** |
| **Pagination** | `resources/js/Components/Table/Pagination.jsx` | "Page", "of" | **2** |
| **EmptyState** | `resources/js/Components/Table/EmptyState.jsx` | "No records found", "Try adjusting your search or filters" | **2** |
| **Table** | `resources/js/Components/Table/index.jsx` | "Actions" (column header) | **1** |
| **ToastContext** | `resources/js/Components/Toast/ToastContext.jsx` | `"useToast must be used within ToastProvider"` | **1** |

**Total Shared Components: ~65**

---

## TIER 5 — Backend Services & Controllers

| File | Hardcoded Strings | Count |
|------|-------------------|:-----:|
| `app/Features/ServiceOrders/Services/ServiceOrderService.php` | Exception messages (8) + task names `'Devolução de Equipamento'`, `'Empréstimo de Equipamento'` (Portuguese) | **10** |
| `app/Features/Equipments/Services/EquipmentService.php` | Exception messages: invalid status value, cannot transition | **2** |
| `app/Exceptions/InvalidStateTransitionException.php` | Default message: `'Invalid equipment state transition.'` | **1** |
| `app/Features/ServiceOrders/Controllers/ServiceOrderPageController.php` | Column labels (6) + filter labels (3) + placeholder (1) | **10** |
| `app/Features/ServiceOrders/Listeners/CreateLoanTasks.php` | Task name: `'Empréstimo de Equipamento'` (Portuguese) | **1** |
| `app/Features/Notifications/Listeners/SendServiceOrderCreatedNotification.php` | Title: `'New Service Order Created'`, Message: `"...has been created and assigned to you."` | **2** |

**Total Backend Services: ~26**

---

## TIER 6 — Existing Lang Files (Update/Extend)

| File | Current State | Action Needed |
|------|--------------|---------------|
| `resources/lang/en/auth.php` | 3 keys: `failed`, `inactive`, `password` | Extend with auth page keys (Sign In, Email, Password, etc.) |
| `resources/lang/pt_PT/auth.php` | 3 keys, Portuguese translations | Same extension |
| `resources/lang/en/` | No other files exist | Create: `enums.php`, `forms.php`, `components.php`, `pages.php`, `validation.php`, `messages.php` |
| `resources/lang/pt_PT/` | No other files exist | Mirror structure from `en/` |

---

## Progress Tracking

Check items below as they are completed via `i18n {target}`.

### Tier 1 — Core Infrastructure

- [x] `i18n enums` — Extract enum `label()` methods (11 enums, ~71 strings)
- [x] `i18n http-errors` — Extract `Handler.php` generic error messages (5 strings)
- [x] `i18n lang-structure` — Create lang directory structure (folders + indexing)

### Tier 2 — Form Schemas

- [x] `i18n forms-service-orders` — ServiceOrderFormSchema (~40 strings)
- [x] `i18n forms-equipments` — EquipmentFormSchema (~20 strings)
- [x] `i18n forms-clients` — ClientFormSchema (~15 strings)
- [x] `i18n forms-materials` — MaterialFormSchema (~10 strings)
- [x] `i18n forms-sectors` — SectorFormSchema (~6 strings)
- [x] `i18n forms-locations` — LocationFormSchema (~12 strings)
- [x] `i18n forms-tasks` — TaskFormSchema (~15 strings)
- [x] `i18n forms-teams` — TeamFormSchema (~6 strings)
- [x] `i18n forms-workers` — WorkerFormSchema (~15 strings)
- [x] `i18n forms-worklogs` — WorkLogFormSchema (~12 strings)
- [x] `i18n forms-minitasks` — MiniTaskFormSchema (~10 strings)
- [x] `i18n forms-service-types` — ServiceTypeFormSchema (~6 strings)

### Tier 3 — Frontend Pages

- [x] `i18n sidebar` — Sidebar navigation (~25 strings)
- [x] `i18n layout` — AppLayout footer (1 string)
- [x] `i18n auth` — Login + Register pages (~26 strings)
- [x] `i18n dashboard` — Dashboard page (~15 strings)
- [x] `i18n service-orders` — Service Orders page (~32 strings)
- [x] `i18n settings` — Settings page (~50 strings)
- [x] `i18n profile` — Profile page (~8 strings)
- [x] `i18n index-pages` — All CRUD index pages (~30 strings)
- [x] `i18n dev-pages` — Notifications, Export, Analytics WIP pages (~9 strings)

### Tier 4 — Shared Components

- [x] `i18n datamanager` — DataManager + EditPanel + FilterBar (~43 strings)
- [x] `i18n modal` — Modal + DialogModal (~15 strings)
- [x] `i18n topbar` — Topbar (2 strings)
- [x] `i18n table` — Table + Pagination + EmptyState (~5 strings)
- [x] `i18n toast` — ToastContext (1 string)

### Tier 5 — Backend Services & Controllers

- [x] `i18n service-order-service` — ServiceOrderService messages (~10 strings)
- [x] `i18n equipment-service` — EquipmentService + InvalidStateTransitionException (~3 strings)
- [x] `i18n controllers` — ServiceOrderPageController column/filter labels (~10 strings)
- [x] `i18n listeners` — CreateLoanTasks + SendServiceOrderCreatedNotification (~3 strings)

### Tier 6 — Lang Dictionaries

- [x] `i18n populate-en` — Populate all `en/` dictionary files
- [x] `i18n populate-pt` — Populate all `pt_PT/` dictionary files
