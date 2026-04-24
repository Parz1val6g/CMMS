# User Stories: Users & Authentication

---

## 🔐 US-001: Registar Utilizador Novo

**Como** utilizador não autenticado,  
**Eu quero** criar uma conta com email e password,  
**Para que** eu possa aceder ao sistema.

### Critérios de Aceitação
- ✅ Campo email: validação, único, obrigatório
- ✅ Campo password: mín. 8 caracteres, confirmação
- ✅ Campos first_name, last_name, phone: obrigatórios
- ✅ Phone: formato PT (11 dígitos), único
- ✅ Após registo: user criado com status=active, role=pending
- ✅ Email de verificação enviado

### Casos de Teste
| Entrada | Esperado | Resultado |
|---------|----------|-----------|
| Email válido, password 8+ chars | ✅ Conta criada | |
| Email duplicado | ❌ Erro "email já existe" | |
| Password < 8 chars | ❌ Validação falha | |
| Phone inválido | ❌ Erro formato | |

### Notas
- Integração: enviar email de verificação
- Password com hash (bcrypt)

---

## 🔐 US-002: Fazer Login

**Como** utilizador registado,  
**Eu quero** fazer login com email e password,  
**Para que** eu possa aceder à minha conta.

### Critérios de Aceitação
- ✅ Email + password corretos → token gerado
- ✅ Token de acesso (Bearer) retornado
- ✅ Session criada na tabela `sessions`
- ✅ LoginHistory registado (IP, user_agent, timestamp)
- ✅ Constant-time password comparison (segurança)
- ✅ Limite: máx. 5 tentativas falhadas → bloqueio temporário

### Casos de Teste
| Entrada | Esperado | Resultado |
|---------|----------|-----------|
| Email + password corretos | ✅ Token gerado | |
| Email correto, password errada | ❌ Erro "credenciais inválidas" | |
| Email não existe | ❌ Erro "credenciais inválidas" | |
| User status = inactive | ❌ Erro "conta desativada" | |
| 5 tentativas falhadas | ❌ Bloqueio 15 min | |

### Notas
- LoginHistory com: IP, user_agent, success/failure
- Session com: user_id, token, expires_at

---

## 🔐 US-003: Fazer Logout

**Como** utilizador autenticado,  
**Eu quero** fazer logout,  
**Para que** minha session seja terminada.

### Critérios de Aceitação
- ✅ Session atual destruída
- ✅ Token revogado (invalidado)
- ✅ User redireccionado para login
- ✅ Logout global opcional: destruir TODAS as sessions do user

### Casos de Teste
| Ação | Esperado | Resultado |
|------|----------|-----------|
| Logout normal | ✅ Session destruída | |
| Logout all | ✅ Todas sessions destruídas | |
| Token posterior recusado | ❌ Erro autenticação | |

---

## 🔐 US-004: Renovar Token (Refresh Token)

**Como** utilizador autenticado com token próximo da expiração,  
**Eu quero** renovar meu token,  
**Para que** continuar acedendo sem fazer re-login.

### Critérios de Aceitação
- ✅ POST /auth/refresh-token com token válido
- ✅ Novo token gerado
- ✅ Token antigo permanece válido por 5 min (para transição)
- ✅ LoginHistory atualizado

### Notas
- Duração token: 1 hora
- Refresh window: 5 min antes expiração

---

## 👤 US-005: Visualizar Perfil do Utilizador

**Como** utilizador autenticado,  
**Eu quero** ver meus dados pessoais e configurações,  
**Para que** eu possa gerenciar minha conta.

### Critérios de Aceitação
- ✅ GET /auth/me retorna: id, name, email, phone, role, permissions
- ✅ Roles e permissions incluso
- ✅ Status verificação email
- ✅ Data de criação da conta
- ✅ Last login timestamp

### Notas
- Cache: 5 min (invalidar ao atualizar perfil)

---

## 👤 US-006: Editar Perfil Pessoal

**Como** utilizador autenticado,  
**Eu quero** atualizar meus dados pessoais,  
**Para que** informações fiquem atualizadas.

