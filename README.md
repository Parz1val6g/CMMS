# CMMS — Municipal Service Order Management System

A web-based platform for managing municipal service operations — from citizen issue reports through to field execution and completion tracking.

## Overview

Citizens report problems, attendants create service orders, managers activate and oversee them, sector managers assign work to teams, and workers log progress in the field. The system enforces a clean cascade: **Service Order → Task → Mini-Task → Work Log**, with completion propagating upward automatically.

### Roles

| Role | Responsibility |
|---|---|
| Admin | User and permission management |
| Manager | Owns service orders — activates, reviews, concludes |
| Attendant | Receives citizen reports, creates service orders |
| Task Manager | Breaks tasks into mini-tasks, assigns workers |
| Sector Manager | Oversees teams and workers within a sector |
| Team Manager | Manages team composition |
| Worker | Executes mini-tasks, logs work in the field |

## Tech Stack

- **Backend** — Laravel 12, MySQL, Redis
- **Frontend** — React 19, Inertia.js, Tailwind CSS v4
- **Auth** — Laravel Sanctum (session-based via Inertia)
- **Build** — Vite

## Requirements

- PHP 8.2+
- Node.js 20+
- MySQL 8+
- Redis
- Composer

## Getting Started

### 1. Clone and install dependencies

```bash
git clone https://github.com/Parz1val6g/CMMS.git
cd CMMS
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 3. Set up the database

```bash
php artisan migrate:fresh --seed --force
```

### 4. Run the development stack

```bash
composer dev
```

This starts Laravel, the queue worker, logs, and Vite HMR in a single command.

The app will be available at `http://localhost:8000`.

## Seeded Accounts

After seeding, the following accounts are available:

| Role | Email | Password |
|---|---|---|
| Admin | joao.almeida@cm-mangualde.pt | password |
| Manager | maria.pereira@cm-mangualde.pt | password |
| Attendant | ana.lima@cm-mangualde.pt | password |
| Task Manager | sofia.marques@cm-mangualde.pt | password |
| Worker | carlos.silva@cm-mangualde.pt | password |

## Key Commands

```bash
composer dev          # Start full dev stack
composer test         # Run test suite
npm run build         # Production frontend build
php artisan migrate:fresh --seed --force  # Reset and reseed database
```

## Architecture

Features are self-contained under `app/Features/{Feature}/` and `resources/js/Features/{Feature}/`, each with its own controllers, models, policies, requests, and routes.

Cross-cutting infrastructure lives in `app/Core/` (base policies, permission manager, enums, middleware) and `app/Shared/` (User, Role, Location hierarchy).

## License

MIT
