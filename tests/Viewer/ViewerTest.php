<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Viewer;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\MockServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Viewer\Html;
use PlanetTeamSpeak\TeamSpeak3Framework\Viewer\Json;

class ViewerTest extends TestCase
{
    private function createServer(): Server
    {
        $host = new Host(new MockServerQuery(['host' => '127.0.0.1', 'port' => 10011]));

        return new ViewerServer($host, [
            'virtualserver_id' => 1,
            'virtualserver_name' => 'Example <Server>',
            'virtualserver_status' => 'online',
            'virtualserver_clientsonline' => 4,
            'virtualserver_queryclientsonline' => 1,
            'virtualserver_maxclients' => 32,
            'virtualserver_uptime' => 90,
            'virtualserver_icon_id' => 100,
            'virtualserver_welcomemessage' => ' Welcome ',
            'virtualserver_hostmessage' => '',
            'virtualserver_version' => '3.0.13.6 [Build: 1478594913]',
            'virtualserver_platform' => new \PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper('Linux'),
            'virtualserver_flag_password' => 1,
            'virtualserver_autostart' => 1,
            'virtualserver_weblist_enabled' => 1,
            'virtualserver_ask_for_privilegekey' => 1,
        ]);
    }

    public function testHtmlViewerRendersEscapedServerInformationAndIcons(): void
    {
        $html = (new Html('icons/'))->fetchObject($this->createServer());

        $this->assertStringContainsString("id='ts3_h_s1'", $html);
        $this->assertStringContainsString('Example &lt;Server&gt;', $html);
        $this->assertStringContainsString("icons/server_pass.png", $html);
        $this->assertStringContainsString("icons/group_icon_100.png", $html);
        $this->assertStringContainsString('Clients: 3/32', $html);
    }

    public function testHtmlImageAttributesAreEscaped(): void
    {
        $viewer = new HtmlForTest("icons/'onerror='alert(1)");

        $html = $viewer->getImageForTest("image.png' onerror='alert(2)", "title' onmouseover='alert(3)");

        $this->assertStringNotContainsString("' onerror=", $html);
        $this->assertStringNotContainsString("' onmouseover=", $html);
        $this->assertStringContainsString('&#039;', $html);
    }

    public function testJsonViewerProducesStructuredServerData(): void
    {
        $data = [];
        $viewer = new Json($data);
        $this->assertSame('', $viewer->fetchObject($this->createServer()));

        $this->assertCount(1, $data);
        $this->assertSame('ts3_s1', $data[0]->ident);
        $this->assertSame('server', $data[0]->class);
        $this->assertSame('Example <Server>', $data[0]->name);
        $this->assertSame(31, $data[0]->props->flags);
        $this->assertSame('Welcome', $data[0]->props->welcmsg);
        $this->assertSame('Linux', $data[0]->props->platform);
        $this->assertSame('server-pass', $data[0]->image);
        $this->assertJsonStringEqualsJsonString(json_encode($data), $viewer->toString());
    }

    public function testViewersRenderChannelStateAndMetadata(): void
    {
        $channel = new ViewerChannel($this->createServer(), [
            'cid' => 2, 'pid' => 0, 'channel_name' => 'Music', 'channel_topic' => ' Topic ',
            'channel_codec' => 5, 'channel_codec_quality' => 10, 'channel_icon_id' => 200,
            'channel_maxclients' => 10, 'channel_maxfamilyclients' => -1,
            'total_clients' => 2, 'total_clients_family' => -1,
            'channel_flag_default' => 1, 'channel_flag_password' => 1,
            'channel_flag_permanent' => 1, 'channel_flag_semi_permanent' => 0,
            'channel_needed_talk_power' => 5,
        ]);

        $html = (new Html('icons/'))->fetchObject($channel, [true]);
        $this->assertStringContainsString("id='ts3_h_s1_ch2'", $html);
        $this->assertStringContainsString('channel_flag_music.png', $html);
        $this->assertStringContainsString('channel_flag_moderated.png', $html);

        $data = [];
        (new Json($data))->fetchObject($channel, [true]);
        $this->assertSame('ts3_c2', $data[0]->ident);
        $this->assertSame('ts3_s1', $data[0]->parent);
        $this->assertSame('Music', $data[0]->name);
        $this->assertSame('Music', $data[0]->props->path);
        $this->assertSame('Topic', $data[0]->props->topic);
        $this->assertSame(119, $data[0]->props->flags);
    }
}

class ViewerServer extends Server
{
    public function count(): int
    {
        return 0;
    }

    public function clientCount(): int
    {
        return 3;
    }

    public function channelGetLevel(int $cid): int
    {
        return 0;
    }

    public function channelGetPathway(int $cid): string
    {
        return 'Music';
    }

    public function channelIsSpacer(Channel $channel): bool
    {
        return false;
    }
}

class ViewerChannel extends Channel
{
    public function count(): int
    {
        return 0;
    }
}

class HtmlForTest extends Html
{
    public function getImageForTest(string $name, string $text): string
    {
        return $this->getImage($name, $text);
    }
}
