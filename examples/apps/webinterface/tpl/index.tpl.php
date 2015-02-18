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

<div class="quickinfo">

  <!-- Begin Global Information -->

  <table class="list">

    <tr>
      <th colspan="2"><img src="tpl/images/icons/info.png" align="top" alt="" /> General Information</th>
    </tr>

    <tr>
      <td>Uptime:</td>
      <td><?= $this->ts3_details["instance_uptime"]; ?></td>
    </tr>
    <tr>
      <td>Version:</td>
      <td><?= $this->ts3_version["version"]; ?></td>
    </tr>
    <tr>
      <td>Build:</td>
      <td><?= $this->ts3_version["build"]; ?></td>
    </tr>
    <tr>
      <td>Platform:</td>
      <td><?= $this->ts3_version["platform"]; ?></td>
    </tr>
    <tr>
      <td>Total Servers Online:</td>
      <td><?= $this->ts3_details["virtualservers_running_total"]; ?></td>
    </tr>
    <tr>
      <td>Total Channels Online:</td>
      <td><?= $this->ts3_details["virtualservers_total_channels_online"]; ?></td>
    </tr>
    <tr>
      <td>Total Clients Online:</td>
      <td><?= $this->ts3_details["virtualservers_total_clients_online"]; ?> / <?= $this->ts3_details["virtualservers_total_maxclients"]; ?></td>
    </tr>

    <tr>
      <th colspan="2"><img src="tpl/images/icons/info.png" align="top" alt="" /> Traffic Information</th>
    </tr>

    <tr>
      <td>Incoming Bandwidth:</td>
      <td><?= $this->ts3_details["connection_bandwidth_sent_last_second_total"]; ?></td>
    </tr>
    <tr>
      <td>Outgoing Bandwidth:</td>
      <td><?= $this->ts3_details["connection_bandwidth_received_last_second_total"]; ?></td>
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

  <!-- End Global Information -->

</div>

<p>
  Welcome to the TeamSpeak Web Control Panel!<br />
  <br />
  This <u>example</u> application is intended to demonstrate how easy it is to use the <b>TS3 PHP Framework</b> to create powerful
  web applications to manage all features of your <i>TeamSpeak 3 Server</i> instances. If you're using this <u>example</u>
  application for the first time, please navigate through the different pages by clicking the menu items on top to see what
  the <i>TeamSpeak Web Control Panel</i> is capable of. The layout of each page is very clean and intuitive so you don't really need
  a manual to use it.<br />
  <br />
  If you like and use the <b>TS3 PHP Framework</b> and would like to support the product development process, please feel free
  to <a href="http://tinyurl.com/289gzjh" target="_blank">make a donation</a> for our time maintaining the software so our
  efforts won't be wasted.<br />
  <br />
  <a href="http://tinyurl.com/289gzjh" target="_blank"><img src="tpl/images/paypal.gif" alt="" /></a><br />
  <br />
  Please visit the <a href="http://forum.planetteamspeak.com" target="_blank">Planet TeamSpeak</a> forums if you have any bugs
  to report or features to request. Thank you!<br />
  <br />
  <i>- The Planet TeamSpeak Team</i>
</p>

<?php $this->render("footer.tpl.php"); ?>
