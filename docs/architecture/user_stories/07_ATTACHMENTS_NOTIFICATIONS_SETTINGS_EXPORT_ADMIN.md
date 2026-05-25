# User Stories: Attachments, Notifications, Settings, Export & Admin

---

## 📎 US-116: Visualizar Attachments de Ordem

**Como** manager,  
**Eu quero** ver todas fotos/documentos de uma ordem,  
**Para que** eu possa revisar documentação.

### Critérios de Aceitação
- ✅ GET /service-orders/{id}/attachments retorna:
  - Lista de files com metadata (nome, tipo, tamanho, uploaded_by, timestamp)
  - Thumbnails para imagens
  - Download link (signed URL, expira em 1h)
- ✅ Paginação: 20 por página

---

## 📎 US-117: Visualizar Attachments de MiniTask

**Como** worker,  
**Eu quero** ver fotos/documentos de uma mini-task,  
**Para que** eu possa revisar referências.

### Critérios de Aceitação
- ✅ GET /mini-tasks/{id}/attachments (mesmas especificações de US-116)

---

## 📎 US-118: Download de Attachment

**Como** qualquer utilizador,  
**Eu quero** fazer download de um ficheiro anexado,  
**Para que** eu possa guardar/usar localmente.

### Critérios de Aceitação
- ✅ GET /attachments/{id}/download
- ✅ Signed URL: expira 1 hora
- ✅ Authorization: usuario com acesso à ordem/task
- ✅ Log download: auditoria de quem fez download, quando

---

## 📎 US-119: Deletar Attachment

**Como** admin ou criador,  
**Eu quero** remover um ficheiro anexado,  
**Para que** eu possa limpar attachments incorretos.

### Critérios de Aceitação
- ✅ DELETE /attachments/{id}
- ✅ Soft delete (preserve auditoria)
- ✅ Ficheiro removido de storage
- ✅ Auditoria: quem deletou, quando
- ✅ Authorization: criador ou admin

---

## 🔔 US-120: Centro de Notificações

**Como** cualquier utilizador,  
**Eu quero** ver minhas notificações,  
**Para que** eu possa acompanhar eventos importantes.

### Critérios de Aceitação
- ✅ GET /notifications (current user)
- ✅ Filtros: type, read_status, date_range
- ✅ Campos: id, tipo, mensagem, created_at, read_at
- ✅ Ordenação: recent first
- ✅ Paginação: 50 por página

### Tipos de Notificação
- mini_task_assigned: "Nova mini-task atribuída"
- work_log_approved: "Work log aprovado"
- work_log_rejected: "Work log rejeitado"
- service_order_created: "Ordem criada"
- service_order_completed: "Ordem concluída"
- low_stock_material: "Material com stock baixo"
- etc.

---

## 🔔 US-121: Marcar Notificação como Lida

**Como** qualquer utilizador,  
**Eu quero** marcar notificações como lidas,  
**Para que** eu possa organizar meu centro de notificações.

### Critérios de Aceitação
- ✅ PUT /notifications/{id}/read
- ✅ Campo read_at: atualizado
- ✅ Marcar todas como lidas: PUT /notifications/read-all

---

## 🔔 US-122: Deletar Notificação

**Como** utilizador,  
**Eu quero** remover notificações,  
**Para que** eu possa limpar arquivo.

### Critérios de Aceitação
- ✅ DELETE /notifications/{id}
- ✅ Deletar tudo: DELETE /notifications/clear-all

---

## ⚙️ US-123: Preferências de Notificação

**Como** utilizador,  
**Eu quero** configurar quais notificações recebo,  
**Para que** eu possa evitar spam.

### Critérios de Aceitação
- ✅ GET /settings/notifications retorna:
  - Toggles por tipo de notificação (email, push, in-app)
  - Frequência (imediato, diário, semanal)
  - Horário "do not disturb"
- ✅ PUT /settings/notifications com: preferências
- ✅ Default: todos os tipos ativados

---

## ⚙️ US-124: Preferências Pessoais do Utilizador

**Como** utilizador,  
**Eu quero** configurar minhas preferências,  
**Para que** meu perfil seja personalizado.

### Critérios de Aceitação
- ✅ GET /settings/user retorna:
  - Idioma (pt_PT, en_US)
  - Timezone
  - Formatos de data/hora
  - Items por página (paginação)
  - Tema (light/dark)
  - Verificação 2FA (optional)
- ✅ PUT /settings/user com: preferências
- ✅ Auditoria: mudanças registadas

