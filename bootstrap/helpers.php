<?php

use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\HigherOrderTapProxy;
use Illuminate\Support\Collection;

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

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}


if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
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

if (! function_exists('createSQLServerConnection')) {
    /**
     * Create connection to SQL Server withPDO
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