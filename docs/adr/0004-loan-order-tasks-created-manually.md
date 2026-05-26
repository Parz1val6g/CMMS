# Tasks de LoanOrder são criadas manualmente pelo Gestor de Empréstimos

Numa Ordem de Serviço (UC1), as Tarefas são auto-criadas no momento da activação — uma por setor seleccionado. Num LoanOrder, as Tarefas são criadas **manualmente** pelo Gestor de Empréstimos, uma por equipamento, com atribuição explícita de setor.

A aprovação do LoanOrder está bloqueada até todos os equipamentos terem uma Tarefa criada (setor atribuído).

**Motivo:** A atribuição de setor a um equipamento requer julgamento humano. O mesmo tipo de equipamento pode ser gerido por sectores diferentes dependendo do contexto do empréstimo, da disponibilidade, ou de decisões do Gestor de Setor. Não existe uma regra determinística que permita ao sistema inferir automaticamente qual setor é responsável por qual equipamento — ao contrário da SO onde os sectores são seleccionados explicitamente no formulário de criação.

**Alternativa rejeitada:** auto-criação de Tasks no momento da aprovação, com sector derivado do campo `manager_id` do equipamento. Rejeitado porque o gestor responsável pelo equipamento no catálogo não é necessariamente o setor correcto para um empréstimo específico — o Gestor de Empréstimos precisa de analisar caso a caso.
