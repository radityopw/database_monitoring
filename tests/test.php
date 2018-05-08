<?php

$serverName = "mssqlserver";
$connectionOptions = [
    // "Database" => "yourDatabase",
    "Uid" => "sa",
    "PWD" => "fairy@test13"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    echo "Connection established.<br />";
    $version = sqlsrv_query($conn, 'SELECT @@VERSION'); 
    $row = sqlsrv_fetch_array($version);

    echo $row[0];

    // Clean up
    sqlsrv_free_stmt($version);
} else {
     echo "Connection could not be established.<br />";
     die(print_r( sqlsrv_errors(), true));
}