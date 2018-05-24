<?php 
//initiate library
require_once 'vendor/autoload.php';
use GraphAware\Neo4j\Client\ClientBuilder;

// $srv="10.199.2.66";
// $db="resits";
// $user="monitoring";
// $pwd="monitor";

$srv="(local)";
$db="AdventureWorks2014";
$user= NULL;
$pwd= NULL;

//inisiasi koneksi Microsoft SQL Server

// $test = odbc_connect("coba", $user, $pwd);

// var_dump($test);

// exit();

try {$con = new PDO("sqlsrv:Server=$srv;Database=$db",$user,$pwd);
} catch (PDOException $e) {echo $e->getMessage();}

// var_dump($con);

$neo = ClientBuilder::create()
    ->addConnection('bolt', 'bolt://neo4j:secret@localhost:7687') 
    ->build();

// //inisiasi koneksi MySQL
try {$cons = new PDO(
"mysql:host=localhost;dbname=query","root",null);
}catch (PDOException $e) {echo $e->getMessage();}
?>