---

## ⚙️ US-125: Configurações de Admin

**Como** admin,  
**Eu quero** configurar settings globais do sistema,  
**Para que** eu possa customizar comportamento.

### Critérios de Aceitação
- ✅ GET /settings/admin (admin only) retorna:
  - Max file upload size
  - Retention period para dados deletados (dias)
  - Email from (sender address)
  - Currencies/locales suportados
  - Low stock threshold (%)
  - API rate limits
- ✅ PUT /settings/admin com: settings
- ✅ Cache: 1 hora (invalida quando muda)
- ✅ Auditoria: mudanças registadas

---

## 📊 US-126: Exportar Clientes para CSV

**Como** manager,  
**Eu quero** exportar lista de clientes,  
**Para que** eu possa usar em Excel/outros.

### Critérios de Aceitação
- ✅ GET /export/clients?format=csv retorna CSV download
- ✅ Campos: id, nif, manager, status, created_at, orders_count
- ✅ Filtros aplicados (de US-030)
- ✅ Max rows: 10k (se mais, retornar erro com sugestão de paginação)
- ✅ Encoding: UTF-8 com BOM

---

## 📊 US-127: Exportar Service Orders

**Como** manager,  
**Eu quero** exportar ordens de serviço,  
**Para que** eu possa analisar em Excel.

### Critérios de Aceitação
- ✅ GET /export/service-orders?format=csv&date_from=&date_to=
- ✅ Campos: id, process, client, priority, status, manager, created_at, completed_at
- ✅ Filtros: período, manager, status
- ✅ Max rows: 10k
- ✅ Opção include related: tasks, work_logs (nested CSV)

---

## 📊 US-128: Exportar Work Logs

**Como** manager,  
**Eu quero** exportar work logs,  
**Para que** eu possa gerar relatórios.

### Critérios de Aceitação
- ✅ GET /export/work-logs?format=csv&date_from=&date_to=
- ✅ Campos: id, mini_task, workers, started_at, completed_at, duration_minutes, materials_used, status
- ✅ Filtros: date_range, worker_id, status
- ✅ Max rows: 10k

---

## 📊 US-129: Exportar em PDF

**Como** manager,  
**Eu quero** gerar PDF de uma ordem,  
**Para que** eu possa imprimir/enviar.

### Critérios de Aceitação
- ✅ GET /export/service-orders/{id}?format=pdf
- ✅ Inclui: ordem details, tasks, mini-tasks, work logs summary, materiais, horas
- ✅ Layout profissional: logo, data, assinatura spaces (opcional)
- ✅ Gerado on-demand (não cacheado)

---

## 📊 US-130: Relatório de Dashboard

**Como** admin/manager,  
**Eu quero** ver dashboard com estatísticas,  
**Para que** eu possa acompanhar performance global.

### Critérios de Aceitação
- ✅ GET /dashboard retorna:
  - Totals: ordens (pending, active, completed), horas, materiais custo
  - Top performers: workers (horas), sectors (completed tasks)
  - Recent activity: últimas 10 ações
  - Pending approvals: work logs awaiting supervisor
  - Low stock alerts: materiais críticos
- ✅ Período: today, last 7 days, last 30 days (customizável)

---

## 📊 US-131: Relatório Financeiro

**Como** manager,  
**Eu quero** ver custo total de trabalhos,  
**Para que** eu possa analisar orçamento.

### Critérios de Aceitação
- ✅ GET /reports/financial?date_from=&date_to= retorna:
  - Por ordem: custo materiais + custo horas (rate * hours)
  - Margin se possível (valor contratado vs custo)
  - Agregação: por sector, manager, cliente
  - Comparação: período vs período anterior
- ✅ Export: PDF com gráficos

### Notas
- Rate horária: configurável em app_settings ou por role
- Custo material: sum(quantity_used * unit_price_at_use)

---

## 📊 US-132: Relatório de Recursos Humanos

**Como** manager,  
**Eu quero** ver relatório de horas trabalhadas,  
**Para que** eu possa folha de pagamento.

### Critérios de Aceitação
- ✅ GET /reports/hr?date_from=&date_to= retorna:
  - Por worker: horas totais, minutos, dias trabalhados
  - Por sector: agregação de horas
  - Ausências/faltas (se integrado)
  - Distribuição por tipo de trabalho
- ✅ Export: CSV (para sistema folha de pagamento)
- ✅ Validações: horas lógicas (não > 8h/dia, ex)

---