### Critérios de Aceitação
- ✅ Campos editáveis: first_name, last_name, phone
- ✅ Email NÃO editável (apenas admin)
- ✅ Password alterada via endpoint específico
- ✅ Validações: phone único (exceto self), format
- ✅ Auditoria: log de quem atualizou e quando

### Casos de Teste
| Campo | Ação | Esperado | Resultado |
|-------|------|----------|-----------|
| first_name | "João Silva" | ✅ Atualizado | |
| phone | "912345678" | ✅ Atualizado se único | |
| email | "novo@email.com" | ❌ Não permitido | |

---

## 🔑 US-007: Recuperar Password Esquecida

**Como** utilizador com password esquecida,  
**Eu quero** receber um link para resetar password,  
**Para que** eu possa regain acesso.

### Critérios de Aceitação
- ✅ POST /forgot-password com email
- ✅ Email encontrado → link seguro enviado (signed URL, 1 hora expiração)
- ✅ Email não existe → mensagem genérica (não revelar existência)
- ✅ Link contém token opaco (não expõe email)
- ✅ Token pode ser usado apenas 1x

### Notas
- Usar Laravel password reset tokens
- Link: https://app.com/reset-password?token=XXX
- Armazenar em `password_reset_tokens`

---

## 🔑 US-008: Resetar Password com Link

**Como** utilizador com link de reset válido,  
**Eu quero** criar nova password,  
**Para que** eu possa fazer login novamente.

### Critérios de Aceitação
- ✅ POST /reset-password com token, email, nova password
- ✅ Validações: token válido, não expirado, email match
- ✅ Password antiga não pode ser reutilizada
- ✅ Após reset: todos tokens de reset invalidados
- ✅ LoginHistory registado

### Casos de Teste
| Cenário | Esperado | Resultado |
|---------|----------|-----------|
| Token válido + password nova | ✅ Reset bem-sucedido | |
| Token expirado (>1h) | ❌ Erro "link expirado" | |
| Token já usado | ❌ Erro "link inválido" | |
| Email não match | ❌ Erro "token inválido" | |

---

## 🔑 US-009: Alterar Password (Utilizador Autenticado)

**Como** utilizador autenticado,  
**Eu quero** alterar minha password,  
**Para que** eu possa atualizar segurança.

### Critérios de Aceitação
- ✅ Requer password atual (verificação)
- ✅ Nova password: ≠ da anterior, mín. 8 chars, confirmação
- ✅ Após mudança: TODOS os tokens revogados (force re-login)
- ✅ Email de confirmação enviado
- ✅ Auditoria: log de mudança

### Notas
- Força re-login em todos devices
- SessionHandler revoga todas sessions do user

---

## 👥 US-010: Listar Utilizadores (Admin Only)

**Como** administrador,  
**Eu quero** ver lista de todos utilizadores,  
**Para que** eu possa gerenciar contas e permissões.

### Critérios de Aceitação
- ✅ GET /users com paginação (20 por página)
- ✅ Filtros: role, status, data criação, search (name/email)
- ✅ Campos: id, name, email, phone, role, status, created_at, last_login
- ✅ Soft-deleted users: opção de mostrar/esconder
- ✅ Apenas admin pode acessar

### Notas
- Permissão: users.view (PermissionAction::VIEW, PermissionResource::USERS)

---

## 👥 US-011: Visualizar Detalhes de Utilizador

**Como** administrador ou utilizador (self),  
**Eu quero** ver detalhes completos de um utilizador,  
**Para que** eu possa gerenciar ou revisar informações.

### Critérios de Aceitação
- ✅ GET /users/{id} retorna: todos dados, roles, permissions, login history
- ✅ User pode ver apenas seu próprio perfil OU admin qualquer um
- ✅ Incluir: último login, tentativas falhadas, sessions ativas

### Notas
- Authorization: User::id == auth()->id() OR admin

---

## 👥 US-012: Criar Utilizador (Admin Only)

