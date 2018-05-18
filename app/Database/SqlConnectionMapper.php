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

    /**
     * Initialize class and assign variable
     *
     * @param string $db
     */
    public function __construct(string $db)
    {
        $this->connection = createSQLServerConnection($db);
        $this->query = config('query.sqlserver.extract');
        $this->queryResult();
    }

    /**
     * Assign result from query
     */
    private function queryResult()
    {
        $result = tap($this->connection->prepare($this->query))->execute();
        $this->result = $result->fetchAll(PDO::FETCH_OBJ);
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
}