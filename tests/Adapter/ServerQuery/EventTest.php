<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Adapter\ServerQuery;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Event;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

class EventTest extends TestCase
{
    public function testParsesAndExposesEventData(): void
    {
        $event = new Event(new StringHelper('notifytextmessage clid=7 msg=Hello\\sWorld'));

        $this->assertSame('textmessage', $event->getType()->toString());
        $this->assertSame(7, $event['clid']);
        $this->assertSame('Hello World', $event->getData()['msg']->toString());
        $this->assertSame('clid=7 msg=Hello\\sWorld', $event->getMessage()->toString());
    }

    public function testRejectsInvalidEventFormat(): void
    {
        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage('invalid notification event format');

        new Event(new StringHelper('error id=0 msg=ok'));
    }

    public function testRejectsEventWithoutData(): void
    {
        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage('invalid notification event data');

        new Event(new StringHelper('notifytextmessage'));
    }

    public function testRejectsUnknownOffsets(): void
    {
        $event = new Event(new StringHelper('notifytextmessage clid=7'));

        $this->expectException(ServerQueryException::class);
        $this->expectExceptionMessage('invalid parameter');
        $event['missing'];
    }

    public function testEventsAreReadOnlyButCanForgetAnOffset(): void
    {
        $event = new Event(new StringHelper('notifytextmessage clid=7'));

        try {
            $event['clid'] = 8;
            $this->fail('Expected event mutation to fail.');
        } catch (NodeException) {
            $this->assertTrue(isset($event['clid']));
        }

        unset($event['clid']);
        $this->assertFalse(isset($event['clid']));
    }
}
