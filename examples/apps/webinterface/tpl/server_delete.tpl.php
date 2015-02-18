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

<form action="." method="post">

  Are you sure you want to delete this virtual server <b>(ID <?= $this->ts3_sid; ?>)</b>? This action cannot be undone.

  <br />
  <br />

  <input type="hidden" name="module" value="server" />
  <input type="hidden" name="action" value="dodelete" />
  <input type="hidden" name="id" value="<?php echo $this->ts3_sid; ?>" />
  <input type="submit" value="Yes" />
  <input type="button" value="No" onclick="history.back(-1);" />

</form>

<?php $this->render("footer.tpl.php"); ?>
