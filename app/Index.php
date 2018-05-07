<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once 'Hello.php';

echo getenv('SQLSRV_DB_HOST');

// $configLoader = require_once __DIR__.'/../bootstrap/loader/config/configloader.php';

// echo $configLoader->get('database.connections.sqlsrv.database');

