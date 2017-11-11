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

<!-- Begin Virtual Server Form -->

<form action="." method="post">

  <fieldset>
  <legend> General Settings </legend>

  <table class="data">

    <tr>
      <td class="label">Name:</td>
      <td><input type="text" name="props[virtualserver_name]" value="<?= htmlspecialchars($this->ts3_props["virtualserver_name"]); ?>" size="32" /></td>

      <td class="label">Phonetic Name:</td>
      <td><input type="text" name="props[virtualserver_name_phonetic]" value="<?= htmlspecialchars($this->ts3_props["virtualserver_name_phonetic"]); ?>" size="32" /></td>
    </tr>

    <tr>
      <td class="label">Password:</td>
      <td><input type="password" name="props[virtualserver_password]" value="<?= $this->ts3_props["virtualserver_flag_password"] ? $this->defaultPassword : ""; ?>" size="32" /></td>

      <td class="label">Minimum Client Build:</td>
      <td><input type="text" name="props[virtualserver_min_client_version]" value="<?= $this->ts3_props["virtualserver_min_client_version"]; ?>" size="8" maxlength="5" /></td>
    </tr>

    <tr>
      <td class="label">Maximum Clients:</td>
      <td><input type="text" name="props[virtualserver_maxclients]" value="<?= $this->ts3_props["virtualserver_maxclients"]; ?>" size="8" maxlength="5" /></td>

      <td class="label">Reserved Slots:</td>
      <td><input type="text" name="props[virtualserver_reserved_slots]" value="<?= $this->ts3_props["virtualserver_reserved_slots"]; ?>" size="8" maxlength="5" /></td>
    </tr>

    <tr>
      <td class="label">Welcome Message:</td>
      <td colspan="3">
        <textarea name="props[virtualserver_welcomemessage]" rows="5" cols="90"><?= htmlspecialchars($this->ts3_props["virtualserver_welcomemessage"]); ?></textarea>
      </td>
    </tr>

    <tr>
      <td class="label">Autostart:</td>
      <td colspan="3">
        <select name="props[virtualserver_autostart]">
          <option value="1" <?= $this->ts3_props["virtualserver_autostart"] == 1 ? "selected=\"selected\"" : ""; ?>>Enabled</option>
          <option value="0" <?= $this->ts3_props["virtualserver_autostart"] == 0 ? "selected=\"selected\"" : ""; ?>>Disabled</option>
        </select>
      </td>
   </tr>

    <tr>
      <td class="label">Server Icon:</td>
      <td colspan="3">
        <?php foreach($this->ts3_icons as $iconid => $ftdata): ?>
        <div class="icon">
          <input type="radio" name="props[virtualserver_icon_id]" value="<?= $iconid; ?>" <?= $this->ts3_props["virtualserver_icon_id"] == $iconid ? "checked=\"checked\"" : ""; ?> />
          <img src="<?= TS3WA_FTC; ?>?ftdata=<?= $ftdata; ?>" alt="" />
        </div>
        <?php endforeach; ?>
      </td>
    </tr>

  </table>

  </fieldset>

  <br />

  <fieldset>
  <legend> Security Settings </legend>

  <table class="data">

    <tr>
      <td class="label">Minimum Identity Level:</td>
      <td><input type="text" name="props[virtualserver_needed_identity_security_level]" value="<?= $this->ts3_props["virtualserver_needed_identity_security_level"]; ?>" size="8" maxlength="5" /></td>
    </tr>

    <tr>
      <td class="label">Codec Encryption Mode:</td>
      <td>
        <select name="props[virtualserver_codec_encryption_mode]">
          <option value="0" <?= $this->ts3_props["virtualserver_codec_encryption_mode"] == 0 ? "selected=\"selected\"" : ""; ?>>Configure per channel</option>
          <option value="1" <?= $this->ts3_props["virtualserver_codec_encryption_mode"] == 1 ? "selected=\"selected\"" : ""; ?>>Globally disabled</option>
          <option value="2" <?= $this->ts3_props["virtualserver_codec_encryption_mode"] == 2 ? "selected=\"selected\"" : ""; ?>>Globally enabled</option>
        </select>
      </td>
    </tr>

  </table>

  </fieldset>

  <br />

  <fieldset>
  <legend> Host Message Settings </legend>

  <table class="data">

    <tr>
      <td class="label">Mode:</td>
      <td>
        <select name="props[virtualserver_hostmessage_mode]">
          <option value="0" <?= $this->ts3_props["virtualserver_hostmessage_mode"] == 0 ? "selected=\"selected\"" : ""; ?>>Disable message</option>
          <option value="1" <?= $this->ts3_props["virtualserver_hostmessage_mode"] == 1 ? "selected=\"selected\"" : ""; ?>>Show message in log</option>
          <option value="2" <?= $this->ts3_props["virtualserver_hostmessage_mode"] == 2 ? "selected=\"selected\"" : ""; ?>>Show message in modal dialog</option>
          <option value="3" <?= $this->ts3_props["virtualserver_hostmessage_mode"] == 3 ? "selected=\"selected\"" : ""; ?>>Show message in modal dialog and close connection</option>
        </select>
      </td>
    </tr>

    <tr>
      <td class="label">Text:</td>
      <td>
        <textarea name="props[virtualserver_hostmessage]" rows="5" cols="100"><?= htmlspecialchars($this->ts3_props["virtualserver_hostmessage"]); ?></textarea>
      </td>
    </tr>

  </table>

  </fieldset>

  <br />

  <fieldset>
  <legend> Host Banner </legend>

  <table class="data">

    <tr>
      <td class="label">URL:</td>
      <td><input type="text" name="props[virtualserver_hostbanner_url]" value="<?= $this->ts3_props["virtualserver_hostbanner_url"]; ?>" size="64" /></td>
    </tr>

    <tr>
      <td class="label">GFX URL:</td>
      <td><input type="text" name="props[virtualserver_hostbanner_gfx_url]" value="<?= $this->ts3_props["virtualserver_hostbanner_gfx_url"]; ?>" size="64" /></td>
    </tr>

    <tr>
      <td class="label">GFX Interval:</td>
      <td><input type="text" name="props[virtualserver_hostbanner_gfx_interval]" value="<?= $this->ts3_props["virtualserver_hostbanner_gfx_interval"]; ?>" size="8" maxlength="5" /></td>
    </tr>

  </table>

  </fieldset>

  <br />

  <fieldset>
  <legend> Host Button </legend>

  <table class="data">

    <tr>
      <td class="label">URL:</td>
      <td><input type="text" name="props[virtualserver_hostbutton_url]" value="<?= $this->ts3_props["virtualserver_hostbutton_url"]; ?>" size="64" /></td>
    </tr>

    <tr>
      <td class="label">GFX URL:</td>
      <td><input type="text" name="props[virtualserver_hostbutton_gfx_url]" value="<?= $this->ts3_props["virtualserver_hostbutton_gfx_url"]; ?>" size="64" /></td>
    </tr>

    <tr>
      <td class="label">Tooltip:</td>
      <td><input type="text" name="props[virtualserver_hostbutton_tooltip]" value="<?= $this->ts3_props["virtualserver_hostbutton_tooltip"]; ?>" size="32" /></td>
    </tr>

  </table>

  </fieldset>

  <input type="hidden" name="module" value="server" />
  <input type="hidden" name="action" value="domodify" />
  <input type="hidden" name="id" value="<?= $this->ts3_props["virtualserver_id"]; ?>" />

  <br />
  <input type="submit" value="Save Settings" />

</form>

<!-- End Virtual Server Form -->

<?php $this->render("footer.tpl.php"); ?>
