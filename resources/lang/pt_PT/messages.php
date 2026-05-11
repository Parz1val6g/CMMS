<?php

return [
    'http' => [
        401 => 'Autenticação necessária',
        403 => 'Acesso negado',
        404 => 'Recurso não encontrado',
        422 => 'Validação falhou',
        500 => 'Ocorreu um erro',
    ],
    'services' => [
        'service_order' => [
            'equipment_not_available' => 'Equipamento não está disponível para empréstimo. Deve estar ativo, disponível para empréstimo e não em atraso de revisão.',
            'cannot_update_completed' => 'Não é possível atualizar uma ordem de serviço concluída ou cancelada.',
            'cannot_cancel_completed' => 'Não é possível cancelar uma ordem de serviço já concluída.',
            'return_only_for_loan' => 'A devolução só é válida para fluxos de empréstimo.',
            'return_task_exists' => 'Já existe uma tarefa de devolução para esta ordem de serviço.',
            'checkout_must_complete' => 'A tarefa de empréstimo de equipamento deve estar concluída antes de iniciar a devolução.',
            'already_completed' => 'Esta ordem de serviço já está concluída.',
            'incomplete_tasks' => 'Aprovação do Gestor Negada: Nem todas as tarefas estão concluídas.',
        ],
        'equipment' => [
            'invalid_status' => "Valor de estado inválido ':value'.",
            'cannot_transition' => "Não é possível transitar equipamento de ':from' para ':to'.",
            'invalid_transition' => 'Transição de estado de equipamento inválida.',
        ],
        'notifications' => [
            'new_order_title' => 'Nova Ordem de Serviço Criada',
            'new_order_message' => 'A Ordem de Serviço :process foi criada e atribuída a si.',
        ],
    ],
    'controllers' => [
        'service_orders' => [
            'col_process' => 'Processo',
            'col_description' => 'Descrição',
            'col_client' => 'Cliente',
            'col_priority' => 'Prioridade',
            'col_status' => 'Estado',
            'col_created' => 'Criado',
            'filter_search' => 'Pesquisar',
            'filter_status' => 'Estado',
            'filter_priority' => 'Prioridade',
            'search_placeholder' => 'Pesquisar processo...',
        ],
    ],
    'task_names' => [
        'equipment_loan' => 'Empréstimo de Equipamento',
        'equipment_return' => 'Devolução de Equipamento',
    ],
];
