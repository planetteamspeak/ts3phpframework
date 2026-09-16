<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\MockServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;

class ServerQueryTest extends TestCase
{
    public const S_ERROR_OK = 'error id=0 msg=ok';

    /**
     * @throws AdapterException
     */
    protected function createMockServerQuery(): MockServerQuery
    {
        return new MockServerQuery(['host' => '0.0.0.0', 'port' => 9987]);
    }

    /**
     * @throws ServerQueryException
     */
    public function testRequestIllegalCharakterException()
    {
        $query = "Hello\nWorld\r";

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage(sprintf("illegal characters in command '%s'", $query));
        $serverQuery = $this->createMockServerQuery();
        $serverQuery->request($query);
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function testLogin()
    {
        $query = "login serveradmin secret";
        $serverQuery = $this->createMockServerQuery();
        $reply = $serverQuery->request($query);
        $this->assertEquals("ok", $reply->getErrorProperty('msg')->toString());

        $query = "login client_login_name=serveradmin client_login_password=secret";
        $serverQuery = $this->createMockServerQuery();
        $reply = $serverQuery->request($query);
        $this->assertEquals("ok", $reply->getErrorProperty('msg')->toString());
    }

    public function testPrepareEscapesArgumentsAndBuildsListCells(): void
    {
        $serverQuery = $this->createMockServerQuery();

        $this->assertSame(
            'command name=Hello\\sWorld enabled=1 disabled=0 first=one second=three|first=two',
            $serverQuery->prepare('command', [
                'name' => 'Hello World',
                'enabled' => true,
                'disabled' => false,
                'ignored' => null,
                'first' => ['one', 'two'],
                'second' => ['three', null],
            ])
        );
    }

    public function testRequestRejectsBlockedCommands(): void
    {
        $this->expectException(ServerQueryException::class);
        $this->expectExceptionCode(0x100);
        $this->expectExceptionMessage('command not found');

        $this->createMockServerQuery()->request('help');
    }

    public function testRequestTracksCountTimestampAndRuntime(): void
    {
        $serverQuery = $this->createMockServerQuery();
        $this->assertSame(0, $serverQuery->getQueryCount());
        $this->assertNull($serverQuery->getQueryLastTimestamp());

        $serverQuery->request('login serveradmin secret');

        $this->assertSame(1, $serverQuery->getQueryCount());
        $this->assertIsInt($serverQuery->getQueryLastTimestamp());
        $this->assertGreaterThanOrEqual(0, $serverQuery->getQueryRuntime());
        $this->assertSame('0.0.0.0', $serverQuery->getTransportHost());
        $this->assertSame('9987', $serverQuery->getTransportPort());
    }
}
