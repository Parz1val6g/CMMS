# UC1 — Reporte de Problema pelo Cidadão

## Visão geral

Um cidadão contacta a instituição a reportar um problema. O Atendente regista a Ordem de Serviço. O Gestor ativa-a, criando Tarefas por setor. O Gestor de Tarefa divide em Mini-Tarefas com trabalhadores, equipas, materiais e equipamentos. O Trabalhador executa e cria Work Logs. A conclusão propaga-se em cascata de baixo para cima.

---

## 1. Papéis (Roles)

| # | Role | Slug | Função |
|---|------|------|--------|
| 1 | Admin | `admin` | Sistema: utilizadores, roles, configurações |
| 2 | Atendente | `attendant` | Recebe chamadas, cria SOs |
| 3 | Gestor | `manager` | Dono da SO: setores, ativação, revisão, conclusão |
| 4 | Gestor de Tarefa | `task_manager` | Dono da Tarefa: mini-tarefas, atribuições, aprovação |
| 5 | Trabalhador | `worker` | Executa mini-tarefas, cria work logs |
| 6 | Cliente | `client` | Cidadão — sem acesso ao sistema atualmente |
| 7 | Gestor de Setor | `sector_manager` | Org: gere equipas e trabalhadores do setor |
| 8 | Gestor de Equipa | `team_manager` | Org: gere composição das equipas |

**Roles eliminados em UC1:** `mini_task_manager`, `work_log_manager`, `supervisor`.

**Nota:** Um utilizador pode ter múltiplos papéis (ex.: ser Gestor de Setor e Gestor de Tarefa).

---

## 2. Handoff limpo (D1)

Princípio: nível superior tem **read-only** sobre os inferiores. Cada nível tem autoridade **exclusiva de escrita** sobre o seu recurso.

| Papel | SO | Tarefas | Mini-Tarefas | Work Logs | Notas |
|---|---|---|---|---|---|
| Gestor SO | write | read (estado) | — | — | SO |
| Gestor Tarefa | read | write | read (estado) | — | Tarefa |
| Trabalhador | — | read (atribuídas) | write (atribuídas) | write | Work Log |
| Atendente | read (suas SOs) + notas | — | — | — | SO (suas) |

---

## 3. Estados

### 3.1 Ordem de Serviço

```
Pendente → Ativa → Em Progresso → Aguarda Revisão → Concluída
```

| Estado | Transição | Quem/Gatilho |
|---|---|---|
| Pendente | Estado inicial ao criar | Atendente |
| Ativa | Após ativação | Gestor (manual) |
| Em Progresso | Primeiro Work Log aberto em qualquer Tarefa | Sistema (automático) |
| Aguarda Revisão | Todas as Tarefas concluídas ou canceladas | Sistema (automático) |
| Concluída | Gestor aprova | Gestor (manual) |
| Aguarda Revisão → Em Progresso | SO rejeitada (adicionar setores ou reabrir tarefas) | Gestor (manual) |

- **Cancelamento:** não disponível atualmente.
- **Rejeição:** O Gestor pode rejeitar a SO em Aguarda Revisão. Motivos: adicionar setores (cria novas tarefas) ou reabrir tarefas específicas. Tarefas não afetadas mantêm-se concluídas. O Gestor escolhe quais reabrir.

### 3.2 Tarefa

```
Pendente → Em Progresso → Aguarda Aprovação → Concluída
                                      ↕
                                   Rejeitada → Em Progresso
```

| Estado | Transição | Quem/Gatilho |
|---|---|---|
| Pendente | Criada na ativação da SO ou adicionada depois | Sistema ou Gestor SO |
| Em Progresso | Primeiro Work Log aberto em qualquer Mini-Tarefa | Sistema (automático) |
| Aguarda Aprovação | Todas as Mini-Tarefas concluídas | Sistema (automático) |
| Concluída | Gestor de Tarefa aprova | Gestor de Tarefa (manual) |
| Rejeitada | Gestor de Tarefa rejeita | Gestor de Tarefa (manual) |
| Rejeitada → Em Progresso | Mini-Tarefas reabertas | Sistema (automático) |

- **Cancelamento:** Ambos podem cancelar. Gestor de Tarefa (setor não consegue executar). Gestor SO (setor deixou de ser necessário). Tarefa cancelada conta como resolvida.
- **Rejeição:** Gestor de Tarefa rejeita. Escolhe quais mini-tarefas reabrir e pode adicionar novas. As mini-tarefas reabertas voltam a Em Progresso. Os Work Logs são mantidos.

### 3.3 Mini-Tarefa

```
Pendente → Em Progresso → Concluída
```

| Estado | Transição | Quem/Gatilho |
|---|---|---|
| Pendente | Criada pelo Gestor de Tarefa | Gestor de Tarefa |
| Em Progresso | Primeiro Work Log aberto | Sistema (automático) |
| Concluída | Trabalhador marca como concluída | Trabalhador (manual) |

