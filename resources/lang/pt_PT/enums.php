<?php

return [
    'equipment_status' => [
        'active' => 'Ativo',
        'in_use' => 'Em Uso',
        'maintenance_pending' => 'Manutenção Pendente',
        'under_maintenance' => 'Em Manutenção',
        'broken' => 'Avariado',
        'under_repair' => 'Em Reparação',
        'reserved' => 'Reservado',
        'inactive' => 'Inativo',
        'retired' => 'Desativado',
    ],

    'mini_task_status' => [
        'pending' => 'Pendente',
        'in_progress' => 'Em Progresso',
        'completed' => 'Concluída',
        'blocked' => 'Bloqueada',
        'cancelled' => 'Cancelada',
    ],

    'permission_action' => [
        'view' => 'Visualizar',
        'create' => 'Criar',
        'update' => 'Atualizar',
        'delete' => 'Eliminar',
        'change_role' => 'Alterar Função',
        'export' => 'Exportar',
        'import' => 'Importar',
        'restore' => 'Restaurar',
        'force_delete' => 'Eliminação Forçada',
        'cancel' => 'Cancelar',
        'complete' => 'Concluir',
        'reject' => 'Rejeitar',
        'activate' => 'Ativar',
        'approve' => 'Aprovar',
        'checkout' => 'Levantar',
        'convert' => 'Converter',
        'initiate_return' => 'Iniciar Devolução',
        'assign_workers' => 'Atribuir Trabalhadores',
        'assign_materials' => 'Atribuir Materiais',
        'assign_equipment' => 'Atribuir Equipamento',
    ],

    'permission_resource' => [
        'users' => 'Utilizadores',
        'clients' => 'Clientes',
        'locations' => 'Localizações',
        'service_orders' => 'Ordens de Serviço',
        'service_types' => 'Tipos de Serviço',
        'sessions' => 'Sessões',
        'login_histories' => 'Históricos de Login',
        'tasks' => 'Tarefas',
        'mini_tasks' => 'Mini Tarefas',
        'work_logs' => 'Registos de Trabalho',
        'equipments' => 'Equipamentos',
        'equipment_revisions' => 'Revisões de Equipamento',
        'sectors' => 'Setores',
        'teams' => 'Equipas',
        'workers' => 'Trabalhadores',
        'materials' => 'Materiais',
        'units' => 'Unidades',
        'attachments' => 'Anexos',
        'roles' => 'Funções',
        'role_permissions' => 'Permissões de Funções',
        'profile' => 'Perfil',
        'settings' => 'Configurações',
        'tickets' => 'Tickets',
        'loan_orders' => 'Empréstimos',
        'entities' => 'Entidades',
    ],

    'priority' => [
        'low' => 'Baixa',
        'normal' => 'Normal',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],

    'service_order_status' => [
        'pending' => 'Pendente',
        'in_progress' => 'Em Progresso',
        'completed' => 'Concluída',
        'cancelled' => 'Cancelada',
    ],

    'system_status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'suspended' => 'Suspenso',
        'archived' => 'Arquivado',
    ],

    'task_status' => [
        'pending' => 'Pendente',
        'in_progress' => 'Em Progresso',
        'completed' => 'Concluída',
        'blocked' => 'Bloqueada',
        'cancelled' => 'Cancelada',
    ],

    'user_role' => [
        'admin' => 'Administrador',
        'manager' => 'Gestor',
        'pending' => 'Aprovação Pendente',
    ],

    'workflow_type' => [
        'regular' => 'Padrão',
        'loan' => 'Empréstimo',
    ],

    'work_log_status' => [
        'in_progress' => 'Em Progresso',
        'submitted' => 'Aprovação Pendente',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado',
    ],

    'ticket_status' => [
        'open' => 'Aberto',
        'in_progress' => 'Em Progresso',
        'converted' => 'Convertido',
        'cancelled' => 'Cancelado',
    ],

    'ticket_priority' => [
        'low' => 'Baixa',
        'normal' => 'Normal',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],

    'loan_order_status' => [
        'pending'     => 'Pendente',
        'approved'    => 'Aprovado',
        'checked_out' => 'Levantado',
        'returned'    => 'Devolvido',
        'cancelled'   => 'Cancelado',
    ],

    'entity_type' => [
        'municipal_council' => 'Câmara Municipal',
        'parish_council'    => 'Junta de Freguesia',
        'other'             => 'Outro',
    ],
];
