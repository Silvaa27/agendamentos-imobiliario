<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Nome',
    'column.guard_name' => 'Guard',
    'column.roles' => 'Funções',
    'column.permissions' => 'Permissões',
    'column.updated_at' => 'Alterado em',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Nome',
    'field.guard_name' => 'Guard',
    'field.permissions' => 'Permissões',
    'field.select_all.name' => 'Selecionar todos',
    'field.select_all.message' => 'Habilitar todas as permissões para essa função',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Definições',
    'nav.role.label' => 'Cargos',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Cargo',
    'resource.label.roles' => 'Cargos',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Entidades',
    'resources' => 'Recursos',
    'widgets' => 'Widgets',
    'pages' => 'Páginas',
    'custom' => 'Permissões customizadas',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Você não tem permissão para acessar',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Visualizar',
        'view_any' => 'Visualizar Todos',
        'create' => 'Criar',
        'update' => 'Editar',
        'delete' => 'Eliminar',
        'delete_any' => 'Eliminar Todos',
        'force_delete' => 'Eliminar Permanentemente',
        'force_delete_any' => 'Eliminar Permanentemente Todos',
        'restore' => 'Restaurar',
        'restore_any' => 'Restaurar Todos',
        'replicate' => 'Replicar',
        'reorder' => 'Reordenar',

        //Customs
        'view_all_advertise' => 'Ver todos os anúncios',
        'view_all_businesshours' => 'Ver Todos os Horários',
        'create_default_businesshours' => 'Criar Horários Padrão',
        'view_all_unavailabilities' => 'Ver Todas as Indisponibilidades',
        'create_default_unavailabilities' => 'Criar Eventos Padrão',
        'view_shared_advertises_bookings' => 'Ver Marcações de Formulários Partilhados',
    ],
];