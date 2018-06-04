<?php

namespace Dependency\Components;

require_once __DIR__.'/../hihi.php';

$databaseConfig = require config_path('database.php');
$queryConfig = require config_path('query.php');
$extractResult = require __DIR__.'/user_extract.php';
// dd($extractResult);

//Config
$neo4jConfig = $databaseConfig['connections']['neo4j']['user'];
$neo4jAllConfig = $databaseConfig['connections']['neo4j'];
$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];
// dd($sqlSrvConfig);

//Connection
$neo4j = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password']);

//Query
$extractServer  = $queryConfig['sqlserver']['extract_servername'];

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
try {
    $neo4j->run("
    CALL dbms.security.deleteUser('$user')
    CALL dbms.security.createUser('$user','$password') 
    CALL dbms.security.addRoleToUser('reader', '$user')
    MATCH (n)
    RETURN n");
} catch (\Exception $e) {
    $neo4j->run("
    CALL dbms.security.createUser('$user','$password') 
    CALL dbms.security.addRoleToUser('reader', '$user')
    MATCH (n)
    RETURN n");
}
//Create new node from server
$stack->push("MERGE (s:Server {name: {serverName}})", $serverArray);
$serverPrefix = "{$serverName}.";

foreach ($extractResult as $result) {
    $databaseName = $result->getDatabase();
    $databasePrefix = "{$serverPrefix}{$databaseName}.";
    $databaseArray = [
        'databaseName' => $databaseName,
        'databaseSurname' => rtrim($databasePrefix, "."),
    ];
    $serverToDbArray =  array_merge($serverArray, $databaseArray);
    //Create Server to Database Relationships
    $stack->push("MERGE (s:Server {name: {serverName}}) 
        MERGE (d:Database {name: {databaseName}, surname: {databaseSurname}}) 
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
            'databaseUserSurname' => "{$databasePrefix}{$databaseUserName}",
            'userType' => $userType,
        ];

        $dbToUserArray = array_merge($dbUserArray, $databaseArray);
        //Create Database to User relationships
        $stack->push("MERGE (d:Database {name: {databaseName}, surname: {databaseSurname}}) 
            MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
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
                'roleSurname' => "{$databasePrefix}{$role}"
            ];
            $roleToUserArray = array_merge($roleArray, $dbUserArray);
            //Create Role to User relationships
            $stack->push("MERGE (r:Role {name: {role}, surname: {roleSurname}}) 
                MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                MERGE (r)<-[y:HAS_RELATIONSHIPS]-(u)
                SET y.MEMBER_OF = true", $roleToUserArray);
        }
        $schemaPrefix = "{$databasePrefix}{$schema}.";
        if ($schema !== null) {
            $schemaArray = [
                'schema' => $schema,
                'schemaSurname' => rtrim($schemaPrefix, ".")
            ];
            $schemaToDbArray = array_merge($schemaArray, $databaseArray);
            $stack->push("MERGE (o:Database {name: {databaseName}, surname:{databaseSurname}}) 
                MERGE (sc:Schema {name: {schema}, surname:{schemaSurname}}) 
                MERGE (o)-[y:HAS_RELATIONSHIPS]->(sc)
                SET y.SCHEMA = true", $schemaToDbArray);
            // $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) 
            //     MERGE (sc:Schema {name: {schema}}) 
            //     MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
            //     SET y.OWNS = true", $schemaToObjArray);
        }
        if ($permissionType !== null) {
            //Create User to Object relationships
            $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                MERGE (u)-[p:HAS_RELATIONSHIPS]->(o)
                SET p.$permissionType = true, p.$permissionState = true";
            if ($objectName !== null) {
                $objectPrefix = "{$schemaPrefix}{$objectName}.";
                $objectArray = [
                    'objectName' => $objectName,
                    'objectType' => $objectType,
                    'objectSurname' => rtrim($objectPrefix, ".")
                ];
                //Create Object to Schema Relationships
                $schemaToObjArray = array_merge($schemaArray, $objectArray);
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                    MERGE (sc:Schema {name: {schema}, surname:{schemaSurname}}) 
                    MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
                    SET y.OWNS = true", $schemaToObjArray);
                if ($columnName !== null) {
                    $columnArray = [
                        'columnName' => $columnName,
                        'columnSurname' => "{$objectPrefix}{$columnName}"
                    ];
                    $objToColumnArray = array_merge($objectArray, $columnArray);
                    //Create Object to Column Relationships
                    $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                        MERGE (c:Column {name: {columnName}, surname:{columnSurname}}) 
                        MERGE (o)-[y:HAS_RELATIONSHIPS]->(c)
                        SET y.COLUMN = true", $objToColumnArray);
                }
            } else {
                switch ($objectType) {
                    case "DATABASE":
                        $objectArray = [
                            'objectName' => $databaseName,
                            'objectType' => $objectType,
                            'objectSurname' => rtrim($databasePrefix, ".")
                        ];
                        //Create User to Database Relationships
                        $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                            MERGE (o:Database {name: {objectName}, surname:{objectSurname}}) 
                            MERGE (u)-[p:HAS_RELATIONSHIPS]-(o)
                            SET p.$permissionType = true, p.$permissionState = true";
                    break;
                    default:
                        $objectArray = [
                            'objectName' => $objectType,
                            'objectType' => $objectType,
                        ];
                }
            }
            //Create Object to User Relationship
            $userToPermToObjArray = array_merge($dbUserArray, $objectArray);
            $stack->push($query, $userToPermToObjArray);
        }
    }
}
$neo4j->runStack($stack);
dd("SUCCEDED");