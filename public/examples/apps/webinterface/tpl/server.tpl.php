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

<a class="large" href="?module=server&amp;action=create">Create</a>

<br />
<br />

<!-- Begin Virtual Server List -->

<table class="list">
  <tr>
    <th width="40">ID</th>
    <th>Name</th>
    <th width="160">Status</th>
    <th width="100">Uptime</th>
    <th width="60">Port</th>
    <th width="100">Clients</th>
    <th colspan="5">Actions</th>
  </tr>

  <?php foreach($this->ts3_servers as $sid => $server): ?>
  <tr>
    <td><?= $sid; ?></td>
    <td>
      <img src="tpl/images/icons/item.png" align="top" alt="" />
      <a href="?module=server&amp;action=modify&amp;id=<?= $sid; ?>"><?= htmlspecialchars($server); ?></a>
      <br />
      <span class="small">Unique Identifier: <?= $server->virtualserver_unique_identifier; ?></span>
    </td>
    <td>
      <img src="tpl/images/icons/<?= $server->virtualserver_status == "online" ? "active" : "inactive"; ?>.png" align="top" alt="" />
      <?= $server->virtualserver_status; ?>
      <br />
      <span class="small">Autostart: <?= $server->virtualserver_autostart ? "enabled" : "disabled"; ?></span>
    </td>
    <td><?= $server->isOffline() ? "-" : TeamSpeak3_Helper_Convert::seconds($server->virtualserver_uptime); ?></td>
    <td><?= $server->virtualserver_port; ?></td>
    <td>
      <?= $server->isOffline() ? "- / -" : $server->clientCount() . " / " . $server->virtualserver_maxclients; ?>
      <br />
      <span class="small">Queries: <?= $server->isOffline() ? "- / -" : $server->virtualserver_queryclientsonline . " / " . $server->virtualserver_maxclients; ?></span>
    </td>
    <td>
      <img src="tpl/images/icons/<?= $server->getId() == $this->ts3_usesid ? "deselect" : "select"; ?>.png" align="top" alt="" />
      <a href="?module=server&amp;action=select&amp;id=<?= $server->getId() == $this->ts3_usesid ? 0 : $sid; ?>">
        <?= $server->getId() == $this->ts3_usesid ? "Deselect" : "Select"; ?>
      </a>
    </td>
    <td>
      <img src="tpl/images/icons/<?= $server->isOnline() ? "stop" : "start"; ?>.png" align="top" alt="" />
      <a href="?module=server&amp;action=<?= $server->isOnline() ? "stop" : "start"; ?>&amp;id=<?= $sid; ?>">
        <?= $server->isOnline() ? "Stop" : "Start"; ?>
      </a>
    </td>
    <td>
      <img src="tpl/images/icons/database.png" align="top" alt="" />
      <a href="?module=server&amp;action=export&amp;id=<?= $sid; ?>">
        Export
      </a>
    </td>
    <td>
      <img src="tpl/images/icons/edit.png" align="top" alt="" />
      <a href="?module=server&amp;action=modify&amp;id=<?= $sid; ?>">
        Modify
      </a>
    </td>
    <td>
      <img src="tpl/images/icons/delete.png" align="top" alt="" />
      <a href="?module=server&amp;action=delete&amp;id=<?= $sid; ?>">
        Delete
      </a>
    </td>
  </tr>
  <?php endforeach; ?>

</table>

<!-- End Virtual Server List -->

<?php $this->render("footer.tpl.php"); ?>
