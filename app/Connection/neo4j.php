<?php

namespace UserDep\Connection;

use GraphAware\Neo4j\Client\ClientBuilder;

return ClientBuilder::create()
        ->addConnection('bolt', 'bolt://neo4j:password@localhost:7687')
        ->build();;
