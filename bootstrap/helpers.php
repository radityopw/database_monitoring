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

    function createNeo4jConnection(string $username = null, string $password = null, string $host = null, int $port = null) {
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

if (! function_exists('dump')) {
    /**
     * Dump the passed variables without ending the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function dump(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
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

if(! function_exists('config_path')) {
    /**
     * Getting the current config path in application
     *
     * @param [type] $path
     * @return void
     */
    function config_path($path = null){
        $configPath = realpath(__DIR__.'/../config');
        if( $path ){
            return $configPath . DIRECTORY_SEPARATOR . $path;
        }
        return $configPath;
        // if($path){
            
        // }
    }
}