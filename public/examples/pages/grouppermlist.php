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
  echo "<h1>Group Permission List - " . $ts3_ServerInstance . "</h1>\n";

  /* display server select form */
  $selected_sid = form_server_selector($ts3_ServerInstance->serverList());

  /* get TeamSpeak3_Node_Server object by ID */
  $ts3_VirtualServer = $ts3_ServerInstance->serverGetById($selected_sid);

  /* display server/channel group select form */
  $selected_gid = form_group_selector($ts3_VirtualServer->serverGroupList(), $ts3_VirtualServer->channelGroupList());

  /* get group object by ID */
  if($selected_gid->startsWith("sg"))
  {
    $ts3_Group = $ts3_VirtualServer->serverGroupGetById($selected_gid->filterDigits());
  }
  else
  {
    $ts3_Group = $ts3_VirtualServer->channelGroupGetById($selected_gid->filterDigits());
  }

  /* display group database type */
  echo "<b class=\"pre\">Group Type:</b> " . TeamSpeak3_Helper_Convert::groupType($ts3_Group["type"]) . "<br /><br />\n";

  /* display list of permissions */
  echo "<table class=\"list\">\n";
  echo "<tr>\n" .
       "  <th>ID</th>\n" .
       "  <th>Name</th>\n" .
       "  <th>Category</th>\n" .
       "  <th>Type</th>\n" .
       "  <th>Value</th>\n" .
       "  <th>Skip</th>\n" .
       "  <th>Negated</th>\n" .
       "</tr>\n";
  foreach($ts3_Group->permList() as $perm)
  {
    $name = $ts3_ServerInstance->permissionGetNameById($perm["permid"]);
    $pcat = $ts3_ServerInstance->permissionGetCategoryById($perm["permid"]);
    $type = $name->startsWith("b_") ? "Bool" : "Int";

    echo "<tr>\n" .
         "  <td>" . $perm["permid"] . "</td>\n" .
         "  <td>" . $name . "<br /><span class=\"small\">" . TeamSpeak3_Helper_Convert::permissionCategory($pcat) . "<span></td>\n" .
         "  <td>0x" . strtoupper(dechex($pcat)) . "00</td>\n" .
         "  <td>" . $type . "</td>\n" .
         "  <td>" . $perm["permvalue"] . "</td>\n" .
         "  <td>" . $perm["permskip"] . "</td>\n" .
         "  <td>" . $perm["permnegated"] . "</td>\n" .
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
