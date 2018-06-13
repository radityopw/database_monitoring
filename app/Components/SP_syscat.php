<?php
ini_set('max_execution_time', 1000);
// $start = microtime(true);

		require_once __DIR__.'/../hihi.php';
		
		//initiation
        $databaseConfig = require config_path('database.php');
		$neo4jConfig = $databaseConfig['connections']['neo4j']['sp'];
		$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];

		//connection to  neo4j
		$neo = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
		
		//connection to sql server
		$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password'], $sqlSrvConfig['database']);
		
		$neo->run('MATCH (n) DETACH DELETE n');
			//get System Catalog Server & Database
			$sql_srv=$sqlsrv->prepare('SELECT name as srv from sys.servers');
			$sql_srv->execute();

			$sql_db=$sqlsrv->prepare("SELECT name as db from sys.databases where NAME NOT LIKE '%master%' AND name NOT LIKE '%tempdb%' AND name NOT LIKE '%reportserver%' AND name NOT LIKE '%model%' AND name NOT LIKE '%msdb%'");
			$sql_db->execute();

			// fetch result System Catalog
			while($row = $sql_srv->fetch(PDO::FETCH_ASSOC)){
				//create node Server if doesn't exist
				$neo->run('MERGE(x:Server{name:{name}})',['name'=>$row['srv']]);
			}

			//Get System Catalog (db,schema,table)
			$sql_syscat = $sqlsrv->prepare('
				SELECT @@SERVERNAME as srv,DB_NAME(DB_ID()) as db,SCHEMA_NAME(schema_id) as sch, sys.tables.name as tbl FROM sys.tables');
			$sql_syscat->execute();

			//array for identify column table
			$a = array();
			$b = array();

			//Fetch result System Catalog
			while($row=$sql_syscat->fetch(PDO::FETCH_ASSOC)){
				$a[] = $row;
				$dbname = $row['srv'].".".$row['db'];
				$schname = $dbname.".".$row['sch'];
				$tblname = $schname.".".$row['tbl'];

				$tbl = $row['tbl'];
				$srv = $row['srv'];
				$db = $row['db'];
				$sch = $row['sch'];

				$stack = $neo->stack();
				//create node Database if doesn't exist and add property
				$stack->push('MERGE(x:Database{name:{name2}, surname:{name}, server:{srv}})',['name'=>$dbname, 'name2' => $db, 'srv' => $srv]);
				//create node Schema if doesn't exist
				$stack->push('MERGE (s:Schema {name: {name2}, surname:{name}, database:{name3}, server:{name4} })',['name' => $schname, 'name2' => $sch, 'name3' => $db, 'name4' => $srv]);
				//create node Table if doesn't exist
				$stack->push('MERGE (a:Table {surname: {name} })',['name' => $tblname]);
				//add property of table
				$stack->push('MATCH (a:Table { surname: {table} }) SET a += {colname}', ['table' => $tblname, 'colname' => ['name' => $tbl, 'database' => $db, 'server' => $srv, 'schema' => $sch ] ]);
				//create relationship Schema to Table
				$stack->push('MATCH (a:Schema {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Tbl]->(b)',['name1'=>$schname,'name2'=>$tblname]);
				//create relationship Database to Schema
				$stack->push('MATCH (a:Database {surname: {name1} }),(b:Schema {surname: {name2} }) MERGE (a)-[:Sch]->(b)',['name1'=>$dbname,'name2'=>$schname]);
				//create relationship Server to Database
				$stack->push('MATCH (a:Server {name: {name1} }),(b:Database {surname: {name2} }) MERGE (a)-[:Db]->(b)',['name1'=>$row['srv'],'name2'=>$dbname]);
				$neo->runStack($stack);

			}

			//add property column to node table
			$sql_col = $sqlsrv->prepare("
				SELECT @@SERVERNAME as srv,DB_NAME(DB_ID()) as db,SCHEMA_NAME(schema_id) as sch, sys.tables.name as tbl,sys.columns.name as col FROM sys.tables inner join sys.columns on sys.tables.object_id = sys.columns.object_id 
				");
			$sql_col->execute();

			while ($row = $sql_col->fetch(PDO::FETCH_ASSOC)) {
				$b[] = $row;
			}

			for ($i=0; $i < count($a) ; $i++) { 
				$col = '';
				for ($j=0; $j < count($b) ; $j++) {
					if ($a[$i]['tbl'] === $b[$j]['tbl']) {
					 	$col .= $b[$j]['col']." ";
					 } 
				}

				$a[$i] = array( 'srv' => $a[$i]['srv'], 'db' =>$a[$i]['db'] , 'sch' =>$a[$i]['sch'] ,'tbl' => $a[$i]['tbl'], 'col' => $col  );			
			}

			foreach ($a as $col) {
				
				$db = $col['srv'].".".$col['db'];
				$sch = $db.".".$col['sch'];
				$tbl = $sch.".".$col['tbl'];
				$col = $col['col'];

				$neo->run('MATCH (a:Table { surname: {table}}) SET a += {colname}', ['table' => $tbl, 'colname' => ['column' => $col ] ]);

			}


			//add property primary key to node table
			$sql_pk = $sqlsrv->prepare("
				SELECT OBJECT_NAME(OBJECT_ID) AS pk,
				SCHEMA_NAME(schema_id) AS sch,
				OBJECT_NAME(parent_object_id) AS tbl
				FROM sys.objects 
				WHERE type_desc = 'PRIMARY_KEY_CONSTRAINT'");
			$sql_pk->execute();

			while ($row=$sql_pk->fetch(PDO::FETCH_ASSOC)) {
				$pk = $row['pk'];
				$tbl = $row['tbl'];

				$neo->run('MATCH (a:Table { name: {table} }) SET a += {prop}', ['table' => $tbl, 'prop' => ['PK' => $pk ]]);
			}
			
			//add SP and Function
			$sql_sp = $sqlsrv->prepare("
				SELECT @@SERVERNAME as srv, SPECIFIC_CATALOG as db, SPECIFIC_SCHEMA as sch, SPECIFIC_NAME as sp_name, ROUTINE_TYPE, ROUTINE_BODY, ROUTINE_DEFINITION as sql, SQL_DATA_ACCESS, CREATED, LAST_ALTERED
					FROM information_schema.routines
				");
			$sql_sp->execute();


			while($row=$sql_sp->fetch(PDO::FETCH_ASSOC)){

				$srv = $row['srv'];
				$db = $row['db'];
				$sch = $row['sch'];
				$schname = $row['srv'].".".$row['db'].".".$row['sch'];
				$name = $row['sp_name'];
				$surname = $row['srv'].".".$row['db'].".".$row['sch'].".".$row['sp_name'];
				$create = $row['CREATED'];
				$last = $row['LAST_ALTERED'];

				$stack = $neo->stack();

				if ($row['ROUTINE_TYPE'] === 'FUNCTION') {

					$stack->push('MERGE (x:Function {name: {name}, surname:{surname}, server:{srv}, database:{db}, schema:{sch}, created:{create}, last_altered:{last}  })',['name' => $name, 'surname' => $surname, 'srv' => $srv, 'db' => $db,  'sch' => $sch, 'create' => $create, 'last' => $last]);
					$stack->push('MATCH (a:Schema {surname: {sch} }), (b:Function {surname: {name} }) MERGE (a)-[:Func]->(b)',['sch'=>$schname,'name'=>$surname]);
				}

				if ($row['ROUTINE_TYPE'] === 'PROCEDURE') {

					$stack->push('MERGE (x:SP {name: {name}, surname:{surname}, server:{srv}, database:{db}, schema:{sch}, created:{create}, last_altered:{last}  })',['name' => $name, 'surname' => $surname, 'srv' => $srv, 'db' => $db,  'sch' => $sch, 'create' => $create, 'last' => $last]);
					$stack->push('MATCH (a:Schema {surname: {sch} }), (b:SP {surname: {name} }) MERGE (a)-[:SP]->(b)',['sch'=>$schname,'name'=>$surname]);
				}
				
				$neo->runStack($stack);
			}

			//Get FK Relationship from System Catalog
			$sql_fk = $sqlsrv->prepare('
				SELECT
				@@SERVERNAME as srv,DB_NAME(DB_ID()) as db,  
					obj.name AS fk_rel,
				    sch2.name AS par_sch,
				    tab2.name AS par_tbl,
				    col2.name AS par_col,
					sch1.name AS ref_sch,
				    tab1.name AS ref_tbl,
				    col1.name AS ref_col
				FROM sys.foreign_key_columns fkc
				INNER JOIN sys.objects obj
				    ON obj.object_id = fkc.constraint_object_id
				INNER JOIN sys.tables tab1
				    ON tab1.object_id = fkc.parent_object_id
				INNER JOIN sys.columns col1
				    ON col1.column_id = parent_column_id AND col1.object_id = tab1.object_id
				INNER JOIN sys.tables tab2
				    ON tab2.object_id = fkc.referenced_object_id
				INNER JOIN sys.columns col2
				    ON col2.column_id = referenced_column_id AND col2.object_id = tab2.object_id
				INNER JOIN sys.schemas sch1
				    ON tab1.schema_id = sch1.schema_id
				INNER JOIN sys.schemas sch2
				    ON tab2.schema_id = sch2.schema_id');	
			$sql_fk->execute();
			
			while($row=$sql_fk->fetch(PDO::FETCH_ASSOC)){
				$ref_col = $row['srv'].".".$row['db'].".".$row['ref_sch'].".".$row['ref_tbl'];
				$par_col = $row['srv'].".".$row['db'].".".$row['par_sch'].".".$row['par_tbl'];

				$par = $row['par_tbl']; 
				$ref = $row['ref_tbl'];
				$fk = $row['fk_rel'];
				//create relationship Server to Database
				$neo->run('MATCH (a:Table {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:fk_rel {FK: {name3}, par_tbl: {name4}, ref_tbl: {name5} }]->(b)',['name1'=>$par_col,'name2'=>$ref_col, 'name3' => $fk, 'name4' => $par, 'name5' => $ref]);
			}

// $end = microtime(true);
// $execution_time = $end - $start;
// echo "Waktu eksekusi script ".$execution_time." milisecond";
		
	?>