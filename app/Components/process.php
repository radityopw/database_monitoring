<?php

namespace UserDep\Components;

require_once __DIR__.'/../index.php';

$neo4j = createNeo4jConnection();
$extractResult = require_once __DIR__.'/extract.php';

$query = config('query.sqlserver.extract_servername');
$serverName = tap(createSqlServerConnection()->prepare($query))
->execute()
->fetchObject()
->server;

$serverArray  = [
    'serverName' => $serverName
];
$stack = tap($neo4j->stack())->push("MATCH (n) DETACH DELETE n");
$stack->push("MERGE (s:Server {name: {serverName}})", $serverArray);
// $neo4j->run("MERGE (s:Server {name: {serverName}})", $serverArray);

foreach ($extractResult as $result) {
    $databaseName = $result->getDatabase();
    $databaseArray = [
        'databaseName' => $databaseName,
    ];
    $serverToDbArray =  array_merge($serverArray, $databaseArray);
    $stack->push("MERGE (s:Server {name: {serverName}}) 
        MERGE (d:Database {name: {databaseName}}) 
        MERGE (s)-[y:HAS_RELATIONSHIPS]->(d) 
        SET y.DATABASE = true", $serverToDbArray);
    $resultMapping = $result->getResult();
    foreach ($resultMapping as $eachMapping) {
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
        // $stack = tap($neo4j->stack())->push("MERGE (d1:Database {name: {databaseName}}) MERGE (u:User {name: {databaseUserName}, type: {userType}}) MERGE (u)-[y:USES]->(d1)", $dbToUserArray);
        $stack->push("MERGE (d:Database {name: {databaseName}}) 
            MERGE (u:User {name: {databaseUserName}, type: {userType}}) 
            MERGE (d)-[y:HAS_RELATIONSHIPS]->(u)
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
            $stack->push("MERGE (r:Role {name: {role}}) MERGE (u:User {name: {databaseUserName}, type: {userType}}) MERGE (r)<-[y:MEMBER_OF]-(u)", $roleToUserArray);
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
                switch($objectType) {
                    case "DATABASE":
                        $objectArray = [
                            'objectName' => $databaseName,
                            'objectType' => $objectType,
                        ];
                    break;
                    default:
                        $objectArray = [
                            'objectName' => $objectType,
                            'objectType' => $objectType,
                        ];
                }
            }
            $userToPermToObjArray = array_merge($dbUserArray, $permissionArray, $objectArray);
            $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}}) MERGE (o:Object {name: {objectName}, type: {objectType}}) MERGE (u)-[p:$permissionType {state: {permissionState}}]->(o)";
            $stack->push($query, $userToPermToObjArray);
            if ($columnName !== null) {
                $columnArray = [
                    'columnName' => $columnName
                ];
                $objToColumnArray = array_merge($objectArray, $columnArray);
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) MERGE (c:Column {name: {columnName}}) MERGE (o)-[y:CONTAINS]->(c)", $objToColumnArray);
            }
            if ($schema !== null) {
                $schemaArray = [
                    'schema' => $schema,
                ];
                $schemaToObjArray = array_merge($schemaArray, $objectArray);
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) MERGE (sc:Schema {name: {schema}}) MERGE (o)<-[y:COLLECTS]-(sc)", $schemaToObjArray);
            }
        }
    }
}
$neo4j->runStack($stack);
dd("SUCCEDED");