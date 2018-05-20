<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';
use Illuminate\Support\Str;

$neo4j = createNeo4jConnection();
$extractResult = require_once __DIR__.'/extract.php';

$serverName = tap(createSqlServerConnection()->prepare(config('query.sqlserver.extract_servername')))->execute()->fetchObject()->server;

$serverArray  = [
    'serverName' => $serverName
];
$stack = tap($neo4j->stack())->push("CREATE (x:Server {name: {serverName}})", $serverArray);

// dd($stack);

foreach ($extractResult as $result) {
    $databaseArray = [
        'databaseName' => $result->getDatabase()
    ];
    $serverToDbArray =  array_merge($serverArray, $databaseArray);
    $stack->push("MERGE (x:Server {name: {serverName})-[y:HAS]->(z:Database {name: {databaseName})", $serverToDbArray);
    $resultMapping = $result->getResult();
    if ($resultMapping->loginName !== null) {
        
    }
    dd($result);
    // $test->push(
}