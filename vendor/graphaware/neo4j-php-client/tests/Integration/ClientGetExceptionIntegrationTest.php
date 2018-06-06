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
use GraphAware\Neo4j\Client\Exception\Neo4jException;

class ClientGetExceptionIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionHandling()
    {
        $boltUrl = 'bolt://localhost';
        if (isset($_ENV['NEO4J_USER'])) {
            $boltUrl = sprintf(
                'bolt://%s:%s@%s',
                getenv('NEO4J_USER'),
                getenv('NEO4J_PASSWORD'),
                getenv('NEO4J_HOST')
            );
        }

        $client = ClientBuilder::create()
            ->addConnection('default', $boltUrl)
            ->build();

        $this->setExpectedException(Neo4jException::class);
        $result = $client->run('CREATE (n:Cool');
    }
}
