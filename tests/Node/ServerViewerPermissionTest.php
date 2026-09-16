<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Node;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Viewer\Text;

class ServerViewerPermissionTest extends TestCase
{
    public function testViewerSkipsChannelsTheQueryCannotSubscribeTo(): void
    {
        $server = new PermissionAwareServer(new Host(new HostTestFixtureQuery()), [
            'virtualserver_id' => 1,
            'virtualserver_name' => 'Server',
        ]);
        $server->setChannels([
            new PermissionAwareChannel($server, ['cid' => 1, 'pid' => 0, 'channel_name' => 'Visible'], false),
            new PermissionAwareChannel($server, ['cid' => 2, 'pid' => 0, 'channel_name' => 'Restricted'], true),
        ]);

        $viewer = $server->getViewer(new Text());

        $this->assertStringContainsString('Visible', $viewer);
        $this->assertStringNotContainsString('Restricted', $viewer);
    }

    public function testViewerDoesNotHideUnexpectedChannelErrors(): void
    {
        $server = new PermissionAwareServer(new Host(new HostTestFixtureQuery()), [
            'virtualserver_id' => 1,
            'virtualserver_name' => 'Server',
        ]);
        $server->setChannels([
            new PermissionAwareChannel($server, ['cid' => 1, 'pid' => 0, 'channel_name' => 'Broken'], false, 0x300),
        ]);

        $this->expectException(ServerQueryException::class);
        $this->expectExceptionCode(0x300);
        $server->getViewer(new Text());
    }
}

class HostTestFixtureQuery extends \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }
}

class PermissionAwareServer extends Server
{
    private array $channels = [];

    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }

    public function channelList(array $filter = []): array
    {
        return $this->channels;
    }
}

class PermissionAwareChannel extends Channel
{
    public function __construct(Server $server, array $info, private bool $restricted, private ?int $errorCode = null)
    {
        parent::__construct($server, $info);
    }

    public function count(): int
    {
        if ($this->errorCode !== null) {
            throw new ServerQueryException('unexpected channel failure', $this->errorCode);
        }

        if ($this->restricted) {
            throw new ServerQueryException('insufficient client permissions', 0xA08);
        }

        return 0;
    }
}
