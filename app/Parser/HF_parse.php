<?php include 'config.php';
		//get MySQL data yang berisi DMV queries 
		$mysql = $cons->prepare('SELECT * from text');
		$mysql->execute();
		//parsing per row result
		require 'Pars.php';
		while($row=$mysql->fetch(PDO::FETCH_ASSOC)){
			try{
				$data = $pars->parse($row['text']);
				$stack = $neo->stack();

				print_r($data);
				echo "<br> <br>";
			if($data['FROM']){		
				foreach($data['FROM'] as $d){
					$join = $d['join_type'];
					$i = 1;
					foreach($d['no_quotes']['parts'] as $r){
						if($i % 2 == 0){
							$i++;
							$stack->push('MERGE (x:Table {name: {name} })',['name' => trim($r,"[]")]);
						}
						else {
							$i++;
							$stack->push('
								MERGE (x:Schema {name: {name} })',
								['name' => trim($r,"[]")]);
							$o = $r+1;
							//create relationship Schema to Table
							$stack->push('MATCH (a:Schema {name: {name1} }),(b:Table {name: {name2} }) MERGE (a)-[:Tbl]->(b)',['name1'=>$r,'name2'=>$o]);
						}
					}
					$stack->push('MATCH (a:Column {name: {name1} }),(b:Column {name: {name2} }) MERGE (a)<-[:'.$d['join_type'].']->(b)',['name1'=>$d['ref_clause']['0']['no_quotes']['parts']['1'],'name2'=>$d['ref_clause']['2']['no_quotes']['parts']['1']]);
				}
			}
			$neo->runStack($stack);
			}catch(Exception $e){				
			// insert error statement to each row on 'text' 
			$mysql = $cons->prepare("
				UPDATE text SET error = '".$e."'
				WHERE id ='".$row['id']."';");
			$mysql->execute();
				echo $e;
				echo '<e>';
			}

			
		} ?>