# Sistema de Ordens de Serviço

Sistema de gestão operacional municipal para registo, execução e conclusão de ordens de serviço — desde o reporte de um cidadão até ao trabalho no terreno. Inclui também a gestão de empréstimos de equipamento a entidades externas e para uso interno.

## Language

### Estrutura hierárquica

**Ordem de Serviço (SO)**:
O pedido de trabalho de topo. Contém a descrição do problema, localização, cliente, datas, prioridade, e os setores envolvidos. É o contentor que agrega todas as Tarefas.
_Avoid_: OS, order, work order, serviço

**Tarefa**:
Uma unidade de trabalho atribuída a um setor específico dentro de uma Ordem de Serviço. Cada setor selecionado na SO gera automaticamente uma Tarefa. É dividida em Mini-Tarefas pelo seu Gestor.
_Avoid_: task, job, atividade

**Mini-Tarefa**:
A menor unidade de trabalho executável. Atribuída a Trabalhadores e/ou Equipas, com materiais e equipamentos planeados. O Trabalhador regista a execução através de Work Logs.
_Avoid_: subtask, mini-task, micro-tarefa

**Work Log**:
Registo individual de trabalho com timestamp de início e fim. Criado pelo Trabalhador durante a execução de uma Mini-Tarefa. Contém materiais e equipamentos efetivamente utilizados.
_Avoid_: registo de trabalho, log, apontamento

### Papéis (Roles)

**Admin**:
Administrador do sistema. Gere utilizadores, roles, permissões e configurações. Não participa no fluxo operacional.
_Avoid_: administrador, super-admin

**Atendente**:
Recebe chamadas de cidadãos e cria Ordens de Serviço. Pode ver e adicionar notas às SOs que criou. Não gere setores, tarefas, nem ativa SOs.
_Avoid_: operador, telefonista, rececionista

**Gestor** (Gestor de SO):
Dono da Ordem de Serviço. Seleciona setores, ativa a SO, revê o trabalho concluído e marca a SO como concluída. Pode rejeitar a SO para adicionar setores ou reabrir Tarefas.
_Avoid_: manager, responsável, encarregado

**Gestor de Tarefa**:
Dono de uma Tarefa. Divide a Tarefa em Mini-Tarefas, atribui Trabalhadores, Equipas, Materiais e Equipamentos. Revê e aprova ou rejeita a Tarefa quando todas as Mini-Tarefas estão concluídas.
_Avoid_: task manager, encarregado de tarefa

**Trabalhador**:
Executa Mini-Tarefas no terreno. Cria Work Logs com timestamps de início e fim, regista materiais e equipamentos usados. Marca a Mini-Tarefa como concluída quando termina.
_Avoid_: worker, operário, funcionário

**Cliente**:
O cidadão que reportou o problema. Atualmente sem acesso ao sistema.
_Avoid_: customer, munícipe, requerente

**Gestor de Setor**:
Gere as equipas e trabalhadores de um setor organizacional. Por omissão, é também o Gestor de Tarefa das tarefas do seu setor. Um utilizador pode ter ambos os papéis.
_Avoid_: chefe de setor, sector manager

**Gestor de Equipa**:
Gere a composição das equipas (quem pertence a cada equipa). Não participa no despacho operacional de tarefas.
_Avoid_: team manager, chefe de equipa

**Gestor de Empréstimos**:
Responsável pelo ciclo de vida dos Empréstimos. Revê pedidos criados por Entidades, modifica se necessário, atribui sectores a cada equipamento (criando as Tarefas correspondentes), aprova, e fecha o Empréstimo após revisão final. Não cria Empréstimos — esses são sempre criados pelas Entidades. Todos os Gestores de Empréstimos vêem a lista de pedidos pendentes sem gestor atribuído; qualquer um pode "pegar" num pedido, atribuindo-se como gestor responsável. Papel distinto do Gestor de SO — opera sobre Empréstimos, não sobre Ordens de Serviço.
_Avoid_: loan manager, gestor de aluguer, responsável pelos empréstimos

### Estados

**Pendente**:
Estado inicial. O recurso foi criado mas ainda não está em execução.
_Avoid_: pending, aguardar, por fazer

**Ativa**:
Estado da SO após ativação pelo Gestor. As Tarefas foram criadas e o trabalho pode começar.
_Avoid_: active, ativada

**Em Progresso**:
O trabalho está a decorrer. Na SO: pelo menos uma Tarefa iniciada. Na Tarefa: pelo menos um Work Log aberto. Na Mini-Tarefa: pelo menos um Work Log aberto.
_Avoid_: in progress, em curso, a decorrer

**Aberto**:
Estado de um Work Log que foi iniciado mas ainda não fechado. Tem timestamp de início mas não de fim.
_Avoid_: open, em aberto

**Fechado**:
Estado de um Work Log concluído. Tem ambos os timestamps (início e fim).
_Avoid_: closed, fechado

**Aguarda Aprovação**:
A Tarefa tem todas as Mini-Tarefas concluídas e aguarda que o Gestor de Tarefa reveja e aprove.
_Avoid_: awaiting approval, pendente de aprovação

**Aguarda Revisão**:
A SO tem todas as Tarefas concluídas ou canceladas e aguarda que o Gestor reveja e conclua.
_Avoid_: awaiting review, pendente de revisão

**Concluída**:
Estado terminal. O recurso foi finalizado com sucesso.
_Avoid_: completed, terminada, fechada

**Rejeitada**:
A Tarefa ou SO foi rejeitada na revisão/aprovação e volta a Em Progresso para correção.
_Avoid_: rejected, recusada

**Cancelada**:
A Tarefa foi cancelada e conta como resolvida para efeitos de conclusão da SO. A SO atualmente não pode ser cancelada.
_Avoid_: cancelled

