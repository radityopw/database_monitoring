<?php

return [
    'connections' => [
        'sqlsrv' => [
            'host' => env('SQLSRV_DB_HOST', ''),
            'port' => env('SQLSRV_DB_PORT', ''),
            'database' =>   env('SQLSRV_DB_DATABASE', ''),
            'username' => env('SQLSRV_DB_USERNAME', ''),
            'password' => env('SQLSRV_DB_PASSWORD', ''),
            'charset' => env('SQLSRV_DB_CHARSET', 'utf8'),
            'prefix' => 'sqlsrv', 
        ],
        'neo4j' => [
            'sp' => [
                'host' => env('NEO4J_DB_HOST_SP', ''),
                'port' => env('NEO4J_DB_PORT_SP', ''),
                'username' => env('NEO4J_DB_USERNAME_SP', ''),
                'password' => env('NEO4J_DB_PASSWORD_SP', ''),
            ],
            'user' => [
                'host' => env('NEO4J_DB_HOST_USER', ''),
                'port' => env('NEO4J_DB_PORT_USER', ''),
                'username' => env('NEO4J_DB_USERNAME_USER', ''),
                'password' => env('NEO4J_DB_PASSWORD_USER', ''),
            ],
            'username_read' => env('NEO4J_DB_USERNAME_READ', ''),
            'password_read' => env('NEO4J_DB_PASSWORD_READ', ''),
        ]
    ]
];