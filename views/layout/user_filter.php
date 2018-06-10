<?php

try {
    /**
     * Make Post Var become Object
     */
    $postVar = (object) $_POST;
    dump($postVar);

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
    $query .= "UNWIND x AS rel ";
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
    $query .= "RETURN DISTINCT n, rel, y";
    $results = $neo4j->run($query);
    dump($results);
    foreach($results->records() as $record) {
        dump($record->get('n'));
    }
    dump($results->records());
} catch (\Exception $e) {
    $error = true;
    dd($e);
}