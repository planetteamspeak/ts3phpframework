<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class TeamSpeak3Test extends TestCase
{
    public function testFactoryCreatesMockServerQueryHost(): void
    {
        $node = TeamSpeak3::factory('mockserverquery://127.0.0.1:10011/');

        $this->assertInstanceOf(Host::class, $node);
        $this->assertSame('127.0.0.1', $node->getParent()->getTransportHost());
        $this->assertSame('10011', $node->getParent()->getTransportPort());
    }

    public function testEscapePatternsAndDumpAreAvailable(): void
    {
        $patterns = TeamSpeak3::getEscapePatterns();
        $this->assertArrayHasKey(' ', $patterns);
        $this->assertSame('\\s', $patterns[' ']);

        $dump = TeamSpeak3::dump(['value' => '<tag>'], false);
        $this->assertStringContainsString("array(1)", $dump);
        $this->assertStringContainsString("<tag>", $dump);
    }

    public function testTransferClientIdIsWithinProtocolRange(): void
    {
        $id = TeamSpeak3::generateTransferClientId();

        $this->assertGreaterThanOrEqual(0, $id);
        $this->assertLessThanOrEqual(0xFFFF, $id);
    }
}
