<?php

namespace Dependency\Components;

// $startProcess = microtime(true);
/**
 * Get all vendor and required components.
 */
require_once __DIR__.'/../hihi.php';

/**
 * Get all config from php file.
 * Return value of array in the php file.
 */
$databaseConfig = require config_path('database.php');
$queryConfig = require config_path('query.php');
/**
 * Start microtime of extraction process
 */
// $startExtract = microtime(true);
$extractResult = require __DIR__.'/user_extract.php';
// $endExtract = microtime(true);
/**
 * End microtime of extraction process
 */
// dd($extractResult);

/**
 * Extract config from php file array.
 */
$neo4jConfig = $databaseConfig['connections']['neo4j']['user'];
$neo4jAllConfig = $databaseConfig['connections']['neo4j'];
$sqlSrvConfig = $databaseConfig['connections']['sqlsrv'];
// dd($sqlSrvConfig);

/**
 * Create all connection, neo4j and sql server.
 */
$neo4j = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);
$sqlsrv = createSqlServerConnection($sqlSrvConfig['host'], $sqlSrvConfig['port'], $sqlSrvConfig['username'], $sqlSrvConfig['password']);

/**
 * Query to get servername from sql server.
 */
$extractServer  = $queryConfig['sqlserver']['extract_servername'];

/**
 * Get servername from sql server with query.
 */
$serverName = tap($sqlsrv->prepare($extractServer))
->execute()
->fetchObject()
->server;

/**
 * Test to dump the servername from SQL Server
 */
// dd("This is the server name of SQL Server:", $serverName);

$serverArray  = [
    'serverName' => $serverName
];
/**
 * Delete all nodes and relationships.
 * Rebuild all of the graph.
 */
$stack = tap($neo4j->stack())->push("MATCH (n) DETACH DELETE n");

/**
 * Test neo4j to delete all nodes and relationships
 */
// $neo4j->run('MATCH (n) DETACH DELETE n');
// dd("All neo4j nodes and relationships has been deleted successfully.");

/**
 * User and Password for user with role reader
 */
// $user = $neo4jAllConfig['username_read'];
// $password = $neo4jAllConfig['password_read'];
/**
 * Create user with role Reader.
 * Delete the user if user already exists.
 */
// try {
//     $neo4j->run("
//     CALL dbms.security.deleteUser('$user')
//     CALL dbms.security.createUser('$user', '$password', false) 
//     CALL dbms.security.addRoleToUser('reader', '$user')
//     MATCH (n)
//     RETURN n");
// } catch (\Exception $e) {
//     $neo4j->run("
//     CALL dbms.security.createUser('$user', '$password', false) 
//     CALL dbms.security.addRoleToUser('reader', '$user')
//     MATCH (n)
//     RETURN n");
// }
/**
 * Create a new node server with index on name and surname
 */
$stack->push("MERGE (s:Server {name: {serverName}, surname: {serverName}})", $serverArray);
$stack->push("CREATE INDEX ON :Server(name, surname)");
$serverPrefix = "{$serverName}.";

