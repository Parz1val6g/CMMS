# Especificação do Projeto — Sistema de Gestão de Ordens de Serviço

**Última atualização**: 2026-05-05  
**Estado atual**: Backend em produção — Frontend em desenvolvimento  
**Stack tecnológico**: Laravel 12 (PHP 8.x) + React 19 / Inertia.js + MySQL  
**Arquitetura**: Modular baseada em funcionalidades (16 features) com infraestrutura centralizada

---

## 1. Propósito Principal

O **Sistema de Gestão de Ordens de Serviço** é uma plataforma empresarial concebida para digitalizar e gerir o ciclo de vida completo de ordens de serviço municipais. O sistema permite que uma Câmara Municipal registe pedidos de intervenção (ex.: buraco na estrada, reparação de equipamentos), os decomponha em tarefas sectoriais e mini-tarefas atribuídas a trabalhadores ou grupos, e acompanhe a execução através de registos de trabalho (work logs) com controlo de materiais e equipamentos.

O sistema inclui também um **fluxo de empréstimo de equipamentos**, onde o equipamento é tratado como uma ordem de serviço com um modelo sequencial de tarefas (empréstimo → devolução).

---

## 2. Público-Alvo

| Perfil | Descrição | Permissões Principais |
|--------|-----------|----------------------|
| **Administrador** (admin) | Gestor máximo do sistema | CRUD de utilizadores, funções, permissões, config. sistema |
| **Gestor** (manager) | Responsável por ordens de serviço | Criar OS, tarefas, sector assignments, aprovar work logs |
| **Supervisor** (supervisor) | Chefe de secção | Gerir mini-tarefas, atribuir trabalhadores, aprovar work logs |
| **Trabalhador** (worker) | Executante no terreno | Registar work logs, consumir materiais, usar equipamentos |
| **Cliente** | Cidadão / entidade externa | Registar pedidos (via gestor) — sem acesso direto ao sistema |

---

## 3. Capacidades Principais e Regras de Negócio

### 3.1 Hierarquia de Execução (Cascade Completion Chain)

```
Ordem de Serviço (ServiceOrder)
  └── Tarefas (Tasks) — atribuídas a sectores
        └── Mini-Tarefas (MiniTasks) — atribuídas a trabalhadores/grupos
              └── Registos de Trabalho (WorkLogs) — execução real
```

**Regra de conclusão em cascata:**
> Uma Ordem de Serviço só fica concluída quando **todas as tarefas** estão concluídas.  
> Uma tarefa só fica concluída quando **todas as mini-tarefas** estão concluídas.  
> Uma mini-tarefa só fica concluída quando **todos os work logs** estão aprovados.

### 3.2 Fluxo de Aprovação de Work Logs

Os registos de trabalho seguem uma máquina de estados rigorosa:

```
in_progress → submitted → approved
                        → rejected
```

- O trabalhador regista o trabalho (`in_progress`)
- Submete para aprovação (`submitted`)
- O supervisor/gestor aprova (`approved`) ou rejeita (`rejected`)
- A rejeição permite correção e re-submissão

### 3.3 Fluxo de Empréstimo de Equipamentos (Loan Workflow)

Quando uma Ordem de Serviço é do tipo `loan` (empréstimo):

1. **Criação automática** da Tarefa 1: "Empréstimo de Equipamento"
2. **Criação sob demanda** da Tarefa 2: "Devolução de Equipamento" (via `POST /api/service-orders/{so}/initiate-return`)
3. O equipamento transita entre estados: `active → in_use → active`
4. A conclusão da OS fica **bloqueada** até a Tarefa 2 ser criada e concluída
5. Não é possível adicionar mais de 2 tarefas a uma OS de empréstimo

### 3.4 Controlo de Acesso (RBAC)

- Sistema fechado: **sem registo público** — apenas administradores criam utilizadores
- Permissões granulares por recurso e ação (ex.: `view_tasks`, `create_service_orders`)
- Autorização centralizada via `BasePolicy` com métodos `isAdmin()`, `isOwner()`, `hasPermission()`
- Tokens Sanctum para autenticação de API

