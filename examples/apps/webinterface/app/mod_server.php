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

/**
 * @class TS3WA_Module_Server
 */
class TS3WA_Module_Server extends TS3WA_Module
{
  /**
   * Performs actions to initialize the controller.
   *
   * @return void
   */
  public function init()
  {
    $this->isAuthorized();
    $this->assignTitle("Virtual Server Management");
  }

  /**
   * Controller action: index
   *
   * @return void
   */
  public function indexAction()
  {
    try
    {
      /* query list of virtual servers */
      $this->tpl->ts3_servers = $this->ts3->serverList();
    }
    catch(Exception $e)
    {
      /* fallback to default values */
      $this->tpl->ts3_servers = array();

      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    $this->assignLastEvent();
  }

  /**
   * Controller action: select
   *
   * @return void
   */
  public function selectAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      /* start server */
      $this->ts3->serverSelect($sid);

      /* update session variables */
      $_SESSION["_serverqry"] = serialize($this->ts3);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $sid . ") has been selected.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    /* goto indexAction() */
    $this->redirect("server");
  }

  /**
   * Controller action: start
   *
   * @return void
   */
  public function startAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      /* start server */
      $this->ts3->serverStart($sid);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $sid . ") has been started.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    /* goto indexAction() */
    $this->redirect("server");
  }

  /**
   * Controller action: stop
   *
   * @return void
   */
  public function stopAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      /* stop server */
      $this->ts3->serverStop($sid);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $sid . ") has been stopped.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    /* goto indexAction() */
    $this->redirect("server");
  }

  /**
   * Controller action: export
   *
   * @return void
   */
  public function exportAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      /* create snapshot and return data as base64 encoded string */
      $data = $this->ts3->serverGetById($sid)->snapshotCreate(TeamSpeak3::SNAPSHOT_BASE64);
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* goto indexAction() */
      $this->redirect("server");
    }

    /* stream snapshot data to browser */
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=" . $this->ts3->getAdapterHost() . "_" . $this->ts3->getAdapterPort() . "-virtualserver_" . $sid . ".snapshot");
    header("Content-Transfer-Encoding: binary");

    /* send output */
    echo chunk_split($data);
  }

  /**
   * Controller action: create
   *
   * @return void
   */
  public function createAction()
  {
    $this->assignTitle("Create Virtual Server");

    /* assign some default property values */
    $this->tpl->defaultServerName = "TeamSpeak ]|[ Server";
    $this->tpl->defaultMaxClients = 32;
    $this->tpl->defaultWelcomeMsg = "[B]Welcome to this TeamSpeak Server, please check [URL]www.teamspeak.com[/URL].[/B]";

    $this->assignLastEvent();
  }

  /**
   * Controller action: docreate
   *
   * @return void
   */
  public function docreateAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $arr = TeamSpeak3_Helper_Uri::getUserParam("props", array());

    /* remove empty virtualserver_port element from properties to auto-select port */
    if(array_key_exists("virtualserver_port", $arr) && empty($arr["virtualserver_port"]))
    {
      unset($arr["virtualserver_port"]);
    }

    try
    {
      /* create server using given props */
      $server = $this->ts3->serverCreate($arr);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $server["sid"] . ") has been created on port " . $server["virtualserver_port"] . ". The initial privilege key is: " . $server["token"]);
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* goto createAction() */
      $this->redirect("server", "create");
    }

    /* goto indexAction() */
    $this->redirect("server");
  }

  /**
   * Controller action: modify
   *
   * @return void
   */
  public function modifyAction()
  {
    $this->assignTitle("Modify Virtual Server");

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      $icons = array();

      try
      {
        /* init file transfer for each icon available */
        foreach($this->ts3->serverGetById($sid)->channelFileList(0, "", "/icons") as $ftid => $file)
        {
          $icons[$file["name"]->section("_", 1)->toString()] = base64_encode(serialize($this->ts3->serverGetById($sid)->transferInitDownload($ftid, 0, $file["src"])));
        }
      }
      catch(Exception $e)
      {}

      /* query virtual server properties and assign icons list */
      $this->tpl->ts3_props = $this->ts3->serverGetById($sid)->getInfo(TRUE);
      $this->tpl->ts3_icons = $icons;
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* goto indexAction() */
      $this->redirect("server");
    }

    /* assign some default property values */
    $this->tpl->defaultPassword = "{keep_password}";

    $this->assignLastEvent();
  }

  /**
   * Controller action: domodify
   *
   * @return void
   */
  public function domodifyAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);
    $arr = TeamSpeak3_Helper_Uri::getUserParam("props", array());

    /* remove virtualserver_password element from properties if it contains the deault value */
    if(array_key_exists("virtualserver_password", $arr) && $arr["virtualserver_password"] == "{keep_password}")
    {
      unset($arr["virtualserver_password"]);
    }

    try
    {
      /* modify server properties */
      $this->ts3->serverGetById($sid)->modify($arr);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $sid . ") has been modified.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* goto modifyAction() */
      $this->redirect("server", "modify", array("id" => $sid));
    }

    /* goto indexAction() */
    $this->redirect("server");
  }

  /**
   * Controller action: delete
   *
   * @return void
   */
  public function deleteAction()
  {
    $this->assignTitle("Delete Virtual Server");

    /* grab user parameters from $_REQUEST array */
    $this->tpl->ts3_sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);
  }

  /**
   * Controller action: dodelete
   *
   * @return void
   */
  public function dodeleteAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $sid = TeamSpeak3_Helper_Uri::getUserParam("id", 0);

    try
    {
      /* check if deleted server was selected */
      if($this->ts3->serverSelectedId() == $sid)
      {
        /* deselect server */
        $this->ts3->serverDeselect();
      }

      /* delete server */
      $this->ts3->serverDelete($sid);

      /* update session variables */
      $_SESSION["_serverqry"] = serialize($this->ts3);

      /* save custom ok mesage */
      $this->setLastEvent("The virtual server (ID " . $sid . ") has been deleted.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    /* goto indexAction() */
    $this->redirect("server");
  }
}
