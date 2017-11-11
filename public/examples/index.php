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

/* set error reporting levels */
error_reporting(E_ALL | E_STRICT);

/* set default timezone */
date_default_timezone_set("Europe/Berlin");

/* load required files */
require_once("config.php");
require_once("globals.php");

/* load framework library */
require_once("../libraries/TeamSpeak3/TeamSpeak3.php");

/* initialize */
TeamSpeak3::init();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>

  <title>TeamSpeak 3 PHP Framework Test Page</title>

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="pragma" content="no-cache" />
  <meta http-equiv="expires" content="-1" />

  <meta name="robots" content="noindex, nofollow" />
  <meta name="generator" content="TeamSpeak 3 PHP Framework <?= TeamSpeak3::LIB_VERSION; ?>" />
  <meta name="resource-type" content="Document" />

  <style type="text/css" media="screen">
  <!--
    body {
      margin: 0px auto;
      font-family: Tahoma, Helvetica, Arial, sans-serif;
      background-color: #0C2A4C;
      font-size: 12px;
      line-height: 1.25em;
      width: 980px;
    }

    h1 {
      color: #3D6A95;
      font-size: 2em;
      margin-bottom: 1em;
      font-weight:normal;
    }

    h2 {
      color: #3D6A95;
      font-size: 1.2em;
      margin-bottom: 1em;
    }

    img {
      border: 0px;
    }

    a {
      color: #0088B5;
      text-decoration: underline;
    }

    a:hover {
      color: #FE6400;
      text-decoration: none;
    }

    div.body-container {
      padding: 30px 50px;
      margin-top: 10px;
      border: 6px solid #F4B57C;
      background-color: #fff;
    }

    div.logo-container {
      clear: both;
    }

    div.text-container {
      clear: both;
      padding: 10px 35px;
    }

    table.list {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #DADFE0;
    }

    table.list th {
      text-align: left;
      border: 1px solid #DADFE0;
      padding: 2px 5px;
      color: #6A80A6;
      background-color: #F3F4F8;
      white-space: nowrap;
    }

    table.list td {
      text-align: left;
      border: 1px solid #DADFE0;
      padding: 4px 5px;
      vertical-align: top;
      font-size: 0.95em;
    }

    span.error {
      padding: 8px;
      display: block;
      margin: 1em 0;
      border: 1px solid #FFACAD;
      background-color: #FFD5D5;
      color: #CF3738;
    }

    .red {
      color: red;
    }

    .orange {
      color: orange;
    }

    .green {
      color: green;
    }

    .pre {
      font-family: 'Courier New', Monospace;
      font-size: 1.25em;
    }

    .small {
      font-size: .85em;
      color: #8D8D8D;
    }

    div.footer {
      text-align: center;
      padding: 10px;
      font-size: 8pt;
      color: #fff;
    }
  -->
  </style>

</head>

<body>

  <div class="body-container">

    <div class="logo-container">
      <img src="media/logo.png" style="float: left;" alt="" />
      <a href="http://abetterbrowser.org/" target="_blank"><img src="media/noie.png" style="float: right;" alt="" /></a>
    </div>

    <div class="text-container">

      <?php

      /* set name for requested page */
      $page = (isset($_GET["page"]) && !empty($_GET["page"])) ? preg_replace("/[^[:alnum:]_-]/", "", $_GET["page"]) : "default";

      /* load page if source file exists */
      if(file_exists("./pages/" . $page . ".php"))
      {
        include_once("./pages/" . $page . ".php");
      }
      else
      {
        echo "<span class='error'><b>Error 404:</b> page not found</span>\n";
      }

      /* display back link if necessary */
      if($page !== "default") echo "<p><a href='./'>Go Back</a></p>\n";

      ?>

    </div>
  </div>

  <div class="footer">
    &copy; 2008-<?= date('Y'); ?> Planet TeamSpeak. All rights reserved.<br />
    All trademarks referenced herein are the properties of their respective owners.
	</div>

</body>

</html>