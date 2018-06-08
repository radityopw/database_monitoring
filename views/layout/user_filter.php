<?php

try {
    $postVar = (object) $_POST;
    dump($postVar);
    $stack = tap($neo4j->stack())->push('MATCH (n)-[x]-(y)');
    $from_node_type = $postVar->from_node_type;
    $fromColl = collect($postVar->from_node)->filter(function($value, $key){
        return $value !== "";
    });
    $to_node_type = $postVar->to_node_type;
    $toColl = collect($postVar->to_node)->filter(function($value, $key){
        return $value !== "";
    });
    if ($from_node_type !== "") {
        dump($from_node_type);
        $stack->push("WHERE n:$from_node_type");
    }
    if ($to_node_type !== "") {
        dump($to_node_type);
        $stack->push("WHERE n:$to_node_type");
    }
    if (!$fromColl->isEmpty()) {
        $from_node = $fromColl->first();
        dump($from_node);
        $stack->push("WHERE id(n) = $from_node");
    }
    if (!$toColl->isEmpty()) {
        $to_node = $toColl->first();
        dump($to_node);
        $stack->push("WHERE id(n) = $to_node");
    }
    // if () {
        
    // }
    $stack->push("n, x, y");
} catch (\Exception $e) {
    $error = true;
}
// if()

?>