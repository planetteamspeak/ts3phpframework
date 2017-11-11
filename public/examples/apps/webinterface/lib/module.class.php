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
 * @class TS3WA_Module
 */
abstract class TS3WA_Module
{
  /**
   * Stores the parent TS3WA_Application object.
   *
   * @var TS3WA_Application
   */
  protected $app = null;

  /**
   * Stores the active TS3WA_Layout object.
   *
   * @var TS3WA_Layout
   */
  protected $tpl = null;

  /**
   * Stores the active TeamSpeak3_Node_Abstract object.
   *
   * @var TeamSpeak3_Node_Abstract
   */
  protected $ts3 = null;

  /**
   * Indicates whether a page needs to be rendered for the module.
   *
   * @var boolean
   */
  protected $render = TRUE;

  /**
   * Constructor function.
   *
   * @param  TS3WA_Application $app
   * @return TS3WA_Module
   */
  public function __construct(TS3WA_Application $app)
  {
    $this->app = $app;
    $this->tpl = new TS3WA_Layout();

    $this->init();

    if($this->hasTS3Connection())
    {
      $this->getTS3Connection();

      $this->tpl->ts3_usesid = $this->ts3->serverSelectedId();
      $this->tpl->ts3_useudp = $this->ts3->serverSelectedPort();
      $this->tpl->ts3_whoami = $this->ts3->whoami();
    }
  }

  /**
   * Performs actions to initialize the controller.
   *
   * @return void
   */
  abstract function init();

  /**
   * Checks if the user is authorized and redirects to the login module on errors.
   *
   * @return void
   */
  protected function isAuthorized()
  {
    if(!TeamSpeak3_Helper_Uri::getSessParam("_logintime", 0))
    {
      $this->redirect("login");
    }
  }

  /**
   * Writes a specified error event message to the session.
   *
   * @param  string $mesg
   * @return void
   */
  protected function setLastEvent($mesg, $error = FALSE)
  {
    $_SESSION["_eventmesg"] = $mesg;

    if($error)
    {
      $_SESSION["_eventtype"] = "error";
    }
  }

  /**
   * Loads the TeamSpeak3_Node_Abstract object stored in the current session.
   *
   * @return string
   */
  protected function hasTS3Connection()
  {
    return TeamSpeak3_Helper_Uri::getSessParam("_serverqry") ? TRUE : FALSE;
  }

  /**
   * Loads the TeamSpeak3_Node_Abstract object stored in the current session.
   *
   * @return void
   */
  protected function getTS3Connection()
  {
    $serverqry = TeamSpeak3_Helper_Uri::getSessParam("_serverqry");

    if(!$serverqry)
    {
      throw new Exception("TeamSpeak3_Node_Abstract object does not exist in your current session");
    }

    $this->ts3 = unserialize($serverqry);
  }

  /**
   * Sets the page title.
   *
   * @param  string $title
   * @return void
   */
  protected function assignTitle($title)
  {
    $this->tpl->assign("title", (string) $title);
  }

  /**
   * Assigns available event messages to the template and deletes them from the session.
   *
   * @return void
   */
  protected function assignLastEvent()
  {
    $this->tpl->assign("eventMesg", TeamSpeak3_Helper_Uri::getSessParam("_eventmesg"));
    $this->tpl->assign("eventType", TeamSpeak3_Helper_Uri::getSessParam("_eventtype", "user"));

    unset($_SESSION["_eventmesg"]);
    unset($_SESSION["_eventtype"]);
  }

  /**
   * Redirects the client to a specified module and action.
   *
   * @param  string $module
   * @param  string $action
   * @return void
   */
  protected function redirect($module, $action = "index", $params = array())
  {
    $module = $this->app->getModuleKey() . "=" . $module;
    $action = $action != "index" ? '&' . $this->app->getActionKey() . "=" . $action : "";

    foreach($params as $key => $val)
    {
      $action .= "&" . $key . "=" . $val;
    }

    header("Location: " . TeamSpeak3_Helper_Uri::getBaseUri() . "?" . $module . $action);

    exit;
  }

  /**
   * Renders a page for the module.
   *
   * @return void
   */
  public function dispatch()
  {
    $moduleName = $this->app->getModuleName();
    $actionName = $this->app->getActionName();
    $layoutFile = $moduleName . ($actionName != "index" ? "_" . $actionName : "") . ".tpl.php";

    $this->tpl->render($layoutFile);
  }

  /**
   * Disables rendering for the module.
   *
   * @return void
   */
  protected function setNoRender()
  {
    $this->render = FALSE;
  }

  /**
   * Returns TRUE if a page needs to be rendered.
   *
   * @return boolean
   */
  public function isRender()
  {
    return $this->render;
  }
}
