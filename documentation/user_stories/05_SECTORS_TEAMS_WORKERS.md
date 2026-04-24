# User Stories: Sectors, Teams & Workers

---

## 🏢 US-061: Criar Setor Novo

**Como** admin,  
**Eu quero** criar um novo setor (secção de trabalho),  
**Para que** eu possa organizar equipes.

### Critérios de Aceitação
- ✅ POST /sectors com: name, head_id (manager/supervisor user)
- ✅ Name: max 100 chars
- ✅ Head_id: user válido com role manager ou supervisor
- ✅ Status: active (default)
- ✅ Auditoria: criador, timestamp

### Exemplos
- "Trolhas", "Esgotos", "Eletricistas", "Jardinagem"

---

## 🏢 US-062: Listar Setores

**Como** cualquier utilizador,  
**Eu quero** ver todos os setores,  
**Para que** eu possa escolher ao criar tasks.

### Critérios de Aceitação
- ✅ GET /sectors com paginação
- ✅ Filtros: search (name), status, head_id
- ✅ Campos: id, name, head (user info), teams_count, workers_count, tasks_count
- ✅ Apenas active por default (opção include archived)

---

## 🏢 US-063: Visualizar Detalhes de Setor

**Como** manager,  
**Eu quero** ver informações completas de um setor,  
**Para que** eu possa revisar equipe e trabalhos.

### Critérios de Aceitação
- ✅ GET /sectors/{id} retorna:
  - Nome, chefe (user info)
  - Todas teams associadas (com worker counts)
  - Últimas tasks atribuídas (10)
  - Workers totais (diretos + via teams)
  - Tasks concluídas vs pending
  - Agregados: horas trabalhadas, materiais usados (by period)

---

## 🏢 US-064: Editar Setor

**Como** admin,  
**Eu quero** atualizar informações de um setor,  
**Para que** dados permaneçam corretos.

### Critérios de Aceitação
- ✅ PUT /sectors/{id} com: name, head_id (novo chefe), status
- ✅ Name: unique
- ✅ Head_id: user válido com role apropriado
- ✅ Auditoria: mudanças registadas
- ✅ Se head mudou: notificar novo chefe

---

## 🏢 US-065: Deletar Setor

**Como** admin,  
**Eu quero** remover um setor não utilizado,  
**Para que** sistema limpo.

### Critérios de Aceitação
- ✅ DELETE /sectors/{id} (soft delete)
- ✅ Verificar: se tem teams/tasks active → erro (não permitir)
- ✅ Apenas admin

---

## 👥 US-066: Criar Equipa

**Como** sector head ou admin,  
**Eu quero** criar uma nova equipa dentro de um setor,  
**Para que** eu possa atribuir trabalhos.

### Critérios de Aceitação
- ✅ POST /teams com: sector_id, name
- ✅ Sector_id: válido, setor ativo
- ✅ Name: max 100 chars, unique per sector
- ✅ Status: active (default)
- ✅ Auditoria: criador
- ✅ Authorization: sector head ou admin

---

## 👥 US-067: Listar Equipas

**Como** manager ou supervisor,  
**Eu quero** ver todas as equipas,  
**Para que** eu possa atribuir trabalhos.

### Critérios de Aceitação
- ✅ GET /teams com paginação
- ✅ Filtros: sector_id, search (name), status
- ✅ Campos: id, name, sector, workers_count, tasks_count, availability
- ✅ Apenas active por default

### Notas
- Availability: calculated based on open mini-tasks (workers/team)

---

## 👥 US-068: Visualizar Detalhes de Equipa

**Como** manager,  
**Eu quero** ver informações completas de uma equipa,  
**Para que** eu possa revisar composição.

### Critérios de Aceitação
- ✅ GET /teams/{id} retorna:
  - Nome, setor, status
  - Todos workers (com user info, role)
  - Mini-tasks atribuídas (open + recent)
  - Horas trabalhadas (by period)
  - Rendimento (tasks completed vs pending)

---

## 👥 US-069: Adicionar Trabalhador a Equipa

**Como** sector head,  
**Eu quero** atribuir um trabalhador a uma equipa,  
**Para que** ele possa receber trabalhos da equipa.

### Critérios de Aceitação
- ✅ POST /teams/{id}/workers com: worker_id
- ✅ Worker_id: válido, não já em outra team
- ✅ Validação: worker não pode estar em 2 teams simultaneamente (opcional business rule)
- ✅ Auditoria: quem adicionou, quando
- ✅ Authorization: sector head da team's sector ou admin

### Notas
- Business rule: 1 worker = 1 team (current implementação)
- Se preciso multi-team: adicionar field workers.team_id pode ter múltiplos

---

## 👥 US-070: Remover Trabalhador de Equipa

**Como** sector head,  
**Eu quero** remover um trabalhador de uma equipa,  
**Para que** ele deixe de receber trabalhos da equipa.

