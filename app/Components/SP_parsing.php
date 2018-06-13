<?php 
	// $start = microtime(true);
	require_once __DIR__.'/../hihi.php';

	use Dependency\Parser\SP_parser;
	use Dependency\Parser\SP_splitter;

	$sp_pars = new SP_parser();
	$sp_split = new SP_splitter();

	$databaseConfig = require config_path('database.php');

	$neo4jConfig = $databaseConfig['connections']['neo4j']['sp'];
	$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];

	$neo = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
	$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password'], $sqlSrvConfig['database']);

	function strafter($string, $substring) {
  		$pos = strpos($string, $substring);
  		if ($pos === false)
   			return $string;
  		else  
   			return(substr($string, $pos+strlen($substring)));
	}

	$sql_sp = $sqlsrv->prepare("
				SELECT @@SERVERNAME as srv, SPECIFIC_CATALOG as db, SPECIFIC_SCHEMA as sch, SPECIFIC_NAME as sp_name, ROUTINE_TYPE, ROUTINE_BODY, ROUTINE_DEFINITION as sql, SQL_DATA_ACCESS, CREATED, LAST_ALTERED
					FROM information_schema.routines
					WHERE routine_type = 'PROCEDURE'
				");
	$sql_sp->execute();

	$i = 1;
	while ($row=$sql_sp->fetch(PDO::FETCH_ASSOC)) {

		$raw = $sp_split->split($row['sql']);
		// echo $i;
		echo $row['sql']; 
		echo "<br><br>"; print_r($raw);echo "<br> <br>"; 
		// $i++;

		$stack = $neo->stack();

		$key = array_search("proc", $raw);
		$sp = strafter($raw[$key+1],".");
		$spname = $row['srv'].".".$row['db'].".".$row['sch'].".".$sp;
		echo "Nama SP : ".$spname;
		echo "<br><br>";
			
			$from = $sp_pars->from($raw);
			if (isset($from)) {
				echo "From: <br>";
				foreach ($from as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
				}

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "Yes", r.Join = "No", r.Merge = "No", r.Truncate = "No", r.Insert = "No", r.Update = "No" 
							ON MATCH SET r.From = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				
				}
			}

			// echo "<br><br>";

			$join = $sp_pars->join($raw);
			if (isset($join)) {
				echo "Join: <br>";
				foreach ($join as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
				}
				
				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "No", r.Join = "Yes", r.Merge = "No", r.Truncate = "No", r.Insert = "No", r.Update = "No" 
							ON MATCH SET r.Join = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$merge = $sp_pars->merge($raw);
			if (isset($merge)) {
				echo "Merge: <br>";
				foreach ($merge as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
				}

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "No", r.Join = "No", r.Merge = "Yes", r.Truncate = "No", r.Insert = "No", r.Update = "No" 
							ON MATCH SET r.Merge = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$truncate = $sp_pars->truncate($raw);
			if (isset($truncate)) {
				echo "Truncate: <br>";
				foreach ($truncate as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;	
				}

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "No", r.Join = "No", r.Merge = "No", r.Truncate = "Yes", r.Insert = "No", r.Update = "No" 
							ON MATCH SET r.Truncate = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$insert = $sp_pars->insert($raw);
			if (isset($insert)) {
				echo "Insert: <br>";

				foreach ($insert as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;	
				}

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "No", r.Join = "No", r.Merge = "No", r.Truncate = "No", r.Insert = "Yes", r.Update = "No" 
							ON MATCH SET r.Insert = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$update = $sp_pars->update($raw);
			if (isset($update)) {
				echo "Update: <br>";

				foreach ($update as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
				}

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname:{name2} })
							MERGE (a)-[r:Use]->(b)
							ON CREATE SET r.From = "No", r.Join = "No", r.Merge = "No", r.Truncate = "No", r.Insert = "No", r.Update = "Yes" 
							ON MATCH SET r.Update = "Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$exec = $sp_pars->exec($raw);
			if (isset($exec)) {
				echo "Execute: <br>";

				foreach ($exec as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;					
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
				}
				$stack->push('MATCH (a:SP {surname: {name1} }),(b:SP {surname: {name2} }) MERGE (a)-[:Execute]->(b)',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}
			echo "<br>";

			$neo->runStack($stack);
					
	}	
// $end = microtime(true);
// $execution_time = $end - $start;
// echo "Waktu eksekusi script ".$execution_time." milisecond";
?>