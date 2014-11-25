<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework Example :: viewer
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
 * @package   viewer
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/* set error reporting levels */
error_reporting(E_ALL | E_STRICT);

/* set default timezone */
date_default_timezone_set("Europe/Berlin");

/* load config file */
require_once("../../config.php");

/* load framework library */
require_once("../../../libraries/TeamSpeak3/TeamSpeak3.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>

  <title>TeamSpeak Web Status Viewer</title>

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
      font-family: Tahoma, Helvetica, Arial, sans-serif;
      font-size: 12px;
      line-height: 1.25em;
    }

    h1 {
      color: #3D6A95;
      font-size: 2em;
      margin-bottom: 1em;
      font-weight:normal;
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

    div.footer {
      font-size: 8pt;
      color: #999;
    }

    table.ts3_viewer {
      width: 100%;
      border: 0px;
      border-collapse: collapse;
    }

    table.ts3_viewer tr.row1 {
      background: transparent;
    }

    table.ts3_viewer tr.row2 {
      background: #F9F9F9;
    }

    table.ts3_viewer td {
      white-space: nowrap;
      padding: 0px 0px 1px 0px;
      border: 0px;
    }

    table.ts3_viewer td.corpus {
      width: 100%;
    }

    table.ts3_viewer td.query {
      font-style: italic;
      color: #666E73;
    }

    table.ts3_viewer td.spacer {
      overflow: hidden;
    }

    table.ts3_viewer td.left {
      text-align: left;
    }

    table.ts3_viewer td.right {
      text-align: right;
    }

    table.ts3_viewer td.center {
      text-align: center;
    }

    table.ts3_viewer td.suffix {
      vertical-align: top;
    }

    table.ts3_viewer td.suffix img {
      padding-left: 2px;
      vertical-align: top;
    }

    table.ts3_viewer td.spacer.solidline {
      background: url('../../../images/viewer/spacer_solidline.gif') repeat-x;
    }

    table.ts3_viewer td.spacer.dashline {
      background: url('../../../images/viewer/spacer_dashline.gif') repeat-x;
    }

    table.ts3_viewer td.spacer.dashdotline {
      background: url('../../../images/viewer/spacer_dashdotline.gif') repeat-x;
    }

    table.ts3_viewer td.spacer.dashdotdotline {
      background: url('../../../images/viewer/spacer_dashdotdotline.gif') repeat-x;
    }

    table.ts3_viewer td.spacer.dotline {
      background: url('../../../images/viewer/spacer_dotline.gif') repeat-x;
    }

    span.success {
      color: #648434;
    }

    span.error {
      color: #CF3738;
    }
  -->
  </style>

</head>

<body>

<h1>TeamSpeak Web Status Viewer</h1>

<?php

try
{
  /* connect to server, authenticate and get TeamSpeak3_Node_Server object by URI */
  $ts3 = TeamSpeak3::factory("serverquery://" . $cfg["user"] . ":" . $cfg["pass"] . "@" . $cfg["host"] . ":" . $cfg["query"] . "/?server_port=" . $cfg["voice"] . "#no_query_clients");

  /* enable new display mode */
  $ts3->setLoadClientlistFirst(TRUE);

  /* display viewer for selected TeamSpeak3_Node_Server */
  echo $ts3->getViewer(new TeamSpeak3_Viewer_Html("../../../images/viewer/", "../../../images/flags/", "data:image"));

  /* display runtime from adapter profiler */
  echo "<p>Executed " . $ts3->getAdapter()->getQueryCount() . " queries in " . $ts3->getAdapter()->getQueryRuntime() . " seconds</p>\n";
}
catch(Exception $e)
{
  /* echo error message */
  echo "<p><span class=\"error\"><b>ERROR 0x" . dechex($e->getCode()) . "</b>: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

?>

<div class="footer">
  Powered by TeamSpeak 3 PHP Framework <?= TeamSpeak3::LIB_VERSION; ?>
  <br />
  &copy; <?= date("Y"); ?> Planet TeamSpeak. All rights reserved.
</div>

</body>

</html>