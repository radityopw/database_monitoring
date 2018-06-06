<?php

namespace GraphAware\Neo4j\Client\Tests;

/**
 * Class DrupalIssueTest
 * @package GraphAware\Neo4j\Client\Tests
 *
 * @group drupal
 */
class DrupalIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testDrupalConversion()
    {
        $this->addConnection('default', 'bolt://neo4j:sfadfewfn;kewvljnfd@ssl+graphene.com', null);
    }

    private function addConnection($alias, $uri, $config)
    {
        if (substr($uri, 0, 7) === 'bolt://') {
            $parts = explode('bolt://', $uri );
            if (count($parts) === 2) {
                $splits = explode('@', $parts[1]);
                $split = $splits[count($splits)-1];
                if (substr($split, 0, 4) === 'ssl+') {
                    $up = count($splits) > 1 ? $splits[0] : '';
                    $ups = explode(':', $up);
                    $u = $ups[0];
                    $p = $ups[1];
                    $uri = 'bolt://'.str_replace('ssl+', '', $split);
                    $config = \GraphAware\Bolt\Configuration::newInstance()
                        ->withCredentials($u, $p)
                        ->withTLSMode(\GraphAware\Bolt\Configuration::TLSMODE_REQUIRED);
                }
            }
        }

        var_dump($uri);
        var_dump($config);
    }
}