### 3.5 Gestão de Materiais e Equipamentos

- **Materiais**: stock com quantidade mínima, consumidos em work logs (via `work_logs_materials`)
- **Equipamentos**: ativos fixos ou emprestáveis, com revisões periódicas e rastreamento de empréstimos (via `work_log_equipment`)

### 3.6 Hierarquia Geográfica

```
Distrito (District)
  └── Município (Municipality)
        └── Freguesia (Parish)
              └── Localização (Location) — morada completa + coordenadas
```

---

## 4. Funcionalidades Implementadas

### 4.1 Backend (Laravel 12) — Completo

| # | Funcionalidade | Estado | Componentes |
|---|---------------|--------|-------------|
| 1 | **Autenticação** | ✅ Completo | Login, logout, me (Sanctum tokens) |
| 2 | **Administração** | ✅ Completo | CRUD utilizadores, CRUD funções (roles), permissões |
| 3 | **Clientes** | ✅ Completo | CRUD com NIF único, perfil vinculado a utilizador |
| 4 | **Tipos de Serviço** | ✅ Completo | CRUD de categorias de serviço |
| 5 | **Localizações** | ✅ Completo | CRUD com georreferenciação (latitude/longitude) |
| 6 | **Materiais** | ✅ Completo | CRUD com unidades de medida e stock |
| 7 | **Unidades** | ✅ Completo | CRUD de unidades de medida (kg, m, h, etc.) |
| 8 | **Sectores** | ✅ Completo | CRUD com chefe de secção |
| 9 | **Equipas** | ✅ Completo | CRUD vinculado a sectores |
| 10 | **Trabalhadores** | ✅ Completo | CRUD com perfil vinculado a utilizador |
| 11 | **Ordens de Serviço** | ✅ Completo | CRUD + cancelar + concluir + fluxo de empréstimo |
| 12 | **Tarefas** | ✅ Completo | CRUD + cancelar + atribuição a sectores |
| 13 | **Mini-Tarefas** | ✅ Completo | CRUD + concluir + atribuição a trabalhadores/grupos |
| 14 | **Work Logs** | ✅ Completo | CRUD + aprovar/rejeitar + máquina de estados + consumir materiais |
| 15 | **Notificações** | ✅ Completo | Listar, marcar como lida |
| 16 | **Exportação CSV** | ✅ Completo | Exportar OS e Work Logs com filtros (StreamedResponse) |
| 17 | **Configurações** | ✅ Completo | AppSettings (admin-only), UserPreferences (owner-scoped) |
| 18 | **Equipamentos** | ✅ Completo | CRUD + revisões periódicas + vínculo a OS de empréstimo |
| 19 | **Anexos** | ✅ Completo | Upload/delete com autorização (polimórfico: OS / MiniTask) |
| 20 | **Geográfico (só leitura)** | ✅ Completo | Districts, Municipalities, Parishes com eager loading |

### 4.2 Infraestrutura Central

| Componente | Quantidade | Descrição |
|------------|-----------|-----------|
| **Enums** | 8 | `UserRole`, `TaskStatus`, `WorkLogStatus`, `MiniTaskStatus`, `ServicesOrdersPriority`, `PermissionAction`, `PermissionResource`, `SystemStatus` |
| **Traits** | 6 | `Base` (UUID), `Timestamped`, `Publishing`, `Filterable`, `ExportCsv`, `Completable` |
| **Core Services** | 4 | `PermissionManager`, `CacheManager`, `FilterService`, `TransactionHandler` |
| **Helpers** | 4 | `ValidationHelper`, `InputSanitizer`, `FormattingHelper`, `FeatureFlags` |
| **Middleware** | 4 | `AuthenticateApi`, `CheckSoftDeletedUser`, `EnsureEmailVerified`, `SetUserLocale` |
| **Policies** | 18+ | `BasePolicy` + policies específicas por funcionalidade |
| **Eventos** | 5 pares | Cascade completion chain (WorkLog → MiniTask → Task → ServiceOrder) |
| **Migrations** | 32 | Schema completo com 32 tabelas, índices estratégicos, chaves estrangeiras |
| **Seeders** | 2 | `GeographicDataSeeder` (distrito de Viseu), `DevelopmentTestSeeder` (dados de teste) |
| **Ficheiros de rotas** | 20 | Rotas API agrupadas por funcionalidade em `routes/api/` |

