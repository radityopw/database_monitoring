<?php

require_once __DIR__.'\hihi.php'; 

$databaseConfig = require config_path('database.php');

$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];

$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password'],$sqlSrvConfig['database']);

$sql_tbl = $sqlsrv->prepare('
		SELECT @@SERVERNAME as srv,DB_NAME(DB_ID()) as db,SCHEMA_NAME(schema_id) as sch, sys.tables.name as tbl FROM sys.tables');
$sql_tbl->execute();
$tbl=$sql_tbl->fetchAll();

$sql_sp = $sqlsrv->prepare("
			SELECT @@SERVERNAME as srv, SPECIFIC_CATALOG as db, SPECIFIC_SCHEMA as sch, SPECIFIC_NAME as sp_name
				FROM information_schema.routines
				WHERE routine_type = 'PROCEDURE'
				ORDER BY sp_name ASC
			");
$sql_sp->execute();
$sp=$sql_sp->fetchAll();

?>