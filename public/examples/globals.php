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

/**
 * Converts each element of an array to produce valid HTML code.
 *
 * @param  array $array
 * @return array
 */
function htmlspecialchars_array(array $array)
{
  foreach($array as $key => $val) {
    $array[$key] = (is_array($val)) ? htmlspecialchars_array($val) : htmlspecialchars($val);
  }

  return $array;
}

/**
 * Quick-and-dirty function for shared usage to display a server selection form. Returns
 * the value of $_GET['server'] which should be the ID of the selected virtual server.
 *
 * @param  array $servers
 * @return integer
 */
function form_server_selector(array $servers)
{
  $selected_sid = TeamSpeak3_Helper_Uri::getUserParam("server", 0);
  $current_page = TeamSpeak3_Helper_Uri::getUserParam("page", "default");

  if(!$selected_sid && count($servers))
  {
    $selected_sid = current($servers)->getId();
  }

  echo "<form action=\".\" method=\"get\">" .
       "<input type=\"hidden\" name=\"page\" value=\"" . $current_page . "\" />\n" .
       "<select name=\"server\">\n";

  foreach($servers as $sid => $server)
  {
    echo "<option value=\"" . $sid . "\"" . ($selected_sid == $sid ? " selected=selected" : "") . ">" . htmlspecialchars($server) . "</option>\n";
  }

  echo "</select>\n" .
       "<input type=\"submit\" value=\"Go\" />\n" .
       "</form>\n" .
       "<br />\n";

  return $selected_sid;
}

/**
 * Quick-and-dirty function for shared usage to display a channel selection form. Returns
 * the value of $_GET['channel'] which should be the ID of the selected channel.
 *
 * @param  array   $channels
 * @param  boolean $internal
 * @return integer
 */
function form_channel_selector(array $channels, $internal = FALSE)
{
  $selected_sid = TeamSpeak3_Helper_Uri::getUserParam("server", 0);
  $selected_cid = TeamSpeak3_Helper_Uri::getUserParam("channel", 0);
  $current_page = TeamSpeak3_Helper_Uri::getUserParam("page", "default");

  echo "<form action=\".\" method=\"get\">" .
       "<input type=\"hidden\" name=\"page\" value=\"" . $current_page . "\" />\n" .
       ($selected_sid ? "<input type=\"hidden\" name=\"server\" value=\"" . $selected_sid . "\" />\n" : "") .
       "<select name=\"channel\">\n";

  if($internal)
  {
    // virtual channel (id:0) used for avatars and icons
    echo "<option value=\"0\">--- internal ---</option>\n";
  }
  else
  {
    if(!$selected_cid && count($channels))
    {
      $selected_cid = current($channels)->getId();
    }
  }

  foreach($channels as $cid => $channel)
  {
    $prefix = ($channel["pid"]) ? "&nbsp;&nbsp;&nbsp;&nbsp;" : "";

    echo "<option value=\"" . $cid . "\"" . ($selected_cid == $cid ? " selected=selected" : "") . ">" . $prefix . htmlspecialchars($channel) . "</option>\n";
  }

  echo "</select>\n" .
       "<input type=\"submit\" value=\"Go\" />\n" .
       "</form>\n" .
       "<br />\n";

  return $selected_cid;
}

/**
 * Quick-and-dirty function for shared usage to display a client selection form. Returns
 * the value of $_GET['client'] which should be the ID of the selected client.
 *
 * @param  array   $clients
 * @return integer
 */
function form_client_selector(array $clients)
{
  $selected_sid  = TeamSpeak3_Helper_Uri::getUserParam("server", 0);
  $selected_clid = TeamSpeak3_Helper_Uri::getUserParam("client", 0);
  $current_page  = TeamSpeak3_Helper_Uri::getUserParam("page", "default");

  if(!$selected_clid && count($clients))
  {
    $selected_clid = current($clients)->getId();
  }

  echo "<form action=\".\" method=\"get\">" .
       "<input type=\"hidden\" name=\"page\" value=\"" . $current_page . "\" />\n" .
       ($selected_sid ? "<input type=\"hidden\" name=\"server\" value=\"" . $selected_sid . "\" />\n" : "") .
       "<select name=\"client\">\n";

  foreach($clients as $clid => $client)
  {
    echo "<option value=\"" . $clid . "\"" . ($selected_clid == $clid ? " selected=selected" : "") . ">" . htmlspecialchars($client) . "</option>\n";
  }

  echo "</select>\n" .
       "<input type=\"submit\" value=\"Go\" />\n" .
       "</form>\n" .
       "<br />\n";

  return $selected_clid;
}

/**
 * Quick-and-dirty function for shared usage to display a perm goup selection form. Returns
 * the value of $_GET['group'] which should be the ID of the selected server/channel group.
 *
 * @param  array $sgroups
 * @param  array $cgroups
 * @return TeamSpeak3_Helper_String
 */
function form_group_selector(array $sgroups, array $cgroups)
{
  $selected_sid = TeamSpeak3_Helper_Uri::getUserParam("server", 0);
  $selected_gid = TeamSpeak3_Helper_Uri::getUserParam("group", 0);
  $current_page = TeamSpeak3_Helper_Uri::getUserParam("page", "default");

  if(!$selected_gid && count($sgroups))
  {
    $selected_gid = "sg" . current($sgroups)->getId();
  }

  echo "<form action=\".\" method=\"get\">" .
       "<input type=\"hidden\" name=\"page\" value=\"" . $current_page . "\" />\n" .
       ($selected_sid ? "<input type=\"hidden\" name=\"server\" value=\"" . $selected_sid . "\" />\n" : "") .
       "<select name=\"group\">\n";

  // query server groups
  echo "<optgroup label=\"ServerQuery Groups\">\n";
  foreach($sgroups as $sgid => $sgroup)
  {
    if($sgroup["type"] != TeamSpeak3::GROUP_DBTYPE_SERVERQUERY) continue;

    echo "<option value=\"sg" . $sgid . "\"" . ($selected_gid == "sg" . $sgid ? " selected=selected" : "") . ">" . htmlspecialchars($sgroup) . "</option>\n";
  }
  echo "</optgroup>\n";

  // regular server groups
  echo "<optgroup label=\"Server Groups\">\n";
  foreach($sgroups as $sgid => $sgroup)
  {
    if($sgroup["type"] != TeamSpeak3::GROUP_DBTYPE_REGULAR) continue;

    echo "<option value=\"sg" . $sgid . "\"" . ($selected_gid == "sg" . $sgid ? " selected=selected" : "") . ">" . htmlspecialchars($sgroup) . "</option>\n";
  }
  echo "</optgroup>\n";

  // channel groups
  echo "<optgroup label=\"Channel Groups\">\n";
  foreach($cgroups as $cgid => $cgroup)
  {
    if($cgroup["type"] != TeamSpeak3::GROUP_DBTYPE_REGULAR) continue;

    echo "<option value=\"cg" . $cgid . "\"" . ($selected_gid == "cg" . $cgid ? " selected=selected" : "") . ">" . htmlspecialchars($cgroup) . "</option>\n";
  }
  echo "</optgroup>\n";

  echo "</select>\n" .
       "<input type=\"submit\" value=\"Go\" />\n" .
       "</form>\n" .
       "<br />\n";

  return new TeamSpeak3_Helper_String($selected_gid);
}
