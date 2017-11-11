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

/* set as parent file */
define("TS3WA_VALID", TRUE);

/* define current working dir as root */
define("TS3WA_ROOT", dirname(__FILE__));

/* load required files to init application */
require_once(TS3WA_ROOT . "/inc/defines.inc.php");
require_once(TS3WA_ROOT . "/inc/compatibility.inc.php");
require_once(TS3WA_ROOT . "/inc/bootstrapper.inc.php");
