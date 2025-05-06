<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Transport;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\MockServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport;

class TransportTest extends TestCase
{
    /**
     * @throws AdapterException
     */
    protected function createMockServerQuery(): MockServerQuery
    {
        return new MockServerQuery(['host' => '0.0.0.0', 'port' => 9987]);
    }

    /**
     * @throws TransportException
     */
    public function testGetAdapterTypeReturnValue()
    {
        $mockServerQuery = $this->createMockServerQuery();

        // The original value should be returned as it is
        $this->assertEquals("MockServerQuery", $mockServerQuery->getTransport()->getAdapterType());

        // The Signal class combines the lowered class name with an additional string for the `emit()` function
        $this->assertEquals("mockserverquery", strtolower($mockServerQuery->getTransport()->getAdapterType()));
    }
}
