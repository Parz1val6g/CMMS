# User Stories: Clients

---

## 👥 US-029: Criar Cliente Novo

**Como** manager,  
**Eu quero** registar um novo cliente,  
**Para que** eu possa criar service orders para ele.

### Critérios de Aceitação
- ✅ POST /clients com: nif (tax ID), user_id (manager)
- ✅ NIF: validação formato PT (9 dígitos), único
- ✅ User_id: deve ser user existente com role manager/admin
- ✅ Cliente criado com status=active
- ✅ Auditoria: quem criou, timestamp
- ✅ Email de notificação ao cliente (opcional)

### Notas
- NIF formato: 123456789 (9 dígitos)
- Manager: quem registou é o responsável principal
- Cliente pode ser ligado a user existente ou não registado

---

## 👥 US-030: Listar Clientes

**Como** manager ou admin,  
**Eu quero** ver lista de clientes,  
**Para que** eu possa procurar e gerenciar.

### Critérios de Aceitação
- ✅ GET /clients com paginação (20 por página)
- ✅ Filtros:
  - search (name, email, nif)
  - manager_id (se admin, ver todos; se manager, apenas seus)
  - status (active, inactive)
  - data_creation (range)
- ✅ Campos: id, nif, manager, status, created_at, service_orders_count, locations_count
- ✅ Manager vê apenas seus clientes
- ✅ Admin vê todos

### Notas
- Eager load: manager relations, orders count
- Filtro manager: automático (se !admin, manager_id = auth()->id())

---

## 👥 US-031: Visualizar Detalhes de Cliente

**Como** manager ou admin,  
**Eu quero** ver informações completas de um cliente,  
**Para que** eu possa revisar histórico e ordens.

### Critérios de Aceitação
- ✅ GET /clients/{id} retorna:
  - Dados básicos: nif, manager, status, criação
  - Localizações associadas (1:M)
  - Service orders (últimas 10)
  - Contatos/emails relacionados
  - Histórico de atividade (últimas 20)
- ✅ Authorization: owner (manager) ou admin
- ✅ Soft-deleted: mostrar se admin
- ✅ Cache: 10 min (invalida ao atualizar cliente)

---

## 👥 US-032: Editar Cliente

**Como** manager ou admin,  
**Eu quero** atualizar dados de um cliente,  
**Para que** informações permaneçam atualizadas.

### Critérios de Aceitação
- ✅ PUT /clients/{id} com: nif (opcional), status (opcional)
- ✅ NIF: validação única (exceto self)
- ✅ Apenas owner (manager) ou admin
- ✅ Auditoria: mudanças registadas (campo antigo → novo)
- ✅ Se status changed: notificar cliente

### Casos de Teste
| Ação | Esperado | Resultado |
|------|----------|-----------|
| Atualizar status → inactive | ✅ Cliente inativo, email enviado | |
| Alterar NIF para duplicado | ❌ Erro validação | |
| Manager edita cliente de outro | ❌ Erro autorização | |

---

## 👥 US-033: Deletar Cliente (Soft Delete)

**Como** admin,  
**Eu quero** remover um cliente do sistema,  
**Para que** eu possa limpar dados.

### Critérios de Aceitação
- ✅ DELETE /clients/{id} (soft delete, marked with deleted_at)
- ✅ Verificar: se tem service_orders pending → erro (não permitir)
- ✅ Se completed orders: permitir (dados preservados via soft delete)
- ✅ Auditoria: quem deletou, quando
- ✅ Apenas admin

### Notas
- Validação: cliente com orders pending não pode ser deletado
- Soft delete preserva auditoria

---

## 👥 US-034: Restaurar Cliente Deletado (Admin)

**Como** admin,  
**Eu quero** restaurar um cliente previamente deletado,  
**Para que** eu possa recuperar dados.

### Critérios de Aceitação
- ✅ POST /clients/{id}/restore (restaura soft-deleted)
- ✅ Status retorna a ativo
- ✅ Auditoria: restauração registada
- ✅ Apenas admin

---

## 📍 US-035: Ver Localizações de Cliente

**Como** manager,  
**Eu quero** listar todos locations associados a um cliente,  
**Para que** eu possa planejar service orders.

### Critérios de Aceitação
- ✅ GET /clients/{id}/locations retorna:
  - Todas locations onde cliente tem service orders
  - Ou registos adicionais (optional, se houver)
  - Campos: address, parish, municipality, district, coordinates
- ✅ Paginação se muitas
- ✅ Authorization: owner ou admin

---

## 📋 US-036: Ver Service Orders de Cliente

**Como** manager,  
**Eu quero** ver todas service orders de um cliente,  
**Para que** eu possa rastrear trabalho.

### Critérios de Aceitação
- ✅ GET /clients/{id}/service-orders retorna:
  - Todas orders do cliente (paginated)
  - Status, prioridade, data, manager
  - Últimas 10 por default, com paginação
- ✅ Filtros: status, data, prioridade
- ✅ Authorization: owner ou admin
- ✅ Eager load: location, service_type

---

## 👁️ US-037: Procurar Cliente por NIF

**Como** receptionist ou manager,  
**Eu quero** procurar cliente pelo NIF,  
**Para que** eu possa localizar rapidamente.

### Critérios de Aceitação
- ✅ GET /clients?search={nif} or endpoint específico
- ✅ Retorna cliente se encontrado, ou lista parciais (search)
- ✅ NIF partial search (ex: 123456... retorna todos com prefix)
- ✅ Rápido (indexed search)

### Notas
- NIF index já existe em DB
- Usar full-text search ou LIKE otimizado

---

## 🔗 US-038: Associar Cliente com Utilizador Existente

**Como** admin,  
**Eu quero** linkar um cliente a um utilizador registado,  
**Para que** cliente possa ter conta com login.

### Critérios de Aceitação
- ✅ PUT /clients/{id} com: user_id (novo user para gerir)
- ✅ Validação: user_id deve existir, único por cliente (1:1 possible)
- ✅ User pode receber múltiplos clientes (1:M)
- ✅ Auditoria: mudança registada

### Notas
- Opcional: cliente pode não ter user (apenas para ordens anónimas)

---

## 📊 US-039: Exportar Clientes para CSV

**Como** manager,  
**Eu quero** exportar lista de clientes para CSV,  
**Para que** eu possa analisar em Excel.

### Critérios de Aceitação
- ✅ GET /export/clients?format=csv
- ✅ Campos: id, nif, manager, status, created_at, orders_count
- ✅ Filtros aplicados (mesmos de US-030)
- ✅ Max rows: 10k (paginação se precisar mais)
- ✅ Apenas manager vê seus clientes (exceto admin)

### Notas
- Use ExportCsv trait
- Encoding: UTF-8
- Delay: stream se > 1k rows

---
