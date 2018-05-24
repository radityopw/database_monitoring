<?php 
	
	include('config.php');
	require 'SP_parser.php';
	require 'SP_splitter.php';
	// require_once dirname(__FILE__) . '\vendor\parser\src\PHPSQLParser.php';

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

	$sql_sp = $con->prepare("
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
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Using]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Using]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Using]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Using]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				
				}
			}

			echo "<br><br>";

			$join = $sp_pars->join($raw);
			if (isset($join)) {
				foreach ($join as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Join]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Join]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Join]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Join]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$merge = $sp_pars->merge($raw);
			if (isset($merge)) {
				foreach ($merge as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Merge]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Merge]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Merge]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Merge]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$truncate = $sp_pars->truncate($raw);
			if (isset($truncate)) {
				foreach ($truncate as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Truncate]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Truncate]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Truncate]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Truncate]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$insert = $sp_pars->insert($raw);
			if (isset($insert)) {
				foreach ($insert as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Insert]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Insert]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Insert]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Insert]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$update = $sp_pars->update($raw);
			if (isset($update)) {
				foreach ($update as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Update]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Update]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Update]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:Table {surname: {name2} }) MERGE (a)-[:Update]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$exec = $sp_pars->exec($raw);
			if (isset($exec)) {
				foreach ($exec as $value) {
				if (substr_count($value, "." ) == 0) {
					$value = $row['srv'].".".$row['db'].".".$row['sch'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:SP {surname: {name2} }) MERGE (a)-[:Execute]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, "." ) == 1) {
					$value = $row['srv'].".".$row['db'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:SP {surname: {name2} }) MERGE (a)-[:Execute]->(b)',['name1'=>$spname,'name2'=>$value]);

				}
				elseif (substr_count($value, ".") == 2) {
					$value = $row['srv'].".".$value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:SP {surname: {name2} }) MERGE (a)-[:Execute]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				elseif (substr_count($value, ".") == 3) {
					$value = $value;
					$stack->push('MATCH (a:SP {surname: {name1} }),(b:SP {surname: {name2} }) MERGE (a)-[:Execute]->(b)',['name1'=>$spname,'name2'=>$value]);
				}
				echo $value."<br> \n" ;
				}
			}

			$neo->runStack($stack);
					
	}	

?>