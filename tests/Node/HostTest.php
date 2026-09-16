<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Node;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class HostTest extends TestCase
{
    private function createHost(): Host
    {
        return new Host(new FixtureServerQuery());
    }

    public function testLoadsAndFiltersServerList(): void
    {
        $host = $this->createHost();
        $servers = $host->serverList(['virtualserver_status' => 'online']);

        $this->assertCount(1, $servers);
        $this->assertInstanceOf(Server::class, $servers[1]);
        $this->assertSame('Main Server', $host->serverGetByName('Main Server')->toString());
        $this->assertSame('offline-uid', $host->serverGetByUid('offline-uid')->getProperty('virtualserver_unique_identifier')->toString());
        $this->assertSame(9987, $host->serverGetPortById(1));
    }

    public function testCachesVersionAndWhoamiValues(): void
    {
        $host = $this->createHost();
        $this->assertSame('3.13.7', $host->version('version')->toString());
        $this->assertSame('Linux', $host->version('platform')->toString());
        $this->assertSame(1, $host->serverSelectedId());
        $this->assertSame(9987, $host->serverSelectedPort());

        $host->whoamiSet('client_nickname', 'tester');
        $this->assertSame('tester', $host->whoamiGet('client_nickname')->toString());
        $this->assertSame('fallback', $host->whoamiGet('missing', 'fallback'));
    }

    public function testServerLookupReportsUnknownNames(): void
    {
        $this->expectException(ServerQueryException::class);
        $this->expectExceptionCode(0x400);
        $this->createHost()->serverGetByName('missing');
    }

    public function testServerStatusAndIdentityHelpers(): void
    {
        $server = new Server($this->createHost(), [
            'virtualserver_id' => 7, 'virtualserver_name' => 'Offline', 'virtualserver_status' => 'offline',
            'virtualserver_clientsonline' => 5, 'virtualserver_queryclientsonline' => 1, 'virtualserver_maxclients' => 10,
            'virtualserver_flag_password' => 0,
        ]);

        $this->assertTrue($server->isOffline());
        $this->assertSame(0, $server->clientCount());
        $this->assertSame('ts3_h_s7', $server->getUniqueId());
        $this->assertSame('server_open', $server->getIcon());
        $this->assertSame('$', $server->getSymbol());
    }
}

class FixtureServerQuery extends ServerQuery
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public function request(string $cmd, bool $throw = true): Reply
    {
        $response = match (explode(' ', $cmd)[0]) {
            'version' => 'version=3.13.7 platform=Linux',
            'whoami' => 'virtualserver_id=1 virtualserver_port=9987 client_nickname=serveradmin',
            'serverlist' => 'virtualserver_id=1 virtualserver_port=9987 virtualserver_name=Main\\sServer virtualserver_status=online virtualserver_unique_identifier=main-uid|virtualserver_id=2 virtualserver_port=9988 virtualserver_name=Offline virtualserver_status=offline virtualserver_unique_identifier=offline-uid',
            default => '',
        };

        return new Reply([new StringHelper($response), new StringHelper('error id=0 msg=ok')], $cmd, null, $throw);
    }

    public function prepare(string $cmd, array $params = []): string
    {
        return $cmd;
    }
}
