<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests\Integration;

use GraphAware\Neo4j\Client\ClientBuilder;

class IntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \GraphAware\Neo4j\Client\Client
     */
    protected $client;

    public function setUp()
    {
        $httpUri = 'http://localhost:7474';
        if (isset($_ENV['NEO4J_USER'])) {
            $httpUri = sprintf(
                '%s://%s:%s@%s:%s',
                getenv('NEO4J_SCHEMA'),
                getenv('NEO4J_USER'),
                getenv('NEO4J_PASSWORD'),
                getenv('NEO4J_HOST'),
                getenv('NEO4J_PORT')
            );
        }

        $boltUrl = 'bolt://localhost';
        if (isset($_ENV['NEO4J_USER'])) {
            $boltUrl = sprintf(
                'bolt://%s:%s@%s',
                getenv('NEO4J_USER'),
                getenv('NEO4J_PASSWORD'),
                getenv('NEO4J_HOST')
            );
        }

        $this->client = ClientBuilder::create()
            ->addConnection('http', $httpUri)
            ->addConnection('bolt', $boltUrl)
            ->build();
    }

    /**
     * Empties the graph database.
     *
     * @void
     */
    public function emptyDb()
    {
        $this->client->run('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n', null, null);
    }
}
