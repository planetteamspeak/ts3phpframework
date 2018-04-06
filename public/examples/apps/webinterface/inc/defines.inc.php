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

/* set required PHP version */
define("TS3WA_PARSER", "5.1.2");

/* define special char constants */
define("DS", DIRECTORY_SEPARATOR);
define("PS", PATH_SEPARATOR);
define("LS", "\r\n");

/* define various path constants */
define("TS3WA_APP", TS3WA_ROOT . DS . "app");
define("TS3WA_INC", TS3WA_ROOT . DS . "inc");
define("TS3WA_LIB", TS3WA_ROOT . DS . "lib");
define("TS3WA_TPL", TS3WA_ROOT . DS . "tpl");

/* define path to file transfer client */
define("TS3WA_FTC", "../../ts3icon.php");
