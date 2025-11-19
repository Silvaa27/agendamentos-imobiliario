<?php

return [
    'heading' => 'Calendário de Agendamentos',
    'subheading' => 'Gerencie seus agendamentos',

    'actions' => [
        'create' => 'Criar Agendamento',
        'edit' => 'Editar',
        'view' => 'Visualizar',
        'delete' => 'Eliminar',
    ],

    'fields' => [
        'title' => 'Título',
        'start' => 'Início',
        'end' => 'Fim',
        'all_day' => 'Dia Todo',
    ],

    'messages' => [
        'created' => 'Agendamento criado com sucesso',
        'updated' => 'Agendamento atualizado com sucesso',
        'deleted' => 'Agendamento eliminado com sucesso',
    ],

    'modals' => [
        'create' => [
            'heading' => 'Criar Agendamento',
            'subheading' => 'Preencha os dados do novo agendamento',
        ],
        'edit' => [
            'heading' => 'Editar Agendamento',
            'subheading' => 'Atualize os dados do agendamento',
        ],
        'view' => [
            'heading' => 'Visualizar Agendamento',
            'subheading' => 'Detalhes do agendamento',
        ],
        'delete' => [
            'heading' => 'Eliminar Agendamento',
            'description' => 'Tem a certeza que deseja eliminar este agendamento? Esta ação não pode ser desfeita.',
        ],
    ],

    'buttons' => [
        'confirm' => 'Confirmar',
        'cancel' => 'Cancelar',
        'submit' => 'Submeter',
        'delete' => 'Eliminar',
        'save' => 'Guardar',
    ],

    'views' => [
        'month' => 'Mês',
        'week' => 'Semana',
        'day' => 'Dia',
        'list' => 'Lista',
    ],

    'days' => [
        'monday' => 'Segunda',
        'tuesday' => 'Terça',
        'wednesday' => 'Quarta',
        'thursday' => 'Quinta',
        'friday' => 'Sexta',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ],

    'months' => [
        'january' => 'Janeiro',
        'february' => 'Fevereiro',
        'march' => 'Março',
        'april' => 'Abril',
        'may' => 'Maio',
        'june' => 'Junho',
        'july' => 'Julho',
        'august' => 'Agosto',
        'september' => 'Setembro',
        'october' => 'Outubro',
        'november' => 'Novembro',
        'december' => 'Dezembro',
    ],

    'time' => [
        'today' => 'Hoje',
        'all_day' => 'Dia Todo',
    ],

    'errors' => [
        'overlap' => 'Este horário sobrepõe-se a outro agendamento',
        'invalid_time' => 'Horário inválido',
        'required' => 'Este campo é obrigatório',
    ],
];