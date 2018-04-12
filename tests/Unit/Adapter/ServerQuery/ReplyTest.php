<?php

namespace Tests\Unit\Adapter\ServerQuery;

use \PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;

require_once 'libraries/TeamSpeak3/Adapter/ServerQuery/Reply.php';

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
  
  private static $S_WELCOME_L0 = 'TS3';

  private static $S_WELCOME_L1 = 'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands and "help <command>" for information on a specific command.';
  
  private static $S_ERROR_OK = 'error id=0 msg=ok';
  
  // Default virtual server
  // Response from `serverlist` command on default virtual server
  private static $S_SERVERLIST = 'virtualserver_id=1 virtualserver_port=9987 virtualserver_status=online virtualserver_clientsonline=1 virtualserver_queryclientsonline=1 virtualserver_maxclients=32 virtualserver_uptime=5470 virtualserver_name=TeamSpeak\s]I[\sServer virtualserver_autostart=1 virtualserver_machine_id';
  
  // Expected string output after parsing for `serverlist` command.
  private static $E_SERVERLIST = 'virtualserver_id=1 virtualserver_port=9987 virtualserver_status=online virtualserver_clientsonline=1 virtualserver_queryclientsonline=1 virtualserver_maxclients=32 virtualserver_uptime=5470 virtualserver_name=TeamSpeak ]I[ Server virtualserver_autostart=1 virtualserver_machine_id';
  
  // 3 users connected
  private static $S_CLIENTLIST = 'clid=1 cid=1 client_database_id=1 client_nickname=serveradmin\sfrom\s[::1]:59642 client_type=1|clid=2 cid=1 client_database_id=3 client_nickname=Unknown\sfrom\s[::1]:59762 client_type=1|clid=3 cid=1 client_database_id=3 client_nickname=Unknown\sfrom\s[::1]:59766 client_type=1';
  
  private static $S_CHANNELLIST = 'cid=1 pid=0 channel_order=0 channel_name=Default\sChannel total_clients=3 channel_needed_subscribe_power=0|cid=2 pid=1 channel_order=0 channel_name=Test\sParent\s1 total_clients=0 channel_needed_subscribe_power=0|cid=3 pid=1 channel_order=2 channel_name=Test\sParent\s2 total_clients=0 channel_needed_subscribe_power=0|cid=5 pid=3 channel_order=0 channel_name=P2\s-\sSub\s1 total_clients=0 channel_needed_subscribe_power=0|cid=6 pid=3 channel_order=5 channel_name=P2\s-\sSub\s2 total_clients=0 channel_needed_subscribe_power=0|cid=4 pid=1 channel_order=3 channel_name=Test\sParent\s3 total_clients=0 channel_needed_subscribe_power=0|cid=7 pid=4 channel_order=0 channel_name=P3\s-\sSub\s1 total_clients=0 channel_needed_subscribe_power=0|cid=8 pid=4 channel_order=7 channel_name=P3\s-\sSub\s2 total_clients=0 channel_needed_subscribe_power=0';
  
  public function testConstructor() {
    $reply = new \TeamSpeak3_Adapter_ServerQuery_Reply([
      new \TeamSpeak3_Helper_String(static::$S_SERVERLIST),
      new \TeamSpeak3_Helper_String(static::$S_ERROR_OK)
    ]);
  }
  
  public function testToString() {
    $reply = new \TeamSpeak3_Adapter_ServerQuery_Reply([
      new \TeamSpeak3_Helper_String(static::$S_SERVERLIST),
      new \TeamSpeak3_Helper_String(static::$S_ERROR_OK)
    ]);
    $this->assertEquals(static::$E_SERVERLIST, (string)$reply->toString());
  }
  
  public function testToLines() {
  }
  public function testToTable() {
  }
  public function testToArray() {
  }
  public function testToAssocArray() {
  }
  public function testToList() {
  }
  public function testToObjectArray() {
  }
  public function testGetCommandString() {
  }
  public function testGetNotifyEvents() {
  }
  public function testGetErrorProperty() {
  }
  public function testFetchError() {
    //$this->assertInstanceOf(\TeamSpeak3_Adapter_ServerQuery_Reply::class, $reply);
    //$this->assertInternalType(PHPUnit_IsType::TYPE_INT, $reply->getErrorProperty('id'));
    //$this->assertEquals(0, $reply->getErrorProperty('id'));
    //$this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $reply->getErrorProperty('msg'));
    //$this->assertEquals('ok', $reply->getErrorProperty('msg'));
  }
  public function testFetchReply() {
  }
}