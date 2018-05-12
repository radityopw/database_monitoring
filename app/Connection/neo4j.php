<?php

namespace UserDep\Connection;

use GraphAware\Neo4j\Client\ClientBuilder;

$connPrefix = 'database.connections.neo4j.';
$username = config($connPrefix.'username');
$password = config($connPrefix.'password');
$host = config($connPrefix.'host');
$port = config($connPrefix.'port');

return ClientBuilder::create()
        ->addConnection('bolt', "bolt://$username:$password@$host:$port")
        ->build();