# Sem estado CHECKED_OUT no LoanOrder

Um LoanOrder tem apenas quatro estados: `PENDING → APPROVED → COMPLETED / CANCELLED`. Não existe estado `CHECKED_OUT` ao nível do LoanOrder.

O estado operacional de cada equipamento ("está fora" ou "foi devolvido") é rastreado pelas suas Tarefas, Mini-Tarefas e Work Logs — não por um campo de status no LoanOrder. O LoanOrder transita para `COMPLETED` quando todas as Tasks de todos os equipamentos estão concluídas e o Gestor de Empréstimos fecha manualmente após revisão.

**Motivo:** Um LoanOrder pode conter equipamentos com intervalos de datas distintos (ex.: Equipamento X de dia 1–5, Equipamento Y de dia 6–10). Um estado `CHECKED_OUT` a nível do LoanOrder seria incoerente: quando o Equipamento X está fora, o Y ainda não foi levantado; quando o Y é levantado, o X já foi devolvido. Não existe nenhum momento em que "todos os equipamentos estão fora ao mesmo tempo".

**Alternativa rejeitada:** estado `CHECKED_OUT` com semântica "pelo menos um equipamento está fora". Rejeitado porque cria ambiguidade nos relatórios e no UI — o utilizador não consegue saber quais equipamentos estão efectivamente fora apenas pelo status do LoanOrder.
