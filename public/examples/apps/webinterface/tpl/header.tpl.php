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

<body>

  <a name="up" id="up"></a>

  <!-- Begin Header -->

  <div id="header">
    <div id="header-logo"></div>
    <div id="header-info">
      Logged in as <?= $this->loginname; ?>
      &nbsp;&nbsp;<span class="separator">|</span>&nbsp;&nbsp;
      <?= date("r"); ?>
      &nbsp;&nbsp;<span class="separator">|</span>&nbsp;&nbsp;
      <a href="?module=login&amp;action=logout">Log out</a>
      <br />
      Powered by <?= $this->generator; ?>
    </div>
  </div>

  <!-- End Header -->

  <!-- Begin Navbar -->

  <div id="navbar">
    <ul>
      <li><a href="?module=index">Overview</a></li>
      <li><a href="?module=server">Virtual Server Management</a></li>
      <?php if($this->ts3_usesid): ?>
      <li><a href="?module=viewer">Virtual Server Information</a></li>
      <li><a href="?module=prvkey">Privilege Key List</a></li>
      <?php endif; ?>
      <li><a href="?module=syslog">System Logs</a></li>
    </ul>
  </div>

  <!-- End Navbar -->

  <!-- Begin Content -->

  <div id="content">

    <h1><?= $this->title; ?></h1>

    <?php if($this->eventMesg): ?>
    <span class="event <?= $this->eventType; ?>"><?= $this->eventMesg; ?></span>
    <?php endif; ?>

    <div class="container clearfix">
