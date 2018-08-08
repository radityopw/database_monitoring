<?php

return [
    'connections' => [
        'sqlsrv' => [
            'host' => '',
            'port' => 0,
            'database' =>   '',
            'username' => '',
            'password' => '',
            'charset' => 'utf-8',//Default
            'prefix' => 'sqlsrv', //Default
        ],
        'neo4j' => [
            'sp' => [
                'host' => '',
                'port' => 0,
                'username' => '',
                'password' => '',
            ],
            'user' => [
                'host' => '',
                'port' => 0,
                'username' => '',
                'password' => '',
            ],
            'username_read' => '',
            'password_read' => '',
        ]
    ]
];