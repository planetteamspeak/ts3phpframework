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

<!-- Begin Log Viewer -->

<div class="log">

  <table>
    <tr>
      <th width="210">Date/Time</th>
      <th width="110">Level</th>
      <th width="110">Channel</th>
      <th>Event</th>
    </tr>

    <?php foreach($this->ts3_logs as $logs): ?>
    <?php $logs = TeamSpeak3_Helper_Convert::logEntry($logs["l"]); ?>
    <tr class="<?= strtolower(TeamSpeak3_Helper_Convert::logLevel($logs["level"])); ?>">
      <td><?= date(DATE_ATOM, $logs["timestamp"]); ?></td>
      <td><?= TeamSpeak3_Helper_Convert::logLevel($logs["level"]); ?></td>
      <td><?= htmlspecialchars($logs["channel"]); ?></td>
      <td><?= htmlspecialchars($logs["msg"]); ?></td>
    </tr>
    <?php endforeach; ?>

  </table>

</div>

<!-- End Log Viewer -->

<?php $this->render("footer.tpl.php"); ?>
