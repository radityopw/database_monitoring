<?php

use GraphAware\Neo4j\Client\ClientBuilder;

if (! function_exists('createSQLServerConnection')) {
    /**
     * Create connection to SQL Server with PDO
     *
     * @param  string  $dbName
     * @return PDO
     */

    function createSQLServerConnection(string $dbName = null) {
        $prefixConfig = 'database.connections.sqlsrv.';
        $port = config($prefixConfig.'port') ?? '1433';
        $serverName = 'tcp:'.config($prefixConfig.'host').','.$port;
        $database = $dbName ?? config($prefixConfig.'database');
        $username = config($prefixConfig.'username');
        $password = config($prefixConfig.'password');
        $prefix = config($prefixConfig.'prefix');

        $conn = new PDO("$prefix:server=$serverName ; Database=$database", $username, $password);
        $conn->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);

        return $conn;
    }
}


if (! function_exists('createNeo4jConnection')) {
    /**
     * Create connection to Neo4j
     *
     * @return GraphAware\Neo4j\Client\Client
     */

    function createNeo4jConnection() {
        $connPrefix = 'database.connections.neo4j.';
        $username = config($connPrefix.'username');
        $password = config($connPrefix.'password');
        $host = config($connPrefix.'host');
        $port = config($connPrefix.'port');

        return ClientBuilder::create()
                ->addConnection('bolt', "bolt://$username:$password@$host:$port")
                ->build();
    }
}