<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Exception;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;

class TeamSpeak3ExceptionTest extends TestCase
{
    public function testKeepsRawErrorInformation(): void
    {
        $exception = new TeamSpeak3Exception('original message', 42);

        $this->assertSame(42, $exception->getRawCode());
        $this->assertSame('original message', $exception->getRawMessage());
        $this->assertNotSame('', $exception->getSender());
    }

    public function testCustomMessagesCanBeRegisteredAndRemoved(): void
    {
        TeamSpeak3Exception::registerCustomMessage(4242, 'Error %code: %mesg');
        $exception = new TeamSpeak3Exception('details', 4242);
        $this->assertSame('Error 4242: details', $exception->getMessage());
        TeamSpeak3Exception::unregisterCustomMessage(4242);

        $this->expectException(TeamSpeak3Exception::class);
        TeamSpeak3Exception::unregisterCustomMessage(4242);
    }
}
