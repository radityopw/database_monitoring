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
use GraphAware\Neo4j\Client\Exception\Neo4jExceptionInterface;
use GraphAware\Neo4j\Client\Neo4jClientEvents;

/**
 * Class BuildWithEventListenersIntegrationTest.
 *
 * @group listener
 */
class BuildWithEventListenersIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return string
     */
    private function createBoltUrl()
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
        return $boltUrl;
    }

    public function testListenersAreRegistered()
    {
        $listener = new EventListener();
        $client = ClientBuilder::create()
            ->addConnection('default', $this->createBoltUrl())
            ->registerEventListener(Neo4jClientEvents::NEO4J_PRE_RUN, [$listener, 'onPreRun'])
            ->registerEventListener(Neo4jClientEvents::NEO4J_POST_RUN, [$listener, 'onPostRun'])
            ->registerEventListener(Neo4jClientEvents::NEO4J_ON_FAILURE, [$listener, 'onFailure'])
            ->build();

        $result = $client->run('MATCH (n) RETURN count(n)');
        $this->assertTrue($listener->hookedPreRun);
        $this->assertTrue($listener->hookedPostRun);
    }

    public function testFailureCanBeDisabled()
    {
        $listener = new EventListener();
        $client = ClientBuilder::create()
            ->addConnection('default', $this->createBoltUrl())
            ->registerEventListener(Neo4jClientEvents::NEO4J_ON_FAILURE, [$listener, 'onFailure'])
            ->build();

        $client->run('MATCH (n)');
        $this->assertInstanceOf(Neo4jExceptionInterface::class, $listener->e);
    }
}
