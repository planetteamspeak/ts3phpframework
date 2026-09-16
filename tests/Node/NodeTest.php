<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Node;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Viewer\Text;

class NodeTest extends TestCase
{
    public function testExposesPropertiesConversionsAndIconMetadata(): void
    {
        $node = new TestNode(7, [
            'name' => 'root', 'traffic_bytes_sent' => 1024, 'connection_packets_sent' => 1234,
            'connection_packetloss_total' => '0.125', 'virtualserver_uptime' => 90,
            'virtualserver_version' => '3.0.13.6 [Build: 1478594913]', 'client_icon_id' => -1,
        ]);

        $info = $node->getInfo(false, true);
        $this->assertSame('1 KiB', $info['traffic_bytes_sent']);
        $this->assertSame('1.234', $info['connection_packets_sent']);
        $this->assertSame('12.50%', $info['connection_packetloss_total']);
        $this->assertSame('0D 00:01:30', $info['virtualserver_uptime']);
        $this->assertSame('4294967295', $info['client_icon_id']->toString());
        $this->assertTrue($node->iconIsLocal('client_icon_id') === false);
        $this->assertSame('/icon_4294967295', $node->iconGetName('client_icon_id')->toString());
    }

    public function testImplementsArrayAccessAndRecursiveIteration(): void
    {
        $child = new TestNode(2, ['name' => 'child']);
        $node = new TestNode(1, ['name' => 'root'], [$child]);

        $this->assertSame('root', $node['name']);
        $this->assertSame(1, $node->count());
        $this->assertTrue($node->valid());
        $this->assertSame(0, $node->key());
        $this->assertSame($child, $node->current());
        $this->assertTrue($node->hasChildren() === false);
        $this->assertFalse($node->hasNext());
        $node->next();
        $this->assertFalse($node->valid());
        $node->rewind();
        unset($node['name']);
        $this->assertFalse(isset($node['name']));
    }

    public function testRejectsMissingPropertiesAndReadOnlyMutation(): void
    {
        $node = new TestNode(1, []);

        $this->expectException(NodeException::class);
        $node['missing'];
    }

    public function testRendersItsTreeWithTextViewer(): void
    {
        $node = new TestNode(1, ['name' => 'root'], [new TestNode(2, ['name' => 'child'])]);

        $this->assertSame("* root\n\\-* child\n", $node->getViewer(new Text()));
    }

    public function testFilterExcludesNodesWithoutTheRequestedProperty(): void
    {
        $node = new TestNode(1, ['name' => 'root']);

        $this->assertSame([], $node->filterForTest([$node], ['missing' => 'value']));
    }
}

class TestNode extends Node
{
    public function __construct(int $id, array $info, array $children = [])
    {
        $this->nodeId = $id;
        $this->nodeInfo = $info;
        $this->nodeList = $children;
    }

    public function getUniqueId(): string
    {
        return 'test_' . $this->nodeId;
    }

    public function getIcon(): string
    {
        return 'test';
    }

    public function getSymbol(): string
    {
        return '*';
    }

    public function __toString(): string
    {
        return $this->nodeInfo['name'] ?? 'unnamed';
    }

    public function filterForTest(array $nodes, array $rules): array
    {
        return $this->filterList($nodes, $rules);
    }
}
