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

<a class="large" href="?module=prvkey&amp;action=create">Create</a>

<br />
<br />

<!-- Begin Privilege Key List -->

<table class="list">
  <tr>
    <th>Privilege Key</th>
    <th width="150">Type</th>
    <th>Group</th>
    <th>Channel</th>
    <th width="250">Created</th>
  </tr>

  <?php foreach($this->ts3_prvkeys as $prvkey): ?>
  <tr>
    <td>
      <img src="tpl/images/icons/privilege.png" align="top" alt="" />
      <?= $prvkey["token"]; ?>
      <br />
      <span class="small">Description: <?= htmlentities($prvkey["token_description"]); ?></span>
    </td>
    <td><?= $prvkey["token_type"] == 0 ? "Server" : "Channel"; ?> Group</td>
    <td><?= $prvkey["token_id1"]; ?></td>
    <td><?= $prvkey["token_id2"]; ?></td>
    <td><?= date(DATE_ATOM, $prvkey["token_created"]); ?></td>
  </tr>
  <?php endforeach; ?>

</table>

<!-- End Privilege Key List -->

<?php $this->render("footer.tpl.php"); ?>
