# User Stories: Roles & Permissions (RBAC)

---

## 🔐 US-019: Criar Role Novo

**Como** administrador,  
**Eu quero** criar um novo papel (role) no sistema,  
**Para que** eu possa definir novos níveis de acesso.

### Critérios de Aceitação
- ✅ POST /roles com: name, columns (opcional, para UI)
- ✅ Name: único, obrigatório, max 50 chars
- ✅ Columns: JSON array de nomes de colunas visíveis (usability)
- ✅ Role criado com permissions iniciais vazias
- ✅ Auditoria: quem criou

### Notas
- Roles pré-existentes: admin, manager, supervisor, worker, pending
- Columns field para customização UI (quais campos mostrar por role)

---

## 🔐 US-020: Listar Roles

**Como** administrador,  
**Eu quero** ver todos os roles disponíveis,  
**Para que** eu possa revisar e editar.

### Critérios de Aceitação
- ✅ GET /roles retorna: id, name, user_count, permission_count
- ✅ Apenas admin pode acessar
- ✅ Paginação: 50 por página

---

## 🔐 US-021: Visualizar Detalhes de Role

**Como** administrador,  
**Eu quero** ver todas as permissões atribuídas a um role,  
**Para que** eu possa auditar permissões.

### Critérios de Aceitação
- ✅ GET /roles/{id} retorna:
  - Nome, criação data
  - Todas as role_permissions (resource, action)
  - Contador de users com esse role
  - Columns configuração
- ✅ Apenas admin

---

## 🔐 US-022: Atualizar Role

**Como** administrador,  
**Eu quero** editar nome e columns de um role,  
**Para que** eu possa ajustar configurações.

### Critérios de Aceitação
- ✅ PUT /roles/{id} com: name, columns
- ✅ Name deve ser único
- ✅ Auditoria: mudanças registadas
- ✅ Permissões NÃO afetadas (editar via endpoint separado)

---

## 🔐 US-023: Adicionar Permissão a Role

**Como** administrador,  
**Eu quero** dar uma permissão (resource + action) a um role,  
**Para que** eu possa controlar acesso granular.

### Critérios de Aceitação
- ✅ POST /roles/{id}/permissions com: resource, action, description (opcional)
- ✅ Validação: resource e action valid (enums)
- ✅ Não permitir duplicatas (role_id + resource + action unique)
- ✅ Auditoria: adição registada
- ✅ Permissões afetam users imediatamente (cache invalidado)

### Notas
- Resources: users, clients, locations, service_orders, etc. (PermissionResource enum)
- Actions: view, create, update, delete, export, import, restore, force_delete (PermissionAction enum)

### Exemplo
```
POST /roles/manager-001/permissions
{
  "resource": "clients",
  "action": "view",
  "description": "Manager pode listar todos clientes"
}
```

---

## 🔐 US-024: Remover Permissão de Role

**Como** administrador,  
**Eu quero** revogar uma permissão de um role,  
**Para que** eu possa restringir acesso.

### Critérios de Aceitação
- ✅ DELETE /roles/{id}/permissions/{permissionId}
- ✅ Auditoria: remoção registada
- ✅ Cache de permissões invalidado (users afetados usam cache novo na próxima ação)
- ✅ Apenas admin

---

## 🔐 US-025: Deletar Role

**Como** administrador,  
**Eu quero** remover um role do sistema,  
**Para que** eu possa limpar roles não utilizados.

### Critérios de Aceitação
- ✅ DELETE /roles/{id}
- ✅ Verificar se role está atribuído a users (erro se sim)
- ✅ Se sem users: permitir soft delete
- ✅ Role deletado não pode ser atribuído a novos users
- ✅ Users existentes mantêm role (soft deleted)

### Notas
- Validação: não permitir deletar roles pré-existentes (admin, etc.) sem confirmação extra

---

## 👤 US-026: Verificar Permissões de Utilizador

**Como** middleware/serviço,  
**Eu quero** verificar se um user tem uma permissão específica,  
**Para que** eu possa autorizar ações.

### Critérios de Aceitação
- ✅ PermissionManager::can(User, resource, action): bool
- ✅ Verificação em cache (5 min TTL)
- ✅ User sem role atribuído: acesso negado (default deny)
- ✅ Admin: acesso a tudo
- ✅ Fallback: se cache miss, fazer query DB

### Notas
- Usado em middleware + policies
- Cache key: permission.{user_id}

---

## 👤 US-027: Obter Todas Permissões de Utilizador

**Como** frontend,  
**Eu quero** obter lista de permissões do user autenticado,  
**Para que** eu possa mostrar/esconder features na UI.

### Critérios de Aceitação
- ✅ GET /auth/permissions retorna:
  ```
  {
    "resources": {
      "clients": ["view", "create", "update"],
      "service_orders": ["view", "update"],
      ...
    }
  }
  ```
- ✅ Cache 5 min
- ✅ Invalida ao change role

### Notas
- Usado por frontend para condicionar UI elements

---

## 👤 US-028: Visualizar Permissões de Outro User (Admin)

**Como** administrador,  
**Eu quero** ver todas permissões de outro user,  
**Para que** eu possa auditar acesso.

### Critérios de Aceitação
- ✅ GET /users/{id}/permissions retorna mesma estrutura que US-027
- ✅ Apenas admin
- ✅ Inclui role atual + todas permissions

---
