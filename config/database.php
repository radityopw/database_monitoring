<?php

return [
    'connections' => [
        'sqlsrv' => [
            'host' => '192.168.99.100',
            // 'host' => '10.199.2.66',
            'port' => 1433,
            'database' =>   '',
            'username' => 'sa',
            'password' => 'fairy@test13',
            // 'database' =>   'resits',
            // 'username' => 'monitoring',
            // 'password' => 'monitor',
            'charset' => 'utf8',
            'prefix' => 'sqlsrv', 
        ],
        'neo4j' => [
            'sp' => [
                'host' => 'localhost',
                'port' => 7687,
                'username' => 'neo4j',
                'password' => 'secret',
            ],
            'user' => [
                'host' => '192.168.99.100',
                'port' => 7687,
                'username' => 'neo4j',
                'password' => 'neo4j123',
            ],
            'username_read' => 'neo4j_read',
            'password_read' => 'neo4jread123',
        ]
    ]
];