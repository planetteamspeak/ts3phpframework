<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework Example :: webinterface
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
 * @package   webinterface
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/* check if file is included correctly */
defined("TS3WA_VALID") || die("Access denied...");

/* set error reporting levels */
error_reporting(E_ALL | E_STRICT);

/* set default timezone */
date_default_timezone_set("Europe/Berlin");

/* modify include paths */
set_include_path(TS3WA_LIB . PS . get_include_path());

/* load framework library */
require_once(TS3WA_ROOT . DS . "../../../libraries/TeamSpeak3/TeamSpeak3.php");

/* load libraries */
require_once("application.class.php");
require_once("layout.class.php");
require_once("module.class.php");

try
{
  /* init application */
  $app = new TS3WA_Application();

  /* run */
  $app->run();
}
catch(Exception $e)
{
  /* kill session */
  session_destroy();

  /* die with a simple error message */
  die("<h2>FATAL ERROR " . $e->getCode() . "</h2>[" . basename($e->getFile()) . ":" . $e->getLine() . "] " . $e->getMessage() . "<pre>" . $e->getTraceAsString() . "</pre>");
}
