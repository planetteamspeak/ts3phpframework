<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework Example :: console
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   console
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/* set error reporting levels */
error_reporting(E_ALL | E_STRICT);

/* set default timezone */
date_default_timezone_set("Europe/Berlin");

/* deny browser access */
if(php_sapi_name() != "cli")
{
  die("ERROR, access denied");
}

/* load required files */
require_once("../../config.php");

/* load framework library */
require_once("../../../libraries/TeamSpeak3/TeamSpeak3.php");

/* display library version */
echo "TeamSpeak 3 Framework Console Test Script\n" .
"\n" .
"PHP Version: " . phpversion() . "\n" .
"LIB Version: " . TeamSpeak3::LIB_VERSION . "\n" .
"\n";

/* initialize */
TeamSpeak3::init();

try
{
  /* subscribe to various events */
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryConnected", "onConnect");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryCommandStarted", "onCommand");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", "onTimeout");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyLogin", "onLogin");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyEvent", "onEvent");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "onTextmessage");
  TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyServerselected", "onSelect");

  /* connect to server, login and get TeamSpeak3_Node_Host object by URI */
  $ts3 = TeamSpeak3::factory("serverquery://" . $cfg["user"] . ":" . $cfg["pass"] . "@" . $cfg["host"] . ":" . $cfg["query"] . "/?server_port=" . $cfg["voice"] . "&blocking=0");

  /* register for all events available */
  $ts3->notifyRegister("server");
  $ts3->notifyRegister("channel");
  $ts3->notifyRegister("textserver");
  $ts3->notifyRegister("textchannel");
  $ts3->notifyRegister("textprivate");

  /* wait for events */
  while(1) $ts3->getAdapter()->wait();
}
catch(Exception $e)
{
  die("[ERROR]  " . $e->getMessage() . "\n");
}

// ================= [ BEGIN OF CALLBACK FUNCTION DEFINITIONS ] =================

/**
 * Callback method for 'serverqueryConnected' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery $adapter
 * @return void
 */
function onConnect(TeamSpeak3_Adapter_ServerQuery $adapter)
{
  echo "[SIGNAL] connected to TeamSpeak 3 Server on " . $adapter->getHost() . "\n";

  $version = $adapter->getHost()->version();

  echo "[INFO]   server is running with version " . $version["version"] . " on " . $version["platform"] . "\n";
}

/**
 * Callback method for 'serverqueryCommandStarted' signals.
 *
 * @param  string $cmd
 * @return void
 */
function onCommand($cmd)
{
  echo "[SIGNAL] starting command " . $cmd . "\n";
}

/**
 * Callback method for 'serverqueryWaitTimeout' signals.
 *
 * @param  integer $seconds
 * @return void
 */
function onTimeout($seconds, TeamSpeak3_Adapter_ServerQuery $adapter)
{
  echo "[SIGNAL] no reply from the server for " . $seconds . " seconds\n";

  if($adapter->getQueryLastTimestamp() < time()-300)
  {
    echo "[INFO]   sending keep-alive command\n";

    $adapter->request("clientupdate");
  }
}

/**
 * Callback method for 'notifyLogin' signals.
 *
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onLogin(TeamSpeak3_Node_Host $host)
{
  echo "[SIGNAL] authenticated as user " . $host->whoamiGet("client_login_name") . "\n";
}

/**
 * Callback method for 'notifyEvent' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onEvent(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
  echo "[SIGNAL] received notification " . $event->getType() . "\n";
}

/**
 * Callback method for 'notifyTextmessage' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onTextmessage(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
  echo "[SIGNAL] client " . $event["invokername"] . " sent textmessage: " . $event["msg"] . "\n";
}

/**
 * Callback method for 'notifyServerselected' signals.
 *
 * @param  string $cmd
 * @return void
 */
function onSelect(TeamSpeak3_Node_Host $host)
{
  echo "[SIGNAL] selected virtual server with ID " . $host->serverSelectedId() . "\n";
}
