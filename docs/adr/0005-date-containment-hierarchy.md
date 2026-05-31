# Hierarquia de contenção de datas entre SO → Tarefa → Mini-Tarefa

Cada nível da cascata operacional tem um Período de Execução (`start_date`/`end_date`). Decidimos enforçar bidirecionalmente a contenção hierárquica: o período de uma Tarefa deve estar dentro do período da SO mãe; o período de uma Mini-Tarefa deve estar dentro do período da Tarefa mãe. A enforção é bidirecional — alterar as datas de um nível superior é bloqueado se os níveis filhos ficarem fora do novo intervalo.

## Considered Options

**Sem validação cruzada** — cada nível gere as suas datas de forma independente. Mais simples, mas cria inconsistências visíveis (uma Tarefa com período além da data da SO) sem razão operacional válida.

**Avisos sem bloqueio** — o sistema avisa mas não impede. Rejeitado porque aviso sem enforce tende a ser ignorado e os dados degradam-se silenciosamente.

## Consequences

- `execution_date` na SO é renomeado para `end_date`; é adicionado `start_date` (ambos obrigatórios).
- A Tarefa ganha `start_date`/`end_date`; sem período definido a Tarefa fica bloqueada em Pendente e não permite criar Mini-Tarefas.
- O período da Tarefa só pode ser editado enquanto o estado for Pendente ou Em Progresso.
- A validação de contenção é enforçada no backend em todos os níveis.
