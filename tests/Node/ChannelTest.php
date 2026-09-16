<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Node;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Client;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class ChannelTest extends TestCase
{
    public function testClientGetByIdReturnsClientFromChannelList(): void
    {
        $server = $this->createMock(Server::class);
        $channel = new Channel($server, ['cid' => 1]);
        $client = new Client($server, ['clid' => 2, 'cid' => 1]);

        $server->method('clientList')->willReturn([2 => $client]);

        $this->assertSame($client, $channel->clientGetById(2));
    }

    public function testSubChannelGetByIdReturnsChildFromChannelList(): void
    {
        $server = $this->createMock(Server::class);
        $parent = new Channel($server, ['cid' => 1]);
        $child = new Channel($server, ['cid' => 2, 'pid' => 1]);
        $server->method('channelList')->willReturn([2 => $child]);

        $this->assertSame($child, $parent->subChannelGetById(2));
    }
}
