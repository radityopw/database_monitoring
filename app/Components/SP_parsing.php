<?php 
	
	require_once __DIR__.'/../hihi.php';

	use Dependency\Parser\SP_parser;
	use Dependency\Parser\SP_splitter;

	$sp_pars = new SP_parser();
	$sp_split = new SP_splitter();

	$db = 'resits';
	$sqlsrv = createSQLServerConnection($db);
    $neo = createNeo4jConnection('sp');

	function strafter($string, $substring) {
  		$pos = strpos($string, $substring);
  		if ($pos === false)
   			return $string;
  		else  
   			return(substr($string, $pos+strlen($substring)));
	}

	function strbefore($string, $substring) {
  		$pos = strpos($string, $substring);
  		if ($pos === false)
   			return $string;
  		else  
   			return(substr($string, 0, $pos));
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
		echo $i;print_r($raw);echo "<br> <br>"; $i++;

		$stack = $neo->stack();

		$key = array_search("proc", $raw);
		$sp = strafter($raw[$key+1],".");
		$spname = $row['srv'].".".$row['db'].".".$row['sch'].".".$sp;
		echo "Nama SP : ".$spname;
		echo "<br><br>";
			
			$from = $sp_pars->from($raw);
			if (isset($from)) {
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

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.From="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				
				}
			}

			echo "<br><br>";

			$join = $sp_pars->join($raw);
			if (isset($join)) {
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
				
				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.Join="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$merge = $sp_pars->merge($raw);
			if (isset($merge)) {
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

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.Merge="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$truncate = $sp_pars->truncate($raw);
			if (isset($truncate)) {
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

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.Truncate="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$insert = $sp_pars->insert($raw);
			if (isset($insert)) {
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

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.Insert="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$update = $sp_pars->update($raw);
			if (isset($update)) {
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

				$stack->push('MATCH (a:SP {surname: {name1} }), (b:Table {surname: {name2} }) MERGE (a)-[r:Use]->(b) SET r.Update="Yes"',['name1'=>$spname,'name2'=>$value]);
				echo $value."<br> \n" ;
				}
			}

			$exec = $sp_pars->exec($raw);
			if (isset($exec)) {
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

			$neo->runStack($stack);
					
	}	

?>