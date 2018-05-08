<?php

namespace UserDep\Connection;

use PDO;

$prefixConfig = 'database.connections.sqlsrv.';
$serverName = 'tcp:'.config($prefixConfig.'host').','.config($prefixConfig.'port');
$database = config($prefixConfig.'database');
$username = config($prefixConfig.'username');
$password = config($prefixConfig.'password');
$prefix = config($prefixConfig.'prefix');

$conn = new PDO("$prefix:server=$serverName ; Database=$database", $username, $password);
$conn->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);

return $conn;
