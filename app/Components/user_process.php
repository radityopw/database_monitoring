<?php

namespace Dependency\Components;

require_once __DIR__.'/../hihi.php';

$databaseConfig = require config_path('database.php');
$queryConfig = require config_path('query.php');
$extractResult = require __DIR__.'/user_extract.php';

//Config
$neo4jConfig = $databaseConfig['connections']['neo4j']['user'];
$neo4jAllConfig = $databaseConfig['connections']['neo4j'];
$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];
// dd($sqlSrvConfig);

//Connection
$neo4j = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlsrvConfig['port'], $sqlsrvConfig['username'], $sqlsrvConfig['password']);

//Query
$extractServer  = $queryConfig['extract_servername'];

//Get servername from sql server
$serverName = tap($sqlsrv->prepare($extractServer))
->execute()
->fetchObject()
->server;

$serverArray  = [
    'serverName' => $serverName
];
//Delete all nodes and relationships
$stack = tap($neo4j->stack())->push("MATCH (n) DETACH DELETE n");

//User and password for user with role reader
$user = $neo4jAllConfig['username_read'];
$password = $neo4jAllConfig['password_read'];
//Create user with role Reader
$stack->push("CALL dbms.security.createUser('$user','$password') 
CALL dbms.security.addRoleToUser('reader', '$user')");
//Create new node from server
$stack->push("MERGE (s:Server {name: {serverName}})", $serverArray);

foreach ($extractResult as $result) {
    $databaseName = $result->getDatabase();
    $databaseArray = [
        'databaseName' => $databaseName,
    ];
    $serverToDbArray =  array_merge($serverArray, $databaseArray);
    //Create Server to Database Relationships
    $stack->push("MERGE (s:Server {name: {serverName}}) 
        MERGE (d:Database {name: {databaseName}}) 
        MERGE (s)-[y:HAS_RELATIONSHIPS]->(d) 
        SET y.DATABASE = true", $serverToDbArray);
    $resultMapping = $result->getResult();
    foreach ($resultMapping as $eachMapping) {
        //Initialize all variable from mapping result
        $userType = $eachMapping->userType;
        $databaseUserName = $eachMapping->databaseUserName;
        $loginName = $eachMapping->loginName;
        $role = $eachMapping->role;
        $permissionType = $eachMapping->permissionType ? str_replace(" ", "_", $eachMapping->permissionType) : null;
        $permissionState = $eachMapping->permissionState;
        $objectType = $eachMapping->objectType;
        $schema = $eachMapping->schema;
        $objectName = $eachMapping->objectName;
        $columnName = $eachMapping->columnName;

        $dbUserArray = [
            'databaseUserName' => $databaseUserName,
            'userType' => $userType,
        ];

        $dbToUserArray = array_merge($dbUserArray, $databaseArray);
        //Create Database to User relationships
        $stack->push("MERGE (d:Database {name: {databaseName}}) 
            MERGE (u:User {name: {databaseUserName}, type: {userType}}) 
            MERGE (d)-[y:HAS_RELATIONSHIPS]-(u)
            SET y.USER = true", $dbToUserArray);
        // if ($loginName !== null) {
        //     $loginArray = [
        //         'loginName' => $loginName
        //     ];
        //     $loginToServerArray = array_merge($serverArray, $loginArray);
        //     $stack->push("MERGE (s:Server {name: {serverName}}) MERGE (l:Login {name: {loginName}}) MERGE (l)-[y:USES]->(s)", $loginToServerArray);
        //     $loginToUserArray = array_merge($loginArray, $dbUserArray);
        //     $stack->push("MERGE (l:Login {name: {loginName}}) MERGE (u:User {name: {databaseUserName}, type: {userType}}) MERGE (l)-[y:MAPPED_TO]->(u)", $loginToUserArray);
        // }
        if ($role !== null) {
            $roleArray = [
                'role' => $role,
            ];
            $roleToUserArray = array_merge($roleArray, $dbUserArray);
            //Create Role to User relationships
            $stack->push("MERGE (r:Role {name: {role}}) 
                MERGE (u:User {name: {databaseUserName}, type: {userType}}) 
                MERGE (r)<-[y:HAS_RELATIONSHIPS]-(u)
                SET y.MEMBER_OF = true", $roleToUserArray);
        }
        if ($permissionType !== null) {
            $permissionArray = [
                'permissionState' => $permissionState,
            ];
            if ($objectName !== null) {
                $objectArray = [
                    'objectName' => $objectName,
                    'objectType' => $objectType,
                ];
            } else {
                switch ($objectType) {
                    case "DATABASE":
                        $objectArray = [
                            'objectName' => $databaseName,
                            'objectType' => $objectType,
                        ];
                        //Create User to Database Relationships
                        $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}}) 
                            MERGE (o:Database {name: {objectName}}) 
                            MERGE (u)-[p:HAS_RELATIONSHIPS]-(o)
                            SET p.$permissionType = true, p.permissionState = true";
                    break;
                    default:
                        $objectArray = [
                            'objectName' => $objectType,
                            'objectType' => $objectType,
                        ];
                        //Create User to Object relationships
                        $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}}) 
                            MERGE (o:Object {name: {objectName}, type: {objectType}}) 
                            MERGE (u)-[p:HAS_RELATIONSHIPS]->(o)
                            SET p.$permissionType = true, p.permissionState = true";
                }
            }
            $userToPermToObjArray = array_merge($dbUserArray, $permissionArray, $objectArray);
            $stack->push($query, $userToPermToObjArray);
            if ($columnName !== null) {
                $columnArray = [
                    'columnName' => $columnName
                ];
                $objToColumnArray = array_merge($objectArray, $columnArray);
                //Create Object to Column Relationships
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) 
                    MERGE (c:Column {name: {columnName}}) 
                    MERGE (o)-[y:HAS_RELATIONSHIPS]->(c)
                    SET y.COLUMN = true", $objToColumnArray);
            }
            if ($schema !== null) {
                $schemaArray = [
                    'schema' => $schema,
                ];
                $schemaToObjArray = array_merge($schemaArray, $objectArray);
                //Create Object to Schema Relationships
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) 
                    MERGE (sc:Schema {name: {schema}}) 
                    MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
                    SET y.OWNS = true", $schemaToObjArray);
                // $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) 
                //     MERGE (sc:Schema {name: {schema}}) 
                //     MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
                //     SET y.OWNS = true", $schemaToObjArray);
            }
        }
    }
}
$neo4j->runStack($stack);
dd("SUCCEDED");