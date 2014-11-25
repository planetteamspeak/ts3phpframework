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

// ==================== [ BEGIN OF GLOBAL CONFIGURATION AREA ] ====================

/**
 * The IPv4 address or FQDN of the TeamSpeak 3 server. Use the local loopback
 * address (127.0.0.1 or localhost) if the server is running on the same host
 * and is not bound to a specific address.
 *
 * Example: 123.45.67.89 or teamspeak.example.com
 */
$cfg["host"] = "127.0.0.1";

/**
 * The ServerQuery port used by the TeamSpeak 3 server. Do NOT change this
 * setting unless you know what you're doing.
 *
 * Default: 10011
 */
$cfg["query"] = 10011;

/**
 * The UDP voice port used by the TeamSpeak 3 server. This is the same port
 * you entered in your TeamSpeak 3 client application.
 *
 * Default: 10011
 */
$cfg["voice"] = 9987;

/**
 * The login credentials used to authenticate with the TeamSpeak 3 server.
 */
$cfg["user"] = "serveradmin";
$cfg["pass"] = "password";

// ===================== [ END OF GLOBAL CONFIGURATION AREA ] =====================

/* eof */
return $cfg;
