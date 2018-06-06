<?php

$postVar = (object) $_POST;
dump($postVar);
$stack = tap($neo4j->stack())->push('MATCH (n)-[x]-(y)');
$from_node_type = $postVar->from_node_type;
$fromColl = collect($postVar->from_node)->filter(function($value, $key){
    return $value !== "";
});
if ($from_node_type !== "") {
    dump($from_node_type);
    $stack->push("WHERE n:$from_node_type");
}
if (!$fromColl->isEmpty()) {
    $from_node = $fromColl->first();
    dump($from_node);
    $stack->push("WHERE id(n) = $from_node");
}

// if()

?>