<?php

try {
    // throw new Exception('Something unexpected happen!');
    /**
     * Make Post Var become Object
     */
    $postVar = (object) $_POST;
    // dump($postVar);

    /**
     * Initialize All Variables
     */
    $from_node_type = $postVar->from_node_type;
    $fromColl = collect($postVar->from_node)->filter(function($value, $key){
        return $value !== "";
    });
    $to_node_type = $postVar->to_node_type;
    $toColl = collect($postVar->to_node)->filter(function($value, $key){
        return $value !== "";
    });
    $relColl = collect($postVar->relationships);
    $hop = $postVar->hop_count;
    
    /**
     * Start the query
     */
    $query = "MATCH (n)-[x:HAS_RELATIONSHIPS*..$hop]->(y) ";
    /**
     * Check if Node Source Type is chosen
     */
    if ($from_node_type !== "") {
        // dump($from_node_type);
        // $query .= "WITH * ";
        $query .= "WHERE n:$from_node_type ";
    }
    /**
     * Check if Node Destination Type is chosen
     */
    if ($to_node_type !== "") {
        // dump($to_node_type);
        // $query .= "WITH * ";
        $query .= "AND y:$to_node_type ";
    }
    /**
     * Check if Node Source name is chosen
     */
    if (!$fromColl->isEmpty()) {
        $from_node = $fromColl->first();
        // dump($from_node);
        // $query .= "WITH * ";
        $query .= "AND id(n) = $from_node ";
    }
    /**
     * Check if Node Destination name is chosen
     */
    if (!$toColl->isEmpty()) {
        $to_node = $toColl->first();
        // dump($to_node);
        // $query .= "WITH * ";
        $query .= "AND id(y) = $to_node ";
    }
    /**
     * Iterate the relationship from list of relationship
     */
    $query .= "UNWIND x AS rel ";
    /**
     * Check if Relationship attribute is chosen
     */
    if (!$relColl->isEmpty()) {
        // dump($relColl);
        foreach($relColl as $relationship) {
            $query .= "WITH * ";
            // $query .= "AND extract(eachRel IN x | exists(eachRel.$relationship)) ";
            $query .= "WHERE exists(rel.$relationship) ";
            // $query .= "filter exists(eachRel.$relationship) ";
        }
    }
    // $query .= "WITH * ";
    // $query .= "AND x:HAS_RELATIONSHIPS*1..$hop ";
    /**
     * Query to return the result
     */
    $query .= "RETURN DISTINCT n, rel, y";

    /**
     * Run the query
     */
    $results = $neo4j->run($query);
    
    /**
     * Initiate nodes and relationships results
     */
    $nodeResColl = collect();
    $relResColl = collect();
    // dump($results);
    foreach($results->records() as $record) {
        $nodeSource = $record->get('n');
        $nodeDest = $record->get('y');
        $relationship = $record->get('rel');
        $nodeResColl->push($nodeSource);
        $nodeResColl->push($nodeDest);
        $relResColl->push($relationship);
        // dump("This is the node source: ", $nodeSource->values());
        // dump("This is the node destination: ", $nodeDest);
        // dump("This is the relationship: ", $relationship);
    }
    /**
     * Mapping result node to array of nodes
     */
    if(!$nodeResColl->isEmpty()) {
        $nodeResColl = $nodeResColl->uniqueStrict(function($value) {
            return $value->identity();
        })->map(function ($value) {
            return [
                'id' => $value->identity(),
                'labels' => $value->labels(),
                'properties' => $value->values()
            ];
        });
    }
    
    /**
     * Mapping relationship collection to array of relationships
     */
    if(!$relResColl->isEmpty()){
        $relResColl = $relResColl->uniqueStrict(function($value) {
            return $value->identity();
        })->map(function ($value) {
            // dump('relationship: ', $value->startNodeIdentity());
            return [
                'id' => $value->identity(),
                'type' => $value->type(),
                'startNode' => $value->startNodeIdentity(),
                'endNode' => $value->endNodeIdentity(),
                'properties' => $value->values()
            ];
        });
    }

    /**
     * The result collection for neo4jd3
     */
    // $test[] = $nodeResColl->first();
    // dump('Nodes Array', json_encode($test));
    // $test = array_values($nodeResColl->toArray());
    // dump('Nodes Array', json_encode($test));
    $resultNeo4jCollection = collect([
        'results' => [
            [
                // 'columns' => [],
                'data' => [
                    [
                        'graph' => [
                            'nodes'=> array_values($nodeResColl->toArray()),
                            'relationships' => array_values($relResColl->toArray()),
                        ]
                    ]
                ]
            ]
        ],
        // 'errors' => [

        // ]
    ]);
    // dd("This is neo4j data in JSON format: ", $resultNeo4jCollection->toJson());

    // dump('This is the result JSON: ', $resultNeo4jCollection->toJson());
} catch (\Exception $e) {
    $error = true;
    $errorMessage = $e->getMessage();
    // dump($e->getMessage());
}