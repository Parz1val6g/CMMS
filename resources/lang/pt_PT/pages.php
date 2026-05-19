<?php

return [
    // Textos ao nível da página (títulos, breadcrumbs, dashboard, páginas de índice CRUD)
    // Estrutura: 'page_name' => ['key' => 'Valor'],

    'loan_orders' => [
        'title'                         => 'Empréstimos',
        'action_approve'                => 'Aprovar',
        'action_approve_confirm'        => 'Tem a certeza que deseja aprovar este empréstimo?',
        'action_checkout'               => 'Levantar',
        'action_checkout_confirm'       => 'Confirmar levantamento de equipamento?',
        'action_initiate_return'        => 'Iniciar Devolução',
        'action_initiate_return_confirm'=> 'Iniciar o processo de devolução?',
        'action_cancel'                 => 'Cancelar',
        'action_cancel_confirm'         => 'Tem a certeza que deseja cancelar este empréstimo?',
    ],
];
