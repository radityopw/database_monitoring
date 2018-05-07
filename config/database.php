<?php

return [
    'connections' => [
        'sqlsrv' => [
            'host' => env('SQLSRV_DB_HOST',''),
            'port' => env('SQLSRV_DB_PORT',''),
            'database' =>   env('SQLSRV_DB_DATABASE','test'),
            'username' => env('SQLSRV_DB_USERNAME',''),
            'password' => env('SQLSRV_DB_PASSWORD', ''),
            'charset' => env('SQLSRV_DB_CHARSET','utf8'),
            'prefix' => '', 
        ],
        'neo4j' => [
            'host' => env('NEO4J_DB_HOST',''),
            'port' => env('NEO4J_DB_PORT',''),
            'database' =>   env('NEO4J_DB_DATABASE',''),
            'username' => env('NEO4J_DB_USERNAME',''),
            'password' => env('NEO4J_DB_PASSWORD', ''),
        ]
    ]
];