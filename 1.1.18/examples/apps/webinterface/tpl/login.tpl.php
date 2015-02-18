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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb">

<head>

  <base href="<?= TeamSpeak3_Helper_Uri::getBaseUri()->spaceToPercent(); ?>" />

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="pragma" content="no-cache" />
  <meta http-equiv="expires" content="-1" />

  <meta name="robots" content="noindex, nofollow" />
  <meta name="copyright" content="<?= $this->copyright; ?>" />
  <meta name="generator" content="<?= $this->generator; ?>" />
  <meta name="resource-type" content="Document" />

  <title>TeamSpeak Web Control Panel - <?= $this->title; ?></title>

  <link href="tpl/favicon.ico" rel="shortcut icon" type="image/x-icon" />

  <link rel="stylesheet" href="tpl/css/template.css" type="text/css" />

</head>

<body class="plain">

  <!-- Begin Login Form -->

  <div id="login">

    <form id="login-form" action="?module=login" method="post">

    <div class="login-area">

      <fieldset class="login-form">

        <h2>Log in to the Web Control Panel</h2>

        <br />

        <div id="login-field-host">
          <label for="hostaddr">Server Address:</label>
          <br/>
          <input type="text" name="hostaddr" id="hostaddr" value="" size="30" maxlength="128" />
        </div>

        <div id="login-field-port">
          <label for="hostport">Server Port:</label>
          <br/>
          <input type="text" name="hostport" id="hostport" value="<?= $this->defaultPort; ?>" size="30" maxlength="5" />
        </div>

        <div id="login-field-user">
          <label for="username">Username:</label>
          <br/>
          <input type="text" name="username" id="username" value="" size="30" maxlength="32" />
        </div>

        <div id="login-field-pass">
          <label for="password">Password:</label>
          <br/>
          <input type="password" name="password" id="password" value="" size="30" maxlength="32" />
        </div>

        <div id="login-submit">
          <input type="submit" value="Login" />
        </div>

      </fieldset>

      <div class="login-info"><?= $this->copyright; ?></div>
      <div class="login-foot"></div>

      <input type="hidden" name="action" value="login" />

    </div>

    <?php if($this->eventMesg): ?>
    <span class="event <?= $this->eventType; ?>"><?= $this->eventMesg; ?></span>
    <?php endif; ?>

    </form>

  </div>

  <!-- End Login Form -->

</body>

</html>
