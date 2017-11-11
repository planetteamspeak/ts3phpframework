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
  echo "<h1>Group Identification - " . $ts3_ServerInstance . "</h1>\n";

  /* display server select form */
  $selected_sid = form_server_selector($ts3_ServerInstance->serverList());

  /* get TeamSpeak3_Node_Server object by ID */
  $ts3_VirtualServer = $ts3_ServerInstance->serverGetById($selected_sid);

  /* load server group profiles ordered by power */
  $ts3_GroupProfiles = $ts3_VirtualServer->serverGroupGetProfiles();

  /* walk through list of clients */
  echo "<table class=\"list\">\n";
  echo "<tr>\n" .
       "  <th>ID</th>\n" .
       "  <th>Name</th>\n" .
       "  <th>Permanent</th>\n" .
       "  <th>Permission Modify Power</th>\n" .
       "  <th>Members</th>\n" .
       "</tr>\n";
  foreach($ts3_GroupProfiles as $ts3_GroupProfile)
  {
    $node = $ts3_GroupProfile["__node"];

    echo "<tr>\n" .
         "  <td>" . $node->getId() . "</td>\n" .
         "  <td>" . htmlspecialchars($node) . "</td>\n" .
         "  <td>" . ($ts3_GroupProfile["b_group_is_permanent"] ? "Yes" : "No") . "</td>\n" .
         "  <td>" . $ts3_GroupProfile["i_permission_modify_power"] . " for " . $ts3_GroupProfile["i_needed_modify_power_count"] . " permissions</td>\n" .
         "  <td>" . count($node->clientList()) . "</td>\n" .
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
