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

?>

<?php $this->render("header.tpl.php"); ?>

<table width="100%">

  <tr>
    <td valign="top">

      <!-- Begin Server Information -->

      <table class="list">

        <tr>
          <th colspan="2"><img src="tpl/images/icons/info.png" align="top" alt="" /> General Information</th>
        </tr>

        <tr>
          <td width="170">Status:</td>
          <td><?= $this->ts3_details["virtualserver_status"]; ?></td>
        </tr>
        <tr>
          <td>Name:</td>
          <td><?= $this->ts3_details["virtualserver_name"]; ?></td>
        </tr>
        <tr>
          <td>Unique Identifer:</td>
          <td><?= $this->ts3_details["virtualserver_unique_identifier"]; ?></td>
        </tr>
        <tr>
          <td>Port:</td>
          <td><?= $this->ts3_details["virtualserver_port"]; ?></td>
        </tr>
        <tr>
          <td>Version:</td>
          <td><?= $this->ts3_details["virtualserver_version"]; ?> on <?= $this->ts3_details["virtualserver_platform"]; ?></td>
        </tr>
        <tr>
          <td>Created:</td>
          <td><?= date(DATE_ATOM, $this->ts3_details["virtualserver_created"]); ?></td>
        </tr>

        <tr>
          <th colspan="2"><img src="tpl/images/icons/info.png" align="top" alt="" /> Usage Information</th>
        </tr>

        <tr>
          <td>Uptime:</td>
          <td><?= $this->ts3_details["virtualserver_uptime"]; ?></td>
        </tr>
        <tr>
          <td>Current Clients:</td>
          <td><?= $this->ts3_details["virtualserver_clientsonline"]; ?> / <?= $this->ts3_details["virtualserver_maxclients"]; ?></td>
        </tr>
        <tr>
          <td>Current Channels:</td>
          <td><?= $this->ts3_details["virtualserver_channelsonline"]; ?></td>
        </tr>
        <tr>
          <td>Total Client Connections:</td>
          <td><?= $this->ts3_details["virtualserver_client_connections"]; ?></td>
        </tr>
        <tr>
          <td>Total Query Connections:</td>
          <td><?= $this->ts3_details["virtualserver_query_client_connections"]; ?></td>
        </tr>
        <tr>
          <td>Average Packet loss:</td>
          <td><?= $this->ts3_details["virtualserver_total_packetloss_total"]; ?></td>
        </tr>
        <tr>
          <td>Average Ping:</td>
          <td><?= $this->ts3_details["virtualserver_total_ping"]; ?> ms</td>
        </tr>

        <tr>
          <th colspan="2"><img src="tpl/images/icons/info.png" align="top" alt="" /> Traffic Information</th>
        </tr>

        <tr>
          <td>Incoming Bandwidth:</td>
          <td><?= $this->ts3_details["connection_bandwidth_received_last_second_total"]; ?></td>
        </tr>
        <tr>
          <td>Outgoing Bandwidth:</td>
          <td><?= $this->ts3_details["connection_bandwidth_sent_last_second_total"]; ?></td>
        </tr>
        <tr>
          <td>Packets received:</td>
          <td><?= $this->ts3_details["connection_packets_received_total"]; ?></td>
        </tr>
        <tr>
          <td>Packets sent:</td>
          <td><?= $this->ts3_details["connection_packets_sent_total"]; ?></td>
        </tr>
        <tr>
          <td>Data received:</td>
          <td><?= $this->ts3_details["connection_bytes_received_total"]; ?></td>
        </tr>
        <tr>
          <td>Data sent:</td>
          <td><?= $this->ts3_details["connection_bytes_sent_total"]; ?></td>
        </tr>

      </table>

      <!-- End Server Information -->

    </td>

    <td valign="top" style="padding-left: 10px; width: 400px;">

      <!-- Begin Server Viewer -->

      <table class="list">

        <tr>
          <th><img src="tpl/images/icons/info.png" align="top" alt="" /> Viewer</th>
        </tr>

        <tr>
          <td>

<?= $this->ts3_viewer; ?>

          </td>
        </tr>

      </table>

      <!-- End Server Viewer -->

    </td>
  </tr>

</table>

<?php $this->render("footer.tpl.php"); ?>
