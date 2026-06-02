# CMMS — Sistema de Gestão de Ordens de Serviço Municipal

Plataforma web para gestão de operações de serviço municipal — desde a participação de ocorrências por cidadãos até à execução em campo e ao registo de conclusão.

## Visão Geral

Os cidadãos reportam problemas, os atendentes criam ordens de serviço, os gestores ativam e supervisionam, os gestores de sector atribuem trabalho às equipas, e os operários registam o progresso em campo. O sistema impõe uma cascata limpa: **Ordem de Serviço → Tarefa → Mini-Tarefa → Registo de Trabalho**, com a conclusão a propagar-se automaticamente para cima.

## Funcionalidades

- **Ciclo de vida de Ordens de Serviço** — pendente → em execução → aguarda aprovação → concluída, com registo de auditoria completo
- **Sistema de Tickets** — tickets de suporte convertíveis diretamente em ordens de serviço
- **Gestão de Equipamentos** — registo de ativos com revisões, tipos de contagem, controlo de disponibilidade e requisições de empréstimo
- **Rastreio de Recursos** — consumo de materiais, horas de operários e custos de equipamentos por registo de trabalho
- **RBAC** — sistema de papéis e permissões com granularidade fina aplicado por políticas por papel
- **Hierarquia Geográfica** — distrito → município → freguesia → localização em cascata, com suporte a pin Google Maps
- **Anexos** — anexos polimórficos em ordens de serviço, tarefas, mini-tarefas e registos de trabalho, analisados por um sidecar AV em sandbox antes do armazenamento
- **Notificações** — sistema de notificações in-app com despacho orientado a eventos
- **Registo de Auditoria** — histórico automático de todas as operações de mutação
- **Dashboard de Análise** — métricas operacionais por sector, equipa e período
- **Exportação CSV** — dados exportáveis de ordens de serviço e relatórios operacionais
- **Multilíngue** — Português (pt_PT) e Inglês (en) incluídos
- **Monitorização de Erros** — integração com Sentry via `VITE_SENTRY_DSN`

## Papéis

| Papel | Responsabilidade |
|---|---|
| Admin | Gestão de utilizadores e permissões |
| Gestor | Responsável pelas ordens de serviço — ativa, revê e conclui |
| Atendente | Recebe participações de cidadãos e cria ordens de serviço |
| Gestor de Tarefas | Cria e gere tarefas numa ordem de serviço |
| Gestor de Mini-Tarefas | Divide tarefas em mini-tarefas |
| Gestor de Registos de Trabalho | Revê e aprova registos de trabalho |
| Gestor de Sector | Supervisiona equipas e operários dentro de um sector |
| Gestor de Equipa | Gere a composição da equipa |
| Gestor de Equipamentos | Gere o registo de equipamentos e requisições de empréstimo |
| Gestor de Tickets | Gere tickets de suporte e converte-os em ordens de serviço |
| Supervisor | Supervisão transversal de sectores |
| Operário | Executa mini-tarefas e regista trabalho em campo |
| Cliente | Cliente externo com acesso de leitura às suas ordens de serviço |
| Entidade | Entidade externa (ex.: empresa contratada) |

## Stack Tecnológico

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | React 19, Inertia.js, Tailwind CSS v4 |
| Autenticação | Laravel Sanctum (sessão via Inertia) |
| Base de Dados | MySQL 8+ |
| Cache / Filas | Redis (ou fallback para base de dados) |
| Build | Vite 8 |
| Mapas | Google Maps JavaScript API |
| Monitorização de Erros | Sentry |
| Análise Estática | PHPStan, ESLint |
| Testes | PHPUnit, Vitest + Testing Library |

## Requisitos

- PHP 8.2+
- Node.js 20+
- MySQL 8+
- Redis
- Composer

## Instalação

### Instalação completa de uma vez

```bash
git clone https://github.com/Parz1val6g/CMMS.git
cd CMMS
composer setup
```