**Como** administrador,  
**Eu quero** criar novo utilizador manualmente,  
**Para que** eu possa onboard novos funcionários.

### Critérios de Aceitação
- ✅ POST /users com: first_name, last_name, email, phone, role (opcional, default=pending)
- ✅ Password gerada temporária (ou enviada por email)
- ✅ User criado com status=active
- ✅ Email de notificação enviado (credentials)
- ✅ Auditoria: quem criou, quando

### Notas
- Role atribuível na criação
- Temporary password com link para reset na 1ª login

---

## 👥 US-013: Atualizar Utilizador (Admin Only)

**Como** administrador,  
**Eu quero** atualizar dados de qualquer utilizador,  
**Para que** eu possa corrigir informações.

### Critérios de Aceitação
- ✅ PUT /users/{id} com: first_name, last_name, phone, status
- ✅ Email NÃO editável
- ✅ Role: atualizar via endpoint separado (com auditoria extra)
- ✅ Auditoria: log de mudanças

---

## 👥 US-014: Atualizar Role de Utilizador (Admin Only)

**Como** administrador,  
**Eu quero** alterar o papel (role) de um utilizador,  
**Para que** eu possa ajustar permissões de acesso.

### Critérios de Aceitação
- ✅ POST /users/{id}/change-role com: nova role
- ✅ Validação: role existe
- ✅ Auditoria: quem mudou, role anterior, role nova, timestamp
- ✅ Email de notificação ao utilizador
- ✅ Se role mudou: sessions ativas NÃO revogadas (permissões ativas na próxima ação)

### Notas
- Roles: admin, manager, supervisor, worker, pending

---

## 👥 US-015: Desativar/Bloquear Utilizador (Admin Only)

**Como** administrador,  
**Eu quero** desativar uma conta de utilizador,  
**Para que** eu possa revogare acesso temporariamente.

### Critérios de Aceitação
- ✅ PUT /users/{id} com: status=inactive
- ✅ User não consegue fazer login
- ✅ Sessions ativas REVOGADAS
- ✅ Auditoria: quem desativou, motivo (opcional)
- ✅ Dados NÃO eliminados (soft delete)

### Casos de Teste
| Ação | Esperado | Resultado |
|------|----------|-----------|
| Desativar user | ✅ Status = inactive | |
| Tentar login desativado | ❌ Erro "conta desativada" | |
| Sessions destruídas | ✅ Token inválido | |

---

## 👥 US-016: Restaurar Utilizador Deletado (Admin Only)

**Como** administrador,  
**Eu quero** restaurar um utilizador previamente deletado,  
**Para que** eu possa recuperar acesso.

### Critérios de Aceitação
- ✅ POST /users/{id}/restore (soft delete restore)
- ✅ Status retorna a active
- ✅ User consegue fazer login novamente
- ✅ Auditoria: restauração registada

---

## 👥 US-017: Eliminar Utilizador (Admin Only)

**Como** administrador,  
**Eu quero** eliminar permanentemente uma conta,  
**Para que** eu possa remover dados de utilizador.

### Critérios de Aceitação
- ✅ DELETE /users/{id} com confirmação (soft delete)
- ✅ User não consegue fazer login
- ✅ Dados preservados (soft delete, recovery possível)
- ✅ Para hard delete: confirmação extra + delay 30 dias
- ✅ Auditoria: quem deletou, quando, ip

### Notas
- Usar soft deletes por default
- Hard delete após período de retenção

---

## 📋 US-018: Visualizar Histórico de Login

**Como** utilizador ou administrador,  
**Eu quero** ver histórico de logins,  
**Para que** eu possa detectar atividade suspeita.

### Critérios de Aceitação
- ✅ GET /login-history (current user) ou /login-history?user_id=X (admin)
- ✅ Campos: timestamp, IP, user_agent, success/failure, device info
- ✅ Filtros: data range, status (success/failure)
- ✅ Paginação: 50 por página
- ✅ Máx. 90 dias histórico (cleanup automático)

### Notas
- Registado automaticamente no login
- Útil para segurança e auditoria

---
