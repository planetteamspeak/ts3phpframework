<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Node;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class ServerTest extends TestCase
{
    public function testServerGroupListSortsStringHelperSortIds(): void
    {
        $server = new class () extends Server {
            public function __construct()
            {
            }

            public function request(string $cmd, bool $throw = true): Reply
            {
                return new Reply([
                    new StringHelper("sgid=1 name=First type=1 iconid=0 sortid=invalid"),
                    new StringHelper("sgid=2 name=Second type=1 iconid=0 sortid=2"),
                    new StringHelper("error id=0 msg=ok"),
                ], $cmd);
            }
        };

        $groups = $server->serverGroupList();

        $this->assertSame([1, 2], array_keys($groups));
    }
}