foreach ($extractResult as $result) {
    $databaseName = $result->getDatabase();
    $databasePrefix = "{$serverPrefix}{$databaseName}.";
    $databaseArray = [
        'databaseName' => $databaseName,
        'databaseSurname' => rtrim($databasePrefix, "."),
    ];
    $serverToDbArray =  array_merge($serverArray, $databaseArray);
    /**
     * Create Server to Database relationship with creating new index for name and surname on Database label
     */
    $stack->push("MERGE (s:Server {name: {serverName}, surname: {serverName}}) 
        MERGE (d:Database {name: {databaseName}, surname: {databaseSurname}}) 
        MERGE (s)-[y:HAS_RELATIONSHIPS]->(d) 
        SET y.DATABASE = 'Yes'", $serverToDbArray);
    $stack->push("CREATE INDEX ON :Database(name, surname)");
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
        /**
         *  Create Database to User relationship with index on user of surname and name
         */
        $stack->push("MERGE (d:Database {name: {databaseName}, surname: {databaseSurname}}) 
            MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
            MERGE (d)<-[y:HAS_RELATIONSHIPS]-(u)
            SET y.USER_OF = 'Yes'", $dbToUserArray);
        $stack->push("CREATE INDEX ON :User(name, surname)");
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
            /**
             * Create Role to User Relationships with index on Role of surname and name
             */
            $stack->push("MERGE (r:Role {name: {role}, surname: {roleSurname}}) 
                MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                MERGE (r)<-[y:HAS_RELATIONSHIPS]-(u)
                SET y.MEMBER_OF = 'Yes'", $roleToUserArray);
            $stack->push("CREATE INDEX ON :Role(name, surname)");
        }
        $schemaPrefix = "{$databasePrefix}{$schema}.";
        if ($schema !== null) {
            $schemaArray = [
                'schema' => $schema,
                'schemaSurname' => rtrim($schemaPrefix, ".")
            ];
            $schemaToDbArray = array_merge($schemaArray, $databaseArray);
            /**
             * Create Schema to Database Relationships with index on Schema of surname and name
             */
            $stack->push("MERGE (o:Database {name: {databaseName}, surname:{databaseSurname}}) 
                MERGE (sc:Schema {name: {schema}, surname:{schemaSurname}}) 
                MERGE (o)-[y:HAS_RELATIONSHIPS]->(sc)
                SET y.SCHEMA = 'Yes'", $schemaToDbArray);
            $stack->push("CREATE INDEX ON :Schema(name, surname)");
            // $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}}) 
            //     MERGE (sc:Schema {name: {schema}}) 
            //     MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
            //     SET y.OWNS = 'Yes'", $schemaToObjArray);
        }
        if ($permissionType !== null) {
            $permissionState = $permissionState === "GRANT" ? "Yes": "No";
            /**
             * Default Query for User to Object Relationships
             */
            $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                MERGE (u)-[p:HAS_RELATIONSHIPS]->(o)
                SET p.$permissionType = '$permissionState'";
            if ($objectName !== null) {
                $objectPrefix = "{$schemaPrefix}{$objectName}.";
                $objectArray = [
                    'objectName' => $objectName,
                    'objectType' => $objectType,
                    'objectSurname' => rtrim($objectPrefix, ".")
                ];
                /**
                 * Create Object to Schema Relationships with index on Object of name and surname
                 */
                $schemaToObjArray = array_merge($schemaArray, $objectArray);
                $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                    MERGE (sc:Schema {name: {schema}, surname:{schemaSurname}}) 
                    MERGE (o)<-[y:HAS_RELATIONSHIPS]-(sc)
                    SET y.OWNS = 'Yes'", $schemaToObjArray);
                $stack->push("CREATE INDEX ON :Object(name, surname)");
                if ($columnName !== null) {
                    $columnArray = [
                        'columnName' => $columnName,
                        'columnSurname' => "{$objectPrefix}{$columnName}"
                    ];
                    $objToColumnArray = array_merge($objectArray, $columnArray);
                    /**
                     * Create Object to Column Relationships with index on name and surname
                     */
                    $stack->push("MERGE (o:Object {name: {objectName}, type: {objectType}, surname:{objectSurname}}) 
                        MERGE (c:Column {name: {columnName}, surname:{columnSurname}}) 
                        MERGE (o)-[y:HAS_RELATIONSHIPS]->(c)
                        SET y.COLUMN = 'Yes'", $objToColumnArray);
                    $stack->push("CREATE INDEX ON :Column(name, surname)");
                    /**
                     * Create column to user relationships.
                     * Override object array with column array.
                     */
                    $objectArray = $columnArray;
                    $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                        MERGE (o:Column {name: {columnName}, surname:{columnSurname}}) 
                        MERGE (u)-[p:HAS_RELATIONSHIPS]->(o)
                        SET p.$permissionType = '$permissionState'";
                }
            } else {
                switch ($objectType) {
                    case "DATABASE":
                        $objectArray = [
                            'objectName' => $databaseName,
                            'objectType' => $objectType,
                            'objectSurname' => rtrim($databasePrefix, ".")
                        ];
                        /**
                         * User to Databse Relationships Query
                         */
                        $query = "MERGE (u:User {name: {databaseUserName}, type: {userType}, surname: {databaseUserSurname}}) 
                            MERGE (o:Database {name: {objectName}, surname:{objectSurname}}) 
                            MERGE (u)-[p:HAS_RELATIONSHIPS]->(o)
                            SET p.$permissionType = '$permissionState'";
                    break;
                    default:
                        $objectPrefix = "{$schemaPrefix}{$objectType}.";
                        $objectArray = [
                            'objectName' => $objectType,
                            'objectType' => $objectType,
                            'objectSurname' => rtrim($objectPrefix, ".")
                        ];
                }
            }
            /**
             * Create User to Object Relationships with index on Object of surname and name
             */
            $userToPermToObjArray = array_merge($dbUserArray, $objectArray);
            $stack->push($query, $userToPermToObjArray);
            $stack->push("CREATE INDEX ON :Object(name, surname)");
        }
    }
}
$neo4j->runStack($stack);
// $endProcess = microtime(true);
// dump("The execution time for processing:", $endProcess-$startProcess-($endExtract-$startExtract));
dd("SUCCEDED");