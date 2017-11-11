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

/* load framework library */
require_once("../libraries/TeamSpeak3/TeamSpeak3.php");

/* init framework */
TeamSpeak3::init();

/* grab user parameters from $_REQUEST array */
$ftdata = TeamSpeak3_Helper_Uri::getUserParam("ftdata");

try
{
  /* validate file transfer information */
  if(!$ftdata = unserialize(base64_decode($ftdata)))
  {
    throw new Exception("unable to decode file transfer data");
  }

  /* init file transfer and stream file to browser using fpassthru() */
  TeamSpeak3::factory("filetransfer://" . $ftdata["host"] . ":" . $ftdata["port"])->download($ftdata["ftkey"], $ftdata["size"], TRUE);
}
catch(Exception $e)
{
  /* send error icon content */
  echo file_get_contents("../images/viewer/group_icon_0.png");
}