<?php

return [
    'client' => [
        'sede_required' => 'É obrigatório definir uma sede (localização principal) para o cliente.',
    ],
    'task' => [
        'period_locked' => 'O período da tarefa não pode ser alterado no estado atual.',
        'no_period_for_mini_task' => 'Não é possível criar mini-tarefa: a tarefa principal deve ter um período de execução (data de início/fim) definido primeiro.',
        'start_date_before_service_order' => 'A data de início deve estar dentro do período da Ordem de Serviço (:start – :end).',
        'end_date_after_service_order' => 'A data de fim deve estar dentro do período da Ordem de Serviço (:start – :end).',
    ],
    'service_order' => [
        'dates_conflict_tasks' => 'Não é possível alterar as datas: :count tarefa(s) ficariam fora do novo período.',
    ],
];
