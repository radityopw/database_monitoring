<?php

require_once '';

return [
    'connections' => [
        'sqlsrv' => [
            'host' => env('SQLSRV_DB_HOST'),
            'port' => env('SQLSRV_DB_PORT'),
            'database' =>   env('SQLSRV_DB_DATABASE'),
            'username' => env('SQLSRV_DB_USERNAME'),
            'password' => env('SQLSRV_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '', 
        ],
        'neo4j' => [

        ]
    ]
];