### 4.3 Frontend (React + Inertia.js) — Em Desenvolvimento

| Funcionalidade | Estado | Componentes |
|---------------|--------|-------------|
| **Dashboard** | ✅ Completo | Página inicial com visão geral |
| **Autenticação** | ✅ Completo | Páginas de login, registo, verificação email, alterar password |
| **Clientes** | ✅ Completo | Lista, detalhe, formulário, edição |
| **Ordens de Serviço** | ✅ Parcial | Drawer com abas, árvore de tarefas, lista de materiais/equipamentos |
| **Sidebar** | ✅ Completo | Navegação agrupada com ícones Lucide, badges Dev Preview |
| **Admin** | ⏳ Pendente | Páginas de administração |
| **Exportações** | ⏳ Pendente | Página placeholder (Dev Preview) |
| **Notificações** | ⏳ Pendente | Página placeholder (Dev Preview) |
| **Analytics** | ⏳ Pendente | Página placeholder (Dev Preview) |
| **Equipamentos** | ⏳ Pendente | Página placeholder (Dev Preview) |

### 4.4 Base de Dados — 32 Tabelas

**Tabelas Principais (22):**
- `users`, `roles`, `role_permissions`, `user_roles`, `user_preferences` — Utilizadores e permissões
- `clients` — Clientes
- `service_orders` — Ordens de serviço (aggregate principal)
- `tasks`, `tasks_sectors` — Tarefas e atribuição sectorial
- `mini_tasks`, `mini_tasks_workers_teams`, `mini_tasks_materials` — Mini-tarefas
- `work_logs`, `work_logs_workers`, `work_logs_materials` — Registos de trabalho
- `sectors`, `teams`, `workers` — Organização
- `districts`, `municipalities`, `parishes`, `locations` — Geografia
- `service_types`, `units`, `materials` — Dados mestres
- `equipments`, `equipment_revisions`, `work_log_equipment` — Equipamentos
- `attachments` — Anexos (polimórfico)
- `notifications` — Notificações
- `app_settings` — Configurações do sistema

---

## 5. Requisitos Técnicos Futuros

### 5.1 Imediatos (Curto Prazo)

| # | Requisito | Prioridade | Descrição |
|---|-----------|-----------|-----------|
| 1 | **Suite de Testes** | 🔴 Crítica | Implementar testes unitários, de funcionalidade e integração para todas as 16 features |
| 2 | **Completar Frontend** | 🔴 Crítica | Implementar páginas em falta: Admin, Export, Notifications, Analytics, Equipments |
| 3 | **Geração Automática de Tarefas (Loan)** | 🟡 Média | Implementar listener `CreateLoanTasks` para criar automaticamente Tarefa 1 ("Empréstimo de Equipamento") na criação de OS tipo `loan` |
| 4 | **Guard de equipamento ativo** | 🟡 Média | Validar que o equipamento está com status `active` antes de permitir empréstimo |

### 5.2 Médio Prazo

| # | Requisito | Prioridade | Descrição |
|---|-----------|-----------|-----------|
| 5 | **Documentação OpenAPI/Swagger** | 🟡 Média | Gerar documentação interativa para todas as rotas API |
| 6 | **Modo Escuro (Dark Mode)** | 🟢 Baixa | Implementar tema escuro com variáveis CSS no frontend |
| 7 | **Pesquisa Global** | 🟢 Baixa | Campo de pesquisa unificado na sidebar para OS, clientes, equipamentos |
| 8 | **Dashboard Analítico** | 🟡 Média | Gráficos e estatísticas de ordens de serviço, materiais, produtividade |
| 9 | **Notificações em Tempo Real** | 🟡 Média | WebSocket/Laravel Echo para notificações push no browser |

