# Use Case 1 — Registar Ordem de Serviço a partir de uma Chamada

## Descrição

Um cidadão telefona a reportar um problema. O **Atendente** regista a Ordem de Serviço. O **Gestor** revê, selecciona sectores e activa-a. O trabalho cascata até aos Workers no terreno.

## Actores

| Actor | Ficheiro | Role no sistema | Credenciais |
|---|---|---|---|
| Atendente | [attendant.md](attendant.md) | `attendant` | `ana.lima@cm-mangualde.pt` / `password123` |
| Service Order Manager | [service-order-manager.md](service-order-manager.md) | `manager` | `maria.pereira@cm-mangualde.pt` / `password123` |
| Task Manager | [task-manager.md](task-manager.md) | `task_manager` | `sofia.marques@cm-mangualde.pt` / `password123` |
| Mini-Task Manager | [mini-task-manager.md](mini-task-manager.md) | `mini_task_manager` | `hugo.ribeiro@cm-mangualde.pt` / `password123` |
| Worker | [worker.md](worker.md) | `worker` | `antonio.ferreira@cm-mangualde.pt` / `password123` |

## Fluxo Geral

```
Cidadão liga
  └─▶ Atendente regista OS (PENDING)
        └─▶ Service Order Manager revê dados, selecciona sectores e activa OS (IN_PROGRESS)
              └─▶ Tasks criadas automaticamente por sector
                    └─▶ Task Manager acompanha e gere Tasks
                          └─▶ Mini-Task Manager cria MiniTasks + atribui Workers/Equipamentos/Materiais
                                └─▶ Worker regista Work Log (IN_PROGRESS → SUBMITTED)
                                      └─▶ Service Order Manager aprova Work Log (APPROVED)
                                            └─▶ Mini-Task Manager conclui MiniTask (COMPLETED)
                                                  └─▶ Task → AWAITING_APPROVAL
                                                        └─▶ Task Manager aprova Task (COMPLETED)
                                                              └─▶ OS → AWAITING_APPROVAL
                                                                    └─▶ Service Order Manager conclui OS (COMPLETED) ✓
```

## Pré-requisitos

- Stack a correr: `php artisan serve` + `php artisan queue:listen` + `npm run dev`
- Base de dados com seed: `php artisan migrate:fresh --seed --force`
- URL: `http://localhost:8000`
