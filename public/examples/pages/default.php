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

?>

<h1>TeamSpeak 3 PHP Framework Test Page</h1>

<p>
  The TeamSpeak 3 PHP Framework is based on simplicity, object-oriented best practices and a rigorously
  tested agile codebase. Extend the functionality of your servers with scripts or create powerful web
  applications to manage all features of your TeamSpeak 3 Server instances.
</p>

<p>
  Tested. Thoroughly. Enterprise-ready and built with agile methods, the TeamSpeak 3 PHP Framework has been
  unit-tested from the start to ensure that all code remains stable and easy for you to extend, re-test with
  your extensions, and further maintain.
</p>

<h2>What is the purpose of this page?</h2>

<p>
  This is an example of a very simple PHP application using the TeamSpeak 3 PHP Framework. The purpose of
  this example is to demonstrate how the TeamSpeak 3 PHP Framework ties in with an application and how to
  take advantage of its features quickly and easily.
</p>

<p>
  Choose one of the following examples to get started:
</p>

<ul>
  <li><a href="./?page=instanceinfo">Query detailed information about your TeamSpeak 3 Server instance</a></li>
  <li><a href="./?page=serverlist">Get a list of virtual servers running in your TeamSpeak 3 Server instance</a></li>
  <li><a href="./?page=serverinfo">Query detailed information about a specific virtual server</a></li>
  <li><a href="./?page=clientlist">Get a list of clients currently connected to a specific virtual server</a></li>
  <li><a href="./?page=clientinfo">Query detailed information about a specific client</a></li>
  <li><a href="./?page=channellist">Get a list of existing channels on a specific virtual server</a></li>
  <li><a href="./?page=channelinfo">Query detailed information about a specific channel</a></li>
  <li><a href="./?page=groupidentify">Try to identify the most powerful group on a virtual server</a></li>
  <li><a href="./?page=grouppermlist">Query detailed information about a specific group including assigned permissions</a></li>
  <li><a href="./?page=filebrowser">Browse and download files from a virtual server without using the TeamSpeak 3 Client</a></li>
  <li><a href="./?page=updatecheck">Search for users with outdated TeamSpeak 3 Client versions on a virtual server</a></li>
  <li><a href="./?page=clientfilter">Search for users with non-alphanumeric characters in their nickname</a></li>
</ul>

<p>
  In addition, we've prepared the following example applications to show you what the TeamSpeak 3 PHP Framework is
  capable of:
</p>

<ul>
  <li><a href="./apps/viewer/viewer.php">TeamSpeak Web Status Viewer</a></li>
  <li><a href="./apps/webinterface/index.php">TeamSpeak Web Control Panel</a></li>
</ul>

<p>
  All of these pages are generated live using version <b><?= TeamSpeak3::LIB_VERSION; ?></b> of the TeamSpeak 3
  PHP Framework.
</p>