`composer setup` instala as dependências PHP e JS, copia `.env.example` para `.env`, gera a chave da aplicação, executa as migrações e compila o frontend.

Edite `.env` e defina as credenciais da base de dados antes de executar as migrações:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cmms
DB_USERNAME=utilizador
DB_PASSWORD=palavra_passe
```

De seguida, popule a base de dados:

```bash
php artisan migrate:fresh --seed --force
```

### Iniciar o ambiente de desenvolvimento

```bash
composer dev
```

Este comando inicia o servidor Laravel, o worker de filas, o Pail (visualizador de logs) e o Vite HMR num único processo. A aplicação fica disponível em `http://localhost:8000`.

## Variáveis de Ambiente

| Variável | Obrigatória | Descrição |
|---|---|---|
| `APP_KEY` | Sim | Gerada por `php artisan key:generate` |
| `DB_*` | Sim | Ligação MySQL |
| `REDIS_HOST` / `REDIS_PORT` | Sim | Ligação Redis |
| `SESSION_ENCRYPT` | — | `true` em produção (valor padrão) |
| `SESSION_SECURE_COOKIE` | — | `true` em produção (valor padrão) |
| `VITE_GOOGLE_MAPS_API_KEY` | Funcionalidade de mapas | Chave da Google Maps JavaScript API |
| `VITE_SENTRY_DSN` | Monitorização de erros | DSN do projeto Sentry |
| `QUEUE_CONNECTION` | — | `redis` recomendado em produção |

## Contas de Demonstração

Após popular a base de dados, estão disponíveis as seguintes contas:

| Papel | Email | Palavra-passe |
|---|---|---|
| Admin | joao.almeida@cm-mangualde.pt | password |
| Gestor | maria.pereira@cm-mangualde.pt | password |
| Atendente | ana.lima@cm-mangualde.pt | password |
| Gestor de Tarefas | sofia.marques@cm-mangualde.pt | password |
| Operário | carlos.silva@cm-mangualde.pt | password |

## Comandos Principais

```bash
composer setup                              # Instalação completa + build
composer dev                                # Iniciar stack de desenvolvimento
composer test                               # Executar suite de testes PHP
npm run lint                                # ESLint
npm run build                               # Build do frontend para produção
php artisan migrate:fresh --seed --force    # Reiniciar e repopular a base de dados
php artisan app:purge-operational-data      # Purgar dados operacionais, manter configuração
```

## Docker

```bash
composer docker-dev    # docker compose up -d --build
composer docker-down   # docker compose down
```

## Arquitetura

As funcionalidades são autocontidas em `app/Features/{Feature}/` e `resources/js/Features/{Feature}/`, cada uma com os seus próprios controladores, modelos, políticas, pedidos, rotas e recursos.

A infraestrutura transversal está em dois namespaces:

- `app/Core/` — políticas base, gestor de permissões, enums, middleware, DSL de esquemas de formulários, trait de auditoria, trait filterable
- `app/Shared/` — modelos User, Role, Attachment e hierarquia geográfica

### Cascata de Conclusão

Concluir um registo de trabalho verifica se todos os registos irmãos estão concluídos → marca a mini-tarefa como concluída → verifica as tarefas → marca a ordem de serviço como a aguardar aprovação. Cada nível dispara um evento de domínio consumido pelo nível seguinte.

### Análise de Ficheiros

Cada anexo carregado é despachado como um job `ScanAttachment` em fila. O job chama um sidecar de análise em sandbox via HTTP. Ficheiros infetados ou ilegíveis são eliminados e o utilizador que fez o carregamento recebe uma notificação in-app.

### Permissões

As permissões estão armazenadas na base de dados como tuplos `(papel, recurso, ação)` e são aplicadas pelo `PermissionManager` dentro de `BasePolicy`. Adicionar uma permissão a um papel é uma alteração de dados, não de código.

## Licença

MIT