## 🔍 US-133: Auditoria de Mudanças

**Como** admin,  
**Eu quero** ver log de todas mudanças no sistema,  
**Para que** eu possa auditar e investigar.

### Critérios de Aceitação
- ✅ GET /audit-log?entity_type=&date_from=&date_to= retorna:
  - Todas ações: create, update, delete, approve, reject, etc.
  - Campos: entity_type, entity_id, action, changed_by, timestamp, old_value, new_value
  - Filtros: entity, user, date, action type
  - Paginação: 100 por página
- ✅ Retenção: 365 dias (configurável)
- ✅ Export: CSV

### Notas
- Auto-gerado: middleware captura todas mudanças
- Crítico para compliance

---

## 🔍 US-134: Auditoria de Acesso

**Como** admin,  
**Eu quero** ver log de logins e acessos,  
**Para que** eu possa detectar atividade suspeita.

### Critérios de Aceitação
- ✅ GET /audit-log/access?user_id=&date_from=&date_to=
- ✅ Campos: user, login_time, logout_time, IP, user_agent, location (geo)
- ✅ Alertas: logins fora de horário, múltiplos IPs, falhas (5+ tentativas)
- ✅ Export: CSV

---

## 🛡️ US-135: Resetar Dados de Teste (Dev Only)

**Como** developer,  
**Eu quero** resetar base de dados em ambiente dev,  
**Para que** eu possa testar workflows.

### Critérios de Aceitação
- ✅ POST /dev/reset (apenas em .env APP_ENV=local)
- ✅ Executa: migrations fresh, seeders (user demo, sectors, etc.)
- ✅ Aviso: "⚠️ Todos dados serão perdidos"
- ✅ Confirmação: requer parâmetro force=true
- ✅ Logging: registar quem resetou, quando

---

## 🛡️ US-136: Backup de Base de Dados (Admin)

**Como** admin,  
**Eu quero** fazer backup manual,  
**Para que** eu possa proteger dados.

### Critérios de Aceitação
- ✅ POST /admin/backup (gera SQL dump)
- ✅ Armazenar em: storage/backups/{date}.sql.gz
- ✅ Auditoria: quem fez backup, timestamp
- ✅ Retenção automática: últimos 30 backups
- ✅ Notificação: email ao admin (backup concluído)

---

## 🛡️ US-137: Restore de Backup (Admin)

**Como** admin,  
**Eu quero** restaurar dados de um backup,  
**Para que** eu possa recuperar de incidentes.

### Critérios de Aceitação
- ✅ POST /admin/restore/{backup_id}
- ✅ Aviso: "⚠️ Dados atuais serão perdidos"
- ✅ Confirmação: requer parâmetro confirm=true
- ✅ Processo: atomic (tudo ou nada)
- ✅ Logging: restauração registada
- ✅ Apenas admin

---

## 📧 US-138: Envio de Email Transacional

**Como** sistema,  
**Eu quero** enviar emails automáticamente,  
**Para que** usuarios recebam notificações.

### Critérios de Aceitação
- ✅ Queue: emails enfileirados (async, via jobs)
- ✅ Templates: welcome, forgot-password, work-log-approved, etc.
- ✅ Retry logic: se falhar, tentar 3x com backoff
- ✅ Tracking: delivery status, bounces
- ✅ Unsubscribe link: user pode desabilitar tipos

---

## 📱 US-139: Notificações Push (Mobile)

**Como** worker,  
**Eu quero** receber notificações no smartphone,  
**Para que** eu possa ficar atualizado fora do escritório.

### Critérios de Aceitação
- ✅ App registra FCM token (Firebase Cloud Messaging)
- ✅ Sistema envia push: mini-task assigned, work-log rejected, etc.
- ✅ Cliques: abre app na página relevante
- ✅ Prefer

ências: user pode desabilitar por tipo
- ✅ Offline: fila localmente, envia quando online

---

## 🔐 US-140: 2FA - Autenticação de Dois Fatores (Optional)

**Como** admin,  
**Eu quero** ativar 2FA,  
**Para que** contas sejam mais seguras.

### Critérios de Aceitação
- ✅ GET /auth/2fa/setup retorna QR code (TOTP)
- ✅ POST /auth/2fa/verify com: código TOTP (6 dígitos)
- ✅ Backup codes: 10 códigos gerados (caso perda de acesso)
- ✅ POST /auth/2fa/disable com: password (re-confirm)
- ✅ Apenas enabled se user quiser (opt-in)

---