### Empréstimo de Equipamento

**Empréstimo (LoanOrder)**:
Pedido de empréstimo de um ou mais equipamentos a uma entidade externa ou para uso interno. Contentor administrativo com lista de equipamentos (cada um com datas de início/fim e flag de operador). Segue a mesma cascata hierárquica da SO: LoanOrder → Tarefas (por equipamento) → Mini-Tarefas (por movimento) → Work Logs.
_Avoid_: loan order, aluguer, pedido de empréstimo

**Estados do Empréstimo**:
`Pendente` — criado, aguarda atribuição de sectores a todos os equipamentos e aprovação.
`Aprovado` — todos os equipamentos têm sector atribuído e o gestor aprovou; trabalho operacional pode começar.
`Aguarda Revisão` — todas as Tasks estão concluídas; gestor revê e fecha manualmente.
`Concluído` — terminal, fechado pelo gestor após revisão.
`Cancelado` — terminal, cancelado.
_Sem estado `CHECKED_OUT` ao nível do LoanOrder — o estado operacional de cada equipamento é rastreado pelas suas Tasks/Mini-Tarefas._

**Tarefa de Equipamento**:
Uma Tarefa dentro de um Empréstimo, representando a responsabilidade de um Setor sobre um equipamento específico. Criada manualmente pelo Gestor do Empréstimo após criação do LoanOrder, uma por equipamento, com atribuição de Setor. A aprovação do LoanOrder está bloqueada até todos os equipamentos terem Tarefa criada.
_Avoid_: equipment task, tarefa de emprestimo

**Mini-Tarefa de Movimento**:
Mini-Tarefa dentro de uma Tarefa de Equipamento que representa um movimento físico do equipamento (entrega ou devolução). Criada pelo Gestor de Setor antes do movimento acontecer, com trabalhadores atribuídos. Tem um `movement_type` que distingue o cenário de movimento.
_Avoid_: movement task, mini-task de entrega

**Tipo de Movimento (movement_type)**:
Campo estruturado numa Mini-Tarefa de Movimento que indica o cenário de transferência física do equipamento. Valores canónicos:
- `institution_delivers` — a instituição entrega o equipamento na localização da entidade (checkout)
- `entity_collects` — a entidade levanta o equipamento nas instalações da instituição (checkout)
- `entity_returns` — a entidade devolve o equipamento nas instalações da instituição (return)
- `institution_collects` — a instituição vai levantar o equipamento na localização da entidade (return)
_Avoid_: delivery type, tipo de entrega
_Futura restrição_: quando `needs_operator = true`, a Mini-Tarefa de Movimento correspondente deve ter pelo menos um Trabalhador ou Equipa atribuída antes de poder ser marcada como concluída.

### Conceitos operacionais

**Ativação**:
Ação atómica executada pelo Gestor que muda o estado da SO de Pendente para Ativa e cria automaticamente uma Tarefa por cada setor selecionado.
_Avoid_: activate, iniciar

**Cascata de conclusão**:
Work Logs fechados → Trabalhador marca Mini-Tarefa concluída → todas as Mini-Tarefas concluídas → Tarefa transita para Aguarda Aprovação → Gestor de Tarefa aprova → Tarefa concluída → todas as Tarefas concluídas/canceladas → SO transita para Aguarda Revisão → Gestor conclui SO.
_Avoid_: cascade, propagação

**Setor**:
Unidade organizacional responsável por um tipo de trabalho (ex.: vias, saneamento, iluminação, jardins). Cada setor tem equipas e trabalhadores.
_Avoid_: sector, departamento, secção

**Equipa**:
Grupo de Trabalhadores que executam trabalho em conjunto. Gerida pelo Gestor de Equipa.
_Avoid_: team, brigada, grupo

**Handoff limpo**:
Princípio arquitetural: o nível superior da cascata tem permissão read-only sobre os níveis inferiores. Cada nível tem autoridade exclusiva de escrita sobre o seu próprio recurso. Sem sobreposição de permissões de escrita entre papéis.
_Avoid_: clean handoff

**Dashboard por Role**:
Cada papel vê um dashboard diferente, com KPIs e widgets relevantes ao seu âmbito. O dashboard respeita o mesmo scope de dados que os restantes page controllers — um Trabalhador só vê as suas Mini-Tarefas, um Gestor de Setor só vê o seu setor. O filtro de período (Hoje/Semana/Mês) só existe nos dashboards de Gestor e Atendente. O mapa de intervenções só existe nos dashboards de Gestor e Gestor de Setor. O widget "Requer Atenção" existe para Gestor, Gestor de Tarefa, Trabalhador e Gestor de Setor, com critérios de urgência específicos a cada role.
_Avoid_: dashboard genérico, dashboard único

## Example dialogue

**Dev:** Quando é que uma Tarefa passa para Aguarda Aprovação?

**Domain expert:** Quando o Trabalhador marca a última Mini-Tarefa como Concluída. O sistema faz essa transição automaticamente — o Gestor de Tarefa não precisa de fazer nada. Ele só é notificado de que tem Tarefas para rever.

**Dev:** E se o Gestor de Tarefa não concordar com o trabalho?

**Domain expert:** Rejeita a Tarefa. Escolhe quais Mini-Tarefas precisam de ser refeitas — elas voltam a Em Progresso. Os Work Logs originais são mantidos para auditoria. A Tarefa volta a Em Progresso e o ciclo recomeça.

**Dev:** O Gestor da SO consegue ver os Work Logs?

**Domain expert:** Não. O Gestor da SO vê apenas as Tarefas e os seus estados. Confia que se as Tarefas estão concluídas, o trabalho foi bem feito. Cada nível tem a sua responsabilidade e o seu âmbito de visibilidade.
