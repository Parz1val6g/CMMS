# Site Map — Navigation Menu Structure

## 🔍 Top Navigation Bar (All Authenticated Users)

```
[Logo] | [Search] | [Notifications] | [User Profile ▼]
                                        ├── My Profile
                                        ├── Settings
                                        ├── Preferences
                                        ├── Help
                                        └── Logout
```

---

## 🗂️ Sidebar Navigation (Role-Based)

### Admin Sidebar

```
Dashboard
├── Users Management
├── Roles & Permissions
├── Organization
│   ├── Sectors
│   ├── Teams
│   └── Workers
├── Master Data
│   ├── Service Types
│   ├── Materials
│   ├── Locations
│   └── Geographic Hierarchy
├── System
│   ├── Settings
│   ├── Audit Log
│   ├── Backups
│   └── System Health
└── Help & Support
```

### Manager Sidebar

```
Dashboard
├── Service Orders
├── Clients
├── Tasks
├── Reports & Analytics
├── Exports
├── Materials (View Only)
├── Organization (View Only)
└── Settings
```

### Supervisor Sidebar

```
Dashboard
├── Mini-Tasks (My Sector)
├── Work Logs (Approval)
├── Tasks (My Sector)
├── Workers & Teams
├── Performance Reports
└── Settings
```

### Worker Sidebar

```
Dashboard
├── My Mini-Tasks
├── My Work Logs
├── Team Info
└── Settings
```

---

## 📱 Responsive Design Breakpoints

```
Mobile (< 768px)
├── Single column layout
├── Collapsible navigation (hamburger menu)
├── Touch-friendly buttons (min 44px height)
└── Stack cards vertically

Tablet (768px - 1024px)
├── Two column layout
├── Side navigation collapsible
├── Optimized for landscape & portrait
└── Larger touch targets

Desktop (> 1024px)
├── Full multi-column layout
├── Fixed navigation sidebar
├── Full data tables
└── All features visible
```

---

## 📊 Search & Filter Capabilities

### Filterable Pages

| Page | Filters | Sort By |
|------|---------|---------|
| Service Orders | Status, Client, Service Type, Date Range, Priority, Manager | Date, Status, Priority, Client |
| Tasks | Status, Task Name, Assigned Sectors, Date Range | Date, Status, Sector |
| Mini-Tasks | Status, Assigned Worker/Team, Priority, Date Range | Date, Status, Worker |
| Work Logs | Status, Mini-Task, Worker, Material Used, Date Range | Date, Status, Material |
| Clients | Status, Name, Tax ID, Location, Date Added | Name, Date Added, Status |
| Materials | Name, Unit, Status, Stock Level, Date | Name, Stock, Unit |
| Workers | Sector, Team, Name, Status, Availability | Name, Sector, Team |

### Global Search

- Quick search across: SO #, Tasks, Workers, Clients, Materials
- Search filters by type (SO: #, Task: T#, Worker: W#, etc)
