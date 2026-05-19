# Casos de Uso do Sistema

## Visão Geral

O sistema suporta quatro fluxos de trabalho principais, todos convergindo para a mesma estrutura hierárquica:

```
Ordem de Serviço → Tarefas (por setor) → Mini-Tarefas → Work Logs
```

A conclusão propaga-se de baixo para cima: quando todos os Work Logs estão concluídos, a Mini-Tarefa fecha; quando todas as Mini-Tarefas fecham, a Tarefa fecha; quando todas as Tarefas fecham, a Ordem de Serviço fecha.

---

## Caso 1 — Reporte de Problema pelo Cidadão

**Contexto:** Um cidadão contacta a instituição a reportar um problema na cidade que precisa de ser resolvido.

### Fluxo

1. **Registo** — O atendente regista o problema numa nova Ordem de Serviço com:
   - Descrição do problema
   - Data de início proposta
   - Estado inicial: *Pendente*
   - Todos os restantes dados relativos a uma ordem de serviço

2. **Alerta** — Quando a Ordem de Serviço atinge a data de início, o sistema emite um alerta.

3. **Ativação** — A pessoa responsável pela Ordem de Serviço ativa-a. Nesse momento, são criadas automaticamente **Tarefas** para cada setor envolvido.

4. **Divisão em Mini-Tarefas** — O responsável por cada Tarefa divide-a em Mini-Tarefas. Cada Mini-Tarefa deve ter:
   - Um ou mais trabalhadores e/ou equipas atribuídas
   - Materiais necessários e a quantidade dos mesmos
   - Equipamentos necessários
   - Datas previstas de início e fim

5. **Execução e Work Logs** — Os trabalhadores/equipas responsáveis pelas Mini-Tarefas criam Work Logs durante a execução. Cada Work Log regista:
   - Materiais e equipamentos utilizados
   - Timestamp de início
   - Timestamp de fim (quando terminado)

6. **Conclusão da Tarefa** — Quando todas as Mini-Tarefas de uma Tarefa estão concluídas, a Tarefa transita para o estado *Aguarda Aprovação*. O responsável pela Tarefa revê o trabalho e marca-a como *Concluída*.

7. **Conclusão da Ordem de Serviço** — Quando todas as Tarefas estão concluídas, o responsável pela Ordem de Serviço revê tudo e marca-a como *Concluída*.

---

## Caso 2 — Requisição Interna de Equipamento

**Contexto:** A nossa instituição necessita de equipamento(s) para um evento ou uso interno.

### Fluxo

1. **Ticket Interno** — Um colaborador interno cria um ticket com:
   - Lista de equipamentos necessários
   - Para cada equipamento: data de início, data de fim, e se é necessário operador

2. **Criação da Ordem de Serviço** — O gestor de tickets acede ao pedido e cria uma Ordem de Serviço com:
   - Dados importados automaticamente do ticket
   - Datas previstas de início e fim
   - Pessoa responsável atribuída
   - Estado: *Pendente*
   - Tipo: *Ticket* (com referência ao ID do ticket original)
   - Secções p/equipamento de acordo com o chefe de cada secção

3. **Criação de Tarefas por Equipamento** — O gestor da Ordem de Serviço analisa os equipamentos necessários e cria uma **Tarefa por equipamento**, atribuindo cada uma a uma secção de acordo com o chefe da mesma.

4. **Execução — Entrega ou Levantamento** — Para cada movimento de equipamento (entrega à entidade ou levantamento pela entidade), é criada uma Mini-Tarefa e, dentro dela, um Work Log. Os cenários possíveis são:

   | Cenário | Registo |
   |---|---|
   | Nós entregamos na localização | Timestamp de saída da instituição + timestamp de entrega |
   | Nós vamos levantar | Timestamp de saída da instituição até à entidade |
   | Entidade levanta e entrega no nosso estabelecimento | Registo de receção e devolução com Work Logs |

---

## Caso 3 — Aluguer de Equipamento por Entidade Externa

**Contexto:** Uma entidade externa pretende alugar equipamento(s) da instituição.

### Fluxo

1. **Pedido Online** — A entidade acede ao site e preenche um formulário com:
   - Lista de equipamentos pretendidos
   - Para cada equipamento: data de início, data de fim, e se é necessário operador
   - Localização de utilização
   - Descrição do pedido

2. **Criação da Ordem de Serviço** — O gestor de empréstimos revê o pedido nos emprestimos e cria uma Ordem de Serviço. O formulário de criação inclui:
   - Campos pré-preenchidos com os dados do pedido da entidade
   - Campo para selecionar a pessoa responsável pela Ordem de Serviço
   - Estado: *Pendente*
   - Tipo: *Empréstimo* (com referência ao ID do empréstimo original)
   - Secções p/equipamento de acordo com o chefe de cada secção

3. **Criação de Tarefas por Equipamento** — O gestor da Ordem de Serviço analisa os equipamentos e cria uma **Tarefa por equipamento**, atribuindo cada uma à secção correspondente.

4. **Execução — Entrega ou Levantamento** — Idêntico ao Caso 2: para cada movimento de equipamento é criada uma Mini-Tarefa e um Work Log com os timestamps e pessoas envolvidas.

   | Cenário | Registo |
   |---|---|
   | Nós entregamos na localização | Timestamp de saída da instituição + timestamp de entrega |
   | Entidade levanta no nosso estabelecimento | Registo de levantamento com Work Log |
   | Entidade devolve no nosso estabelecimento | Registo de devolução com Work Log |

---

## Caso 4 — Manutenção de Equipamento

**Contexto:** Os equipamentos têm um ciclo de manutenção periódico que precisa de ser acompanhado e executado.

### Fluxo

1. **Monitorização** — Cada equipamento tem registado:
   - Data da última manutenção
   - Periodicidade de manutenção
   - Entre outros dados

2. **Alerta Automático** — Quando a data de manutenção se aproxima, o sistema emite um aviso.

3. **Criação da Manutenção e Ordem de Serviço** — É criado um registo de manutenção e uma Ordem de Serviço associada com:
   - Tipo: *Manutenção*
   - Referência ao ID da manutenção

4. **Criação de Tarefa** — A Ordem de Serviço gera uma Tarefa atribuída ao departamento responsável pela manutenção.

5. **Mini-Tarefas** — A Tarefa é dividida em Mini-Tarefas com:
   - Data prevista de início e fim da manutenção

6. **Work Logs** — Durante a execução são criados Work Logs que registam:
   - Timestamp exato de início
   - Timestamp exato de fim
   - Materiais utilizados