<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework Example
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
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/* set error reporting levels */
error_reporting(E_ALL | E_STRICT);

/* set default timezone */
date_default_timezone_set("Europe/Berlin");

/* load required files */
require_once("config.php");
require_once("globals.php");

/* load framework library */
require_once("../libraries/TeamSpeak3/TeamSpeak3.php");

/* init framework */
TeamSpeak3::init();

/* grab user parameters from $_REQUEST array */
$sid  = TeamSpeak3_Helper_Uri::getUserParam("sid", 0);
$cid  = TeamSpeak3_Helper_Uri::getUserParam("cid", 0);
$file = urldecode(TeamSpeak3_Helper_Uri::getUserParam("file"));

try
{
  /* connect to server, login and get TeamSpeak3_Node_Host object by URI */
  $ts3_ServerInstance = TeamSpeak3::factory("serverquery://" . $cfg["user"] . ":" . $cfg["pass"] . "@" . $cfg["host"] . ":" . $cfg["query"] . "/");

  /* get TeamSpeak3_Node_Server object by ID */
  $ts3_VirtualServer = $ts3_ServerInstance->serverGetById($sid);

  /* init file transfer and establish connection */
  $download = $ts3_VirtualServer->transferInitDownload($sid, $cid, $file);
  $transfer = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"]);

  /* send custom headers */
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0");
  header("Content-Type: application/octet-stream");
  header("Content-Length: " . $download["size"]);
  header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
  header("Content-Transfer-Encoding: binary");

  /* send file to browser */
  $transfer->download($download["ftkey"], $download["size"], TRUE);
}
catch(Exception $e)
{
  die("ERROR, " . $e->getMessage());
}