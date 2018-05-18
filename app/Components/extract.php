<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';
use PDO;
$sqlsrv = require_once __DIR__.'/../Connection/sqlsrv.php';

$query = <<<'MULTI'
SELECT name 
FROM sys.sysdatabases WHERE name NOT IN ('master', 'tempdb','model','msdb')
MULTI;

$result = tap($sqlsrv->prepare($query))->execute();
dd($result->fetchAll(PDO::FETCH_OBJ));