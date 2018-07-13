<?php

namespace Dependency\Components;

// $startExtract = microtime(true);
use Dependency\Database\SqlConnectionMapper;
use PDO;

/**
 * Init SQL Server Connection
 */
$databaseConfig = require config_path('database.php');
$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];
$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password']);

/**
 * Query to extract database list from SQL Server
 */
$queryConfig = require config_path('query.php');
$query = $queryConfig['sqlserver']['extract_database'];

/**
 * Mapping the query result to SqlConnectionMapper Class
 */
$result = tap($sqlsrv->prepare($query))->execute()->fetchAll(PDO::FETCH_OBJ);
$resultColl = collect($result)->pluck('name')->mapInto(SqlConnectionMapper::class);
// dd($resultColl);

/**
 * Testing code for presentation in final project
 */
// $resultColl = collect("resits")->mapInto(SqlConnectionMapper::class);
// $resultColl = collect("AdventureWorks2016_EXT")->mapInto(SqlConnectionMapper::class);
// dd("This is the result of mapping query result to result object:", $resultColl);


// $endExtract = microtime(true);
// dd("The execution time for extraction:", $endExtract-$startExtract);
return $resultColl;