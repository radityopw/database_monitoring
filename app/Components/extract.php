<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';
use PDO;
use UserDep\Database\SqlConnectionMapper;

$sqlsrv = createSQLServerConnection();

// $query = "
// SELECT name 
// FROM sys.sysdatabases WHERE name NOT IN ('master', 'tempdb','model','msdb')
// ";

// dd($query);

// $result = tap($sqlsrv->prepare($query))->execute()->fetchAll(PDO::FETCH_OBJ);
// dd($result);
// $resultColl = collect($result)->pluck('name')->mapInto(SqlConnectionMapper::class);
$resultColl = collect("resits")->mapInto(SqlConnectionMapper::class);
// tap($resultColl)->map(function ($item, $key) {
//     dd($item);
//     // return $item." Hafiz";
// });
dd($resultColl);

// dd($result->fetchAll(PDO::FETCH_OBJ));