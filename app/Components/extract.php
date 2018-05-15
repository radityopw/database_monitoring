<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';
$neo4j = require_once __DIR__.'/../Connection/neo4j.php';

$query = "MATCH (n) RETURN n;";
$result = $neo4j->run($query);

dd($result->records());