<?php

return [

    'label' => 'Perfil',

    'form' => [

        'email' => [
            'label' => 'E-mail',
        ],

        'name' => [
            'label' => 'Nome',
        ],

        'password' => [
            'label' => 'Nova palavra-passe',
            'validation_attribute' => 'palavra-passe',
        ],

        'password_confirmation' => [
            'label' => 'Confirmar nova palavra-passe',
            'validation_attribute' => 'confirmação de palavra-passe',
        ],

        'current_password' => [
            'label' => 'Palavra-passe atual',
            'below_content' => 'Por segurança, confirme a sua palavra-passe para continuar.',
            'validation_attribute' => 'palavra-passe atual',
        ],

        'actions' => [

            'save' => [
                'label' => 'Guardar alterações',
            ],

        ],

    ],

    'multi_factor_authentication' => [
        'label' => 'Autenticação de dois fatores (2FA)',
    ],

    'notifications' => [

        'email_change_verification_sent' => [
            'title' => 'Pedido de alteração de e-mail enviado',
            'body' => 'Foi enviado um pedido para alterar o seu endereço de e-mail para :email. Verifique o seu e-mail para confirmar a alteração.',
        ],

        'saved' => [
            'title' => 'Gravado',
        ],

    ],

    'actions' => [

        'cancel' => [
            'label' => 'Cancelar',
        ],

    ],

];