<?php

use GraphAware\Neo4j\Client\ClientBuilder;
use Illuminate\Support\HigherOrderTapProxy;
use Illuminate\Support\Collection;
use Illuminate\Support\Debug\Dumper;

if (! function_exists('createSQLServerConnection')) {
    /**
     * Create connection to SQL Server with PDO
     *
     * @param  string  $dbName
     * @return PDO
     */

    function createSQLServerConnection(string $server = null, int $port = null, string $username = null, string $password = null, string $database = null, string $prefix = 'sqlsrv') {
        $serverName = 'tcp:'.$server.','.$port;
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

    function createNeo4jConnection($appendix = null) {
        if (!$appendix) {
            return null;
        }
        $appendixConn = ctype_lower($appendix) ? $appendix : strtolower($appendix);
        $connPrefix = "database.connections.neo4j.{$appendixConn}.";
        $username = config($connPrefix.'username');
        $password = config($connPrefix.'password');
        $host = config($connPrefix.'host');
        $port = config($connPrefix.'port');

        return ClientBuilder::create()
                ->addConnection('bolt', "bolt://$username:$password@$host:$port")
                ->build();
    }
}


if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }

        die(1);
    }
}

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}