<?php
	
require_once __DIR__.'/../hihi.php';


$databaseConfig = require config_path('database.php');

$neo4jConfig = $databaseConfig['connections']['neo4j']['sp'];
$SqlSrvConfig = $databaseConfig['connections']['sqlsrv'];

$neo4j = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
$sqlsrv = createSqlServerConnection($SqlSrvConfig['host'], $SqlsrvConfig['port'], $SqlsrvConfig['username'], $SqlsrvConfig['password']);
	// include('config.php');

$sql1 = $sqlsrv->prepare('
		SELECT @@SERVERNAME as srv,DB_NAME(DB_ID()) as db,SCHEMA_NAME(schema_id) as sch, sys.tables.name as tbl FROM sys.tables');
$sql1->execute();
$tbl=$sql1->fetchAll();
dump($tbl);

$sql_sp = $sqlsrv->prepare("
			SELECT @@SERVERNAME as srv, SPECIFIC_CATALOG as db, SPECIFIC_SCHEMA as sch, SPECIFIC_NAME as sp_name
				FROM information_schema.routines
				WHERE routine_type = 'PROCEDURE'
				ORDER BY sp_name ASC
			");
$sql_sp->execute();
$sp=$sql_sp->fetchAll();
dump($sp);


?>