### Critérios de Aceitação
- ✅ DELETE /teams/{id}/workers/{workerId}
- ✅ Validação: se tem mini-tasks open → warning (não permitir até concluir)
- ✅ Auditoria: quem removeu, quando
- ✅ Authorization: sector head ou admin

---

## 👥 US-071: Editar Equipa

**Como** admin,  
**Eu quero** atualizar informações de uma equipa,  
**Para que** dados fiquem corretos.

### Critérios de Aceitação
- ✅ PUT /teams/{id} com: name, sector_id (mover para outro setor), status
- ✅ Name: unique per sector
- ✅ Auditoria: mudanças registadas

---

## 👥 US-072: Deletar Equipa

**Como** admin,  
**Eu quero** remover uma equipa não utilizada,  
**Para que** sistema limpo.

### Critérios de Aceitação
- ✅ DELETE /teams/{id} (soft delete)
- ✅ Verificar: se tem mini-tasks open → erro (não permitir)
- ✅ Apenas admin

---

## 👷 US-073: Registar Novo Trabalhador

**Como** admin,  
**Eu quero** criar perfil de trabalhador para um user,  
**Para que** ele possa receber e registar trabalho.

### Critérios de Aceitação
- ✅ POST /workers com: user_id, team_id (optional)
- ✅ User_id: user válido, unique (1:1 user→worker)
- ✅ Team_id: team válido, optional (pode ser solo ou em team)
- ✅ Status: active (default)
- ✅ Auditoria: criador, timestamp

### Notas
- Worker é a representação de um user como executante de trabalho
- Pode estar em 1 team ou nenhum (solo)

---

## 👷 US-074: Listar Trabalhadores

**Como** manager ou supervisor,  
**Eu quero** ver lista de trabalhadores,  
**Para que** eu possa atribuir tasks.

### Critérios de Aceitação
- ✅ GET /workers com paginação
- ✅ Filtros: team_id, sector_id, search (name), status, availability
- ✅ Campos: id, user (name), team, sector, status, tasks_open, hours_worked
- ✅ Apenas active por default
- ✅ Ordenação: por nome ou por load (tasks open)

---

## 👷 US-075: Visualizar Perfil de Trabalhador

**Como** manager ou o próprio worker,  
**Eu quero** ver detalhes de um trabalhador,  
**Para que** eu possa revisar performance.

### Critérios de Aceitação
- ✅ GET /workers/{id} retorna:
  - User info, team (se tiver), setor
  - Mini-tasks open (com prazos)
  - Work logs recentes (últimas 20)
  - Horas totais trabalhadas (by period)
  - Materialidade (accuracy na estimativa vs actual)
  - Rating/performance (opcional: comments, feedback)
  - Availability: schedule (working hours, days off)

### Authorization
- Own worker ou sector head ou admin

---

## 👷 US-076: Editar Trabalhador

**Como** admin ou sector head,  
**Eu quero** atualizar informações de um trabalhador,  
**Para que** dados fiquem corretos.

### Critérios de Aceitação
- ✅ PUT /workers/{id} com: team_id (change team), status
- ✅ Team_id: mover para outra team (validar sem tasks open)
- ✅ Auditoria: mudanças registadas

---

## 👷 US-077: Desativar Trabalhador

**Como** admin,  
**Eu quero** desativar um trabalhador,  
**Para que** ele deixe de receber trabalhos.

### Critérios de Aceitação
- ✅ PUT /workers/{id} com: status=inactive
- ✅ Validação: se tem mini-tasks open → warning
- ✅ Auditoria: quem desativou, razão (optional)

---

## 👷 US-078: Deletar Trabalhador

**Como** admin,  
**Eu quero** remover perfil de trabalhador,  
**Para que** sistema limpo.

### Critérios de Aceitação
- ✅ DELETE /workers/{id} (soft delete)
- ✅ Validação: se tem mini-tasks open ou work_logs → erro
- ✅ User correspondente NÃO é deletado (apenas worker)

---

## 📊 US-079: Relatório de Performance de Trabalhadores

**Como** manager,  
**Eu quero** ver relatório de performance de trabalhadores,  
**Para que** eu possa identificar top performers.

### Critérios de Aceitação
- ✅ GET /reports/workers-performance?date_from=&date_to= retorna:
  - Por worker: tarefas concluídas, horas, eficiência (estimated vs actual)
  - Ranking de performance
  - Absenteeism (se integrado)
  - Custo por tarefa (materiais + horas)
- ✅ Filtros: sector, team, date_range
- ✅ Export: CSV, PDF (opcional)

---

## 📊 US-080: Disponibilidade de Trabalhador/Equipa

**Como** manager,  
**Eu quero** ver quem está disponível para novo trabalho,  
**Para que** eu possa atribuir rapidamente.

### Critérios de Aceitação
- ✅ GET /workers?available=true ou /teams?available=true
- ✅ Disponível = sem mini-tasks open OU less than max allowed
- ✅ Considerar: working hours, scheduled time off
- ✅ Retorna: id, name, current_load, estimated_free_time

### Notas
- Max tasks per worker: configurável in app_settings

---
