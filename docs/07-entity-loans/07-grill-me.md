# Grill-Me — Sistema de Empréstimos para Entidades

**Data:** 2026-05-12
**Participante:** Joel (Cliente)

---

## Decisões Tomadas

### Q1: Como representar a Entidade no sistema?
**R:** `User` + role `entidade` + perfil `Entity` (igual a `Client`/`Worker`)

### Q2: Empréstimo usa client_id ou entity_id?
**R:** `entity_id` substitui `client_id` na tabela `loan_orders`

### Q3: Datas e operador — global ou por equipamento?
**R:** Por equipamento, na tabela pivot `equipment_loan_order`:
- `start_date`, `end_date`, `needs_operator` (boolean)

### Q4: Ciclo de vida do empréstimo?
**R:** `PENDING → APPROVED → CHECKED_OUT → RETURNED` (+ `CANCELLED`)

### Q5: Campos do perfil Entity?
**R:** `entity_type` (enum), `nif`, `name`, `phone`, `location_id` (FK)

### Q6: Morada — direta ou pivot?
**R:** `location_id` direto na tabela `entities` (só uma morada)

### Q7: Operador é o quê?
**R:** É um `Worker` nosso (funcionário da empresa)

### Q8: UI da Entidade?
**R:** Dashboard próprio (`/entidade/dashboard`) com lista de empréstimos + botão "Novo Empréstimo"

### Q9: Quem aprova?
**R:** O Manager aprova (PENDING → APPROVED) e faz checkout (APPROVED → CHECKED_OUT)

### Q10: Como funciona o operador?
**R:** Entidade só diz sim/não (`needs_operator`). O Manager atribui os Workers quando aprova.

### Q11: Quantidade de equipamentos?
**R:** Cada equipamento é uma unidade individual (sem campo quantity)

### Q12: Local de entrega?
**R:** Opcional. Se não escolhido, usa a morada da Entidade (`entity.location_id`)

### Q13: Notificações?
**R:** Para depois — primeiro o core a funcionar

### Q14: Formulário — campos confirmados
**R:** Multi-select equipamentos + datas + operador toggle + local opcional + observações

### Q15: Lista de equipamentos — filtro?
**R:** Só mostrar `is_loanable = true` + `status = ACTIVE`

### Q16: Calendário de disponibilidade?
**R:** Calendário visual (tipo Google Calendar) para a Entidade ver dias ocupados

### Q17: Onde os Managers veem os pedidos?
**R:** Na página normal `/loan-orders` (já planeada), filtrada por PENDING

---

## Check de Consistência

- ✅ Entidade segue o mesmo padrão de Client/Worker (DDD consistente)
- ✅ LoanOrders adaptado em vez de recriado (DRY)
- ✅ Pivot com dates + operador (flexível por equipamento)
- ✅ Self-service + aprovação (separation of concerns)
- ✅ Disponibilidade cross-referencia loan_orders + service_orders + mini_tasks