### 5.3 Longo Prazo

| # | Requisito | Prioridade | Descrição |
|---|-----------|-----------|-----------|
| 10 | **Auditoria e Logs** | 🟡 Média | Registo detalhado de todas as ações (já com migração `audit_logs` criada) |
| 11 | **Performance Profiling** | 🟢 Baixa | Otimização de queries N+1, caching, lazy loading |
| 12 | **Relatórios Avançados** | 🟢 Baixa | Exportação PDF, gráficos, relatórios personalizados |
| 13 | **Mobile App** | 🟢 Baixa | Aplicação móvel para trabalhadores no terreno |
| 14 | **Integração com SIG** | 🟢 Baixa | Mapas interativos com localizações de OS (já com dependência `@react-google-maps/api`) |
| 15 | **Revisão de Segurança** | 🟡 Média | Auditoria de segurança completa (OWASP Top 10) |

---

## 6. Estrutura do Projeto

```
app/
├── Core/                  # Infraestrutura central (enums, traits, services, helpers, middleware, policies)
├── Features/              # 16 features modulares
│   ├── Authentication/
│   ├── Admin/
│   ├── Clients/
│   ├── ServiceOrders/
│   ├── Tasks/
│   ├── MiniTasks/
│   ├── WorkLogs/
│   ├── Sectors/
│   ├── Teams/
│   ├── Workers/
│   ├── Materials/
│   ├── Locations/
│   ├── ServiceTypes/
│   ├── Equipments/
│   ├── Notifications/
│   ├── Export/
│   ├── Settings/
│   ├── Analytics/         # (scaffolded)
│   └── Dashboard/
├── Shared/                # Modelos e serviços partilhados
│   ├── Controllers/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   └── Services/
├── Http/
│   ├── Middleware/
│   └── Requests/
└── Providers/

database/
├── migrations/            # 32 ficheiros
├── seeders/               # 2 seeders
└── factories/             # 13 factories

resources/js/
├── Features/              # Frontend por funcionalidade
├── Components/Common/     # Componentes reutilizáveis
├── composables/           # Composables Vue (legado)
└── services/api/          # Serviços API

routes/
├── api.php                # Agregador central
├── web.php                # Rotas web (Inertia)
└── api/                   # 20 ficheiros de rotas por funcionalidade

documentation/
├── user_stories/          # Histórias de utilizador, diagramas UML, sequência, estados
├── audit/                 # Relatórios de auditoria
├── CURRENT_STRUCTURE.md
├── HISTORY_AND_STATUS.md
├── IMPLEMENTATION_ROADMAP.md
└── IMPLEMENTATION_TRACKER.md
```

---

## 7. Princípios Arquiteturais

1. **Modularidade**: Cada funcionalidade contém tudo o que precisa (DDD tático) — controllers, services, models, policies, requests, resources, routes, testes
2. **DRY Agressivo**: Lógica partilhada extraída para Core (traits, services, helpers)
3. **Segurança Primeiro**: Prepared statements (ORM), whitelisting de inputs, autorização centralizada via Policies
4. **Controllers Magros, Services Gordos**: Controllers têm < 100 linhas; Services têm < 200 linhas
5. **Modelos Leves**: Apenas relações + scopes (< 50 linhas)
6. **Event-Driven**: Cascata de conclusão via eventos (WorkLog → MiniTask → Task → ServiceOrder)
7. **UUID como PK**: Todas as tabelas usam UUID v4 como chave primária
8. **Soft Deletes**: Todas as entidades principais suportam eliminação suave
9. **Transações**: Operações multi-tabela obrigatoriamente dentro de transações DB

---

> **Manutenção**: Este documento é a fonte centralizada de especificação do projeto. Qualquer alteração significativa na arquitectura, funcionalidades ou regras de negócio deve ser reflectida aqui. Consulte [`documentation/IMPLEMENTATION_TRACKER.md`](documentation/IMPLEMENTATION_TRACKER.md) para o registo histórico de alterações.
