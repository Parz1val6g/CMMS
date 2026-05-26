# Clean handoff entre níveis da cascata

Na hierarquia Ordem de Serviço → Tarefa → Mini-Tarefa → Work Log, cada nível tem autoridade exclusiva de escrita sobre o seu próprio recurso. O nível superior tem permissão read-only sobre os níveis inferiores (ex.: o Gestor da SO vê o estado das Tarefas, mas não as gere). Sem sobreposição de permissões de escrita entre papéis.

**Alternativa rejeitada:** papéis superiores com permissão de escrita nos níveis inferiores (ex.: Gestor da SO poder criar Work Logs). Isto criaria ambiguidade sobre quem é responsável por cada ação e abriria caminho para bypass do fluxo definido. A separação estrita garante um trilho de auditoria claro: cada mudança de estado tem exatamente um papel responsável.
