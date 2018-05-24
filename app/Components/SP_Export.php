<?php 
	include ('config.php');
		//Generate JSON from all Nodes
		$result = $neo->run('MATCH (n) RETURN n.name as name,n.surname as surname,n.server as server, n.database as database, n.schema as schema, n.PK as PK, n.column as column ,n.created as created, n.last_altered as last_altered, id(n) as id,labels(n) as label');
		$records = $result->getRecords();
		// dump($records);

			foreach ($result->getRecords() as $record) {
				$property = array(
					"name"=> $record->value('name'),
					"surname" => $record->value('surname'),
					"server" => $record->value('server'),
					"database" => $record->value('database'),
					"schema" => $record->value('schema'),
					"column" => $record->value('column'),
					"PK" => $record->value('PK'),
					"created" => $record->value('created'),
					"last altered" => $record->value('last_altered')
				);
				$filtered = array_filter($property);
				// dump($filtered);
				$nodes[] = ["id"=>$record->value('id'),
							"labels"=>$record->value('label'),
							"properties"=>
								$filtered
			  					//taruh value lain di sini (jika ada)
			  					
							];
			}

		//Generate JSON from all Relationships
		$result = $neo->run('MATCH (a)-[r]->(b) return id(r) as id,id(a) as start,id(b) as end, type(r) as type, r.FK as FK');
		$records = $result->getRecords();
			foreach ($result->getRecords() as $record) {
			$fk = array(
				"FK" => $record->value('FK')
			);
			$filtered_fk = array_filter($fk);
			  $rel[] = ["id"=>$record->value('id'),
			  			"type"=>$record->value('type'),
			  			"startNode"=>$record->value('start'),
			  			"endNode"=>$record->value('end'),
			  			"properties"=>
			  				$filtered_fk
			  				
			  			];
			}

		$json = ["results" => array([
					"data" => array([
						"graph" => array(
							"nodes" => $nodes,
							"relationships" => $rel
							)])])];
		if (file_put_contents('neodata.json', json_encode($json))) {
				echo "pembuatan json berhasil!";
			}	
			// file_put_contents('neodata.json', json_encode($json));



?>
