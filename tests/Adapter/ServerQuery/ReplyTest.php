<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Adapter\ServerQuery;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

/**
 * Class ReplyTest
 *
 * Constants: S_... - Sample response for a command (raw formatting) from server.
 *            E_... - Expected (parsed) response (i.e. from _Helper_String) from framework
 *
 * @package Tests\Unit\Adapter\ServerQuery
 */
class ReplyTest extends TestCase
{
    private static string $S_WELCOME_L0 = 'TS3';

    private static string $S_WELCOME_L1 = 'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands and "help <command>" for information on a specific command.';

    private static string $S_ERROR_OK = 'error id=0 msg=ok';

    // Default virtual server
    // Response from `serverlist` command on default virtual server
    private static string $S_SERVERLIST = 'virtualserver_id=1 virtualserver_port=9987 virtualserver_status=online virtualserver_clientsonline=1 virtualserver_queryclientsonline=1 virtualserver_maxclients=32 virtualserver_uptime=5470 virtualserver_name=TeamSpeak\s]I[\sServer virtualserver_autostart=1 virtualserver_machine_id';

    // Expected string output after parsing for `serverlist` command.
    private static string $E_SERVERLIST = 'virtualserver_id=1 virtualserver_port=9987 virtualserver_status=online virtualserver_clientsonline=1 virtualserver_queryclientsonline=1 virtualserver_maxclients=32 virtualserver_uptime=5470 virtualserver_name=TeamSpeak ]I[ Server virtualserver_autostart=1 virtualserver_machine_id';

    // 3 users connected
    private static string $S_CLIENTLIST = 'clid=1 cid=1 client_database_id=1 client_nickname=serveradmin\sfrom\s[::1]:59642 client_type=1|clid=2 cid=1 client_database_id=3 client_nickname=Unknown\sfrom\s[::1]:59762 client_type=1|clid=3 cid=1 client_database_id=3 client_nickname=Unknown\sfrom\s[::1]:59766 client_type=1';

    private static string $S_CHANNELLIST = 'cid=1 pid=0 channel_order=0 channel_name=Default\sChannel total_clients=3 channel_needed_subscribe_power=0|cid=2 pid=1 channel_order=0 channel_name=Test\sParent\s1 total_clients=0 channel_needed_subscribe_power=0|cid=3 pid=1 channel_order=2 channel_name=Test\sParent\s2 total_clients=0 channel_needed_subscribe_power=0|cid=5 pid=3 channel_order=0 channel_name=P2\s-\sSub\s1 total_clients=0 channel_needed_subscribe_power=0|cid=6 pid=3 channel_order=5 channel_name=P2\s-\sSub\s2 total_clients=0 channel_needed_subscribe_power=0|cid=4 pid=1 channel_order=3 channel_name=Test\sParent\s3 total_clients=0 channel_needed_subscribe_power=0|cid=7 pid=4 channel_order=0 channel_name=P3\s-\sSub\s1 total_clients=0 channel_needed_subscribe_power=0|cid=8 pid=4 channel_order=7 channel_name=P3\s-\sSub\s2 total_clients=0 channel_needed_subscribe_power=0';

    // `clientlist` command with all parameters (single client)
    private static string $S_CLIENTLIST_EXTENDED_SINGLE = "clid=63 cid=53 client_database_id=25 client_nickname=HouseMaister-Radio\sBob\s(Rock) client_type=0 client_away=0 client_away_message client_flag_talking=0 client_input_muted=0 client_output_muted=0 client_input_hardware=1 client_output_hardware=1 client_talk_power=70 client_is_talker=0 client_is_priority_speaker=0 client_is_recording=0 client_is_channel_commander=0 client_unique_identifier=7seVjHY4Hbe2cSxTjyv8es7wv54= client_servergroups=147,227 client_channel_group_id=13 client_channel_group_inherited_channel_id=53 client_version=3.5.6\s[Build:\s1606312422] client_platform=Linux client_idle_time=102752 client_created=1684051977 client_lastconnected=1691241932 client_icon_id=0 client_country=DE connection_client_ip=fe80::9400:ff:fe2b::1 client_badges=Overwolf=1:badges=c2368518-3728-4260-bcd1-8b85e9f8984c";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLID = "63";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_NICKNAME = "HouseMaister-Radio\sBob\s(Rock)";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_AWAY_MESSAGE = "";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_FLAG_TALKING = "0";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_UNIQUE_IDENTIFIER = "7seVjHY4Hbe2cSxTjyv8es7wv54=";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_SERVERGROUPS = "147,227";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_VERSION = "3.5.6\s[Build:\s1606312422]";
    private static string $E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_BADGES = "Overwolf=1:badges=c2368518-3728-4260-bcd1-8b85e9f8984c";

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function testConstructor()
    {
      $reply = new Reply([
        new StringHelper(static::$S_SERVERLIST),
        new StringHelper(static::$S_ERROR_OK)
      ]);

      $this->assertInstanceOf(Reply::class, $reply);
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function testToString()
    {
      $reply = new Reply([
        new StringHelper(static::$S_SERVERLIST),
        new StringHelper(static::$S_ERROR_OK)
      ]);

      $this->assertEquals(static::$E_SERVERLIST, (string) $reply->toString());
    }

    public function testToLines()
    {
    }
    public function testToTable()
    {
    }

    /**
     * Tests the toArray() function
     */
    public function testToArray()
    {
      $reply = new Reply([
        new StringHelper(static::$S_CLIENTLIST_EXTENDED_SINGLE),
        new StringHelper(static::$S_ERROR_OK)
      ]);

      $clientlist_array = $reply->toArray('clid')[0];

      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLID, (string) $clientlist_array['clid']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_NICKNAME, (string) $clientlist_array['client_nickname']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_AWAY_MESSAGE, (string) $clientlist_array['client_away_message']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_FLAG_TALKING, (string) $clientlist_array['client_flag_talking']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_UNIQUE_IDENTIFIER, (string) $clientlist_array['client_unique_identifier']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_SERVERGROUPS, (string) $clientlist_array['client_servergroups']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_VERSION, (string) $clientlist_array['client_version']);
      $this->assertEquals(static::$E_CLIENTLIST_EXTENDED_SINGLE_CLIENT_BADGES, (string) $clientlist_array['client_badges']);
    }

    public function testToAssocArray()
    {
    }
    public function testToList()
    {
    }
    public function testToObjectArray()
    {
    }
    public function testGetCommandString()
    {
    }
    public function testGetNotifyEvents()
    {
    }
    public function testGetErrorProperty()
    {
    }
    public function testFetchError()
    {
        //$this->assertInstanceOf(\TeamSpeak3_Adapter_ServerQuery_Reply::class, $reply);
        //$this->assertInternalType(PHPUnit_IsType::TYPE_INT, $reply->getErrorProperty('id'));
        //$this->assertEquals(0, $reply->getErrorProperty('id'));
        //$this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $reply->getErrorProperty('msg'));
        //$this->assertEquals('ok', $reply->getErrorProperty('msg'));
    }
    public function testFetchReply()
    {
    }
}
