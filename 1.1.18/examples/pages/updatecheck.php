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

try
{
  /* connect to server, login and get TeamSpeak3_Node_Host object by URI */
  $ts3_ServerInstance = TeamSpeak3::factory("serverquery://" . $cfg["user"] . ":" . $cfg["pass"] . "@" . $cfg["host"] . ":" . $cfg["query"] . "/");

  /* access server instance address using __toString() */
  echo "<h1>Search Outdated Clients - " . $ts3_ServerInstance . "</h1>\n";

  /* display server select form */
  $selected_sid = form_server_selector($ts3_ServerInstance->serverList());

  /* get TeamSpeak3_Node_Server object by ID */
  $ts3_VirtualServer = $ts3_ServerInstance->serverGetById($selected_sid);

  /* grab the latest build numbers */
  $ts3_UpdateServer = TeamSpeak3::factory("update");

  /* display latest version strings */
  echo "<b class=\"pre\">Latest Client Versions:</b> " .
       "<span class=\"green\">" . $ts3_UpdateServer->getVersion() . "</span>, " .
       "<span class=\"orange\">" . $ts3_UpdateServer->getVersion("beta") . "</span>, " .
       "<span class=\"red\">" . $ts3_UpdateServer->getVersion("alpha") . "</span> " .
       "<br />\n";

  /* display latest build numbers */
  echo "<b class=\"pre\">Latest Client Builds:&nbsp;&nbsp;</b> " .
       "<span class=\"green\">" . $ts3_UpdateServer->getRev() . "</span>, " .
       "<span class=\"orange\">" . $ts3_UpdateServer->getRev("beta") . "</span>, " .
       "<span class=\"red\">" . $ts3_UpdateServer->getRev("alpha") . "</span> " .
       "<br /><br />\n";

  /* list outdated clients */
  echo "<table class=\"list\">\n";
  echo "<tr>\n" .
       "  <th>ID</th>\n" .
       "  <th>Nickname</th>\n" .
       "  <th>Unique Identifier</th>\n" .
       "  <th>Status</th>\n" .
       "  <th>Platform</th>\n" .
       "  <th>Version</th>\n" .
       "</tr>\n";
  foreach($ts3_VirtualServer->clientList() as $client)
  {
    if($client["client_type"] || $client->getRev() >= $ts3_UpdateServer->getRev()) continue;

    echo "<tr>\n" .
         "  <td>" . $client->getId() . "</td>\n" .
         "  <td>" . htmlspecialchars($client) . "</td>\n" .
         "  <td>" . htmlspecialchars($client["client_unique_identifier"]) . "</td>\n" .
         "  <td><img src=\"../images/viewer/" . $client->getIcon() . ".png\" alt=\"\" /></td>\n" .
         "  <td>" . $client["client_platform"] . "</td>\n" .
         "  <td>" . $client["client_version"] . "</td>\n" .
         "</tr>\n";
  }
  echo "</table>\n";

  /* display runtime from adapter profiler */
  echo "<p>Executed " . $ts3_ServerInstance->getAdapter()->getQueryCount() . " queries in " . $ts3_ServerInstance->getAdapter()->getQueryRuntime() . " seconds</p>\n";
}
catch(Exception $e)
{
  /* catch exceptions and display error message if anything went wrong */
  echo "<span class='error'><b>Error " . $e->getCode() . ":</b> " . $e->getMessage() . "</span>\n";
}
