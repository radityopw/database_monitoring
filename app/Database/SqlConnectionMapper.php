<?php

namespace UserDep\Database;

use PDO;

class SqlConnectionMapper
{
    /**
     * The attribtue refers to connection of database
     *
     * @var PDO
     */
    protected $connection;

    /**
     * The attribute refers to result of query to database name
     *
     * @var array
     */
    protected $result;

    /**
     * The attribute refers to the query used to get the user mapping
     *
     * @var string
     */
    protected $query;

    /*
     * The attribute refer to database name
     *
     * @var string
     */
    protected $database;

    /**
     * Initialize class and assign variable
     *
     * @param string $db
     */
    public function __construct(string $db)
    {
        $this->connection = createSQLServerConnection($db);
        $this->query = config('query.sqlserver.extract_user');
        $this->queryResult();
        $this->database = $db;
    }

    /**
     * Assign result from query
     */
    private function queryResult()
    {
        $this->result = tap($this->connection->prepare($this->query))->execute()->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Returning result from protected property
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returning database name for this class
     * 
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }
}