- **Sem aprovação** ao nível da mini-tarefa. O Trabalhador marca como concluída e pronto.
- **Reabertura:** Só pelo Gestor de Tarefa (via rejeição da Tarefa). O Trabalhador pode reabrir uma mini-tarefa que ele próprio concluiu, desde que não haja outras mini-tarefas concluídas na mesma Tarefa.
- **Divisão:** O Gestor de Tarefa pode dividir uma mini-tarefa em duas, desde que ainda esteja Pendente (sem Work Logs).

### 3.4 Work Log

```
Aberto → Fechado
```

| Estado | Transição | Quem/Gatilho |
|---|---|---|
| Aberto | Trabalhador inicia (timestamp início) | Trabalhador (manual) |
| Fechado | Trabalhador fecha (timestamp fim) | Trabalhador (manual) |

- Trabalhador pode ter múltiplas mini-tarefas atribuídas, mas **apenas 1 Work Log Aberto** de cada vez.

---

## 4. Fluxo completo

### 4.1 Criação da SO (Atendente)

O Atendente cria a SO com os seguintes campos:

| Campo | Tipo | Obrigatório |
|---|---|---|
| Descrição do problema | Texto | Sim |
| Data de início proposta | Data | Sim |
| Localização | Morada/Freguesia/Código Postal/Coordenadas | Sim |
| Cliente (cidadão) | Referência a Cliente | Sim |
| Tipo de serviço | Seleção de lista pré-definida | Sim |
| Prioridade sugerida | Baixa/Média/Alta/Urgente | Sim |
| Gestor responsável | Seleção de Gestor | Sim |
| Anexos | Ficheiros (fotos, docs) | Não |

O Atendente **não** escolhe setores.

### 4.2 Pós-criação (Atendente)

- Pode editar ou apagar a SO enquanto **Pendente**.
- Pode ver e adicionar **notas** nas SOs que criou.
- Vê na listagem apenas as SOs que ele próprio criou.
- Não vê Tarefas, Mini-Tarefas, nem Work Logs.
- Não pode alterar o estado da SO.

### 4.3 Preparação (Gestor)

O Gestor, ao receber a SO Pendente:

1. Revê os dados introduzidos pelo Atendente
2. Seleciona os **setores** envolvidos (multi-seleção)
3. Define a **data de fim prevista**
4. Ajusta a **prioridade** se necessário
5. Pode adicionar **notas** e **anexos**

### 4.4 Ativação (Gestor)

- Ação atómica: um botão. Muda estado para Ativa e cria automaticamente uma Tarefa por setor selecionado.
- Se um setor não tiver Gestor de Setor, a Tarefa é criada na mesma e fica **não atribuída**. O Gestor da SO vê o alerta e atribui depois.
- Cada Tarefa é atribuída por omissão ao **Gestor de Setor** do setor. O Gestor da SO pode reatribuir.
- Podem ser adicionados setores depois da ativação, criando Tarefas adicionais.
- A SO não pode ser reatribuída a outro Gestor depois de Ativa. Só enquanto Pendente.

### 4.5 Planeamento (Gestor de Tarefa)

Para cada Tarefa que lhe é atribuída:

1. Divide a Tarefa em **Mini-Tarefas**, cada uma com:
   - Descrição
   - Data de início prevista
   - Data de fim prevista
   - Trabalhadores atribuídos (múltiplos)
   - Equipas atribuídas (múltiplas) — pode ter trabalhadores E equipas na mesma mini-tarefa
   - Materiais planeados (estimativa)
   - Equipamentos planeados (estimativa)
2. Define manualmente a data de início e fim prevista da Tarefa.

### 4.6 Execução (Trabalhador)

1. Vê as Mini-Tarefas onde está atribuído
2. **Inicia trabalho:** Cria Work Log (Aberto) com timestamp de início, descrição, materiais e equipamentos em uso
3. **Termina trabalho:** Fecha Work Log com timestamp de fim e materiais/equipamentos efetivamente usados
4. Quando termina tudo, marca a **Mini-Tarefa como Concluída**

### 4.7 Revisão e aprovação (Gestor de Tarefa)

- Quando todas as Mini-Tarefas estão Concluídas → a Tarefa transita automaticamente para **Aguarda Aprovação**
- O Gestor de Tarefa **revê** (vê os estados das Mini-Tarefas — não vê Work Logs)
- **Aprova:** Tarefa → Concluída
- **Rejeita:** Escolhe Mini-Tarefas a reabrir + pode adicionar novas → Tarefa → Em Progresso

### 4.8 Conclusão (Gestor da SO)

- Quando todas as Tarefas estão Concluídas ou Canceladas → a SO transita automaticamente para **Aguarda Revisão**
- O Gestor **revê** (vê os estados das Tarefas — não vê Mini-Tarefas nem Work Logs)
- **Conclui:** SO → Concluída
- **Rejeita:** Para adicionar setores (cria novas Tarefas) ou reabrir Tarefas específicas

### 4.9 Alerta de data de início

- Quando a SO atinge a data de início proposta e ainda está Pendente → o sistema notifica o Gestor.
- Sem ação automática. O Gestor decide quando ativar.

---

## 5. Regras de permissão

### 5.1 Visibilidade

| Papel | Vê SO | Vê Tarefas | Vê Mini-Tarefas | Vê Work Logs |
|---|---|---|---|---|
| Admin | Todas | Todas | Todas | Todos |
| Gestor SO | Suas SOs | Suas SOs (estado) | — | — |
| Gestor Tarefa | Suas (read-only) | Suas | Suas Tarefas (estado) | — |
| Trabalhador | — | Onde está atribuído (read-only) | Onde está atribuído | Seus |
| Atendente | SOs que criou | — | — | — |
| Gestor Setor | — | Setor (se também for Gestor Tarefa) | — | — |
| Gestor Equipa | — | — | — | — |
| Cliente | — | — | — | — |

### 5.2 Notas

| Papel | Onde escreve notas |
|---|---|
| Atendente | SOs que criou |
| Gestor SO | Suas SOs |
| Gestor Tarefa | Suas Tarefas |
| Trabalhador | Seus Work Logs |

### 5.3 Anexos

Cada papel pode anexar ficheiros ao recurso que gere:
- Atendente e Gestor → SO
- Gestor de Tarefa → Tarefa
- Trabalhador → Work Log

### 5.4 Atribuição de tarefas

- Por omissão: Gestor de Setor = Gestor de Tarefa (mesmo utilizador com ambos os papéis)
- Gestor SO pode reatribuir a Tarefa a outro Gestor de Tarefa
- No seletor de reatribuição: lista todos os Gestores de Tarefa, com os do setor primeiro

---

## 6. Regras de negócio adicionais

### 6.1 Work Logs

- Trabalhador só pode ter **1 Work Log Aberto** de cada vez (pode ter múltiplas mini-tarefas atribuídas)
- Work Log Aberto → timestamp de início preenchido, timestamp de fim vazio
- Work Log Fechado → ambos os timestamps preenchidos

### 6.2 Conclusão em cascata

```
Work Logs fechados (todos)
  → Trabalhador marca Mini-Tarefa Concluída (manual)
    → Todas as Mini-Tarefas Concluídas
      → Tarefa → Aguarda Aprovação (automático)
        → Gestor de Tarefa aprova (manual) → Tarefa Concluída
          → Todas as Tarefas Concluídas/Canceladas
            → SO → Aguarda Revisão (automático)
              → Gestor conclui (manual) → SO Concluída
```

### 6.3 Rejeição e reabertura

- **Tarefa rejeitada:** Gestor de Tarefa escolhe quais mini-tarefas reabrir. Work Logs mantidos. Tarefa volta a Em Progresso automaticamente quando há mini-tarefas reabertas.
- **SO rejeitada:** Gestor escolhe entre adicionar setores (cria novas Tarefas) ou reabrir Tarefas específicas. Tarefas não afetadas mantêm-se concluídas.
- Mini-Tarefas reabertas voltam a **Em Progresso** (não a Pendente).
- Trabalhador pode reabrir a própria mini-tarefa concluída, desde que não haja outras mini-tarefas concluídas na Tarefa.

### 6.4 Cancelamento

- Tarefa pode ser cancelada pelo Gestor de Tarefa ou pelo Gestor da SO
- Tarefa cancelada conta como "resolvida" para conclusão da SO
- SO atualmente não pode ser cancelada

### 6.5 Transições de estado automáticas

- SO: Pendente → Ativa (manual). Ativa → Em Progresso (primeiro Work Log aberto). Em Progresso → Aguarda Revisão (todas Tarefas concluídas/canceladas). Aguarda Revisão → Concluída (manual).
- Tarefa: Pendente → Em Progresso (primeiro Work Log aberto). Em Progresso → Aguarda Aprovação (todas Mini-Tarefas concluídas). Aguarda Aprovação → Concluída (manual). Rejeitada → Em Progresso (mini-tarefas reabertas, automático).
- Mini-Tarefa: Pendente → Em Progresso (primeiro Work Log aberto). Em Progresso → Concluída (manual).
- Work Log: Aberto → Fechado (manual).

### 6.6 Auditoria

- Cada transição de estado regista: quem, quando, estado anterior, estado seguinte.
- Transições automáticas são registadas como "sistema".

---

## 7. Campos da Ordem de Serviço

| Campo | Preenchido por | Momento |
|---|---|---|
| Descrição | Atendente | Criação |
| Data início proposta | Atendente | Criação |
| Data fim prevista | Gestor | Antes/na ativação |
| Localização | Atendente | Criação |
| Cliente | Atendente | Criação |
| Tipo de serviço | Atendente | Criação |
| Prioridade | Atendente (sugestão), Gestor (final) | Criação / Antes ativar |
| Setores | Gestor | Antes/depois ativação |
| Gestor responsável | Atendente | Criação |
| Estado | Sistema | Automático/manual |
| Anexos | Atendente, Gestor | Qualquer momento |
| Notas | Atendente, Gestor | Qualquer momento |
