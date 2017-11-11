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
 * @class TS3WA_Application
 */
class TS3WA_Application
{
  /**
   * The module object.
   *
   * @var TS3WA_Module
   */
  protected $module = null;

  /**
   * The module name.
   *
   * @var string
   */
  protected $moduleName = null;

  /**
   * The action name.
   *
   * @var string
   */
  protected $actionName = null;

  /**
   * The module key.
   *
   * @var string
   */
  protected $moduleKey = "module";

  /**
   * The action name.
   *
   * @var string
   */
  protected $actionKey = "action";

  /**
   * Constructor function.
   *
   * @return TS3WA_Application
   */
  public function __construct()
  {
    session_start();

    TeamSpeak3::init();

    $this->moduleName = strtolower(TeamSpeak3_Helper_Uri::getUserParam($this->moduleKey, "index"));
    $this->actionName = strtolower(TeamSpeak3_Helper_Uri::getUserParam($this->actionKey, "index"));

    /* create a global suffix for all custom messages */
    $errorSuffix = "<br /><span class=\"small\"><b>ERROR %code</b>: %mesg</span>";

    /* register custom messages for some TeamSpeak 3 error codes */
    TeamSpeak3_Exception::registerCustomMessage(0x208, "You have entered an invalid username or password." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0x400, "Please select a virtual server to complete your request." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0x409, "Your request could not be processed." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0x40B, "The virtual server needs to be stopped to complete your request." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0x501, "There is no data available for your request." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0xA08, "You are not allowed to access this resource. Please check your client permissions." . $errorSuffix);
    TeamSpeak3_Exception::registerCustomMessage(0xD01, "Your IP address has been banned. Please add your IP address to the ServerQuery whitelist and ensure that you're using the correct login credentials." . $errorSuffix);
  }

  /**
   * Returns the requested module name.
   *
   * @return string
   */
  public function getModuleName()
  {
    return $this->moduleName;
  }

  /**
   * Returns the requested action name.
   *
   * @return string
   */
  public function getActionName()
  {
    return $this->actionName;
  }

  /**
   * Returns the requested module key.
   *
   * @return string
   */
  public function getModuleKey()
  {
    return $this->moduleKey;
  }

  /**
   * Returns the requested action key.
   *
   * @return string
   */
  public function getActionKey()
  {
    return $this->actionKey;
  }

  /**
   * Returns the module object.
   *
   * @return TS3WA_Module
   */
  public function getModule()
  {
    return $this->module;
  }

  /**
   * Returns the instance module path.
   *
   * @return string
   */
  protected function getModulePath()
  {
    return TS3WA_APP . DS . "mod_" . $this->moduleName . ".php";
  }

  /**
   * Returns the instance module class.
   *
   * @return string
   */
  protected function getModuleClass()
  {
    return "TS3WA_Module_" . ucfirst($this->moduleName);
  }

  /**
   * Returns the instance action method.
   *
   * @return string
   */
  protected function getActionMethod()
  {
    return $this->actionName . "Action";
  }

  /**
   * Starts the application by loading the requested module and action.
   *
   * @return void
   */
  public function run()
  {
    $moduleFile = $this->getModulePath();
    $moduleName = $this->getModuleClass();

    /* load required /app/mod_{$this->moduleName}.php file */
    if(!file_exists($moduleFile) || !is_readable($moduleFile))
    {
      throw new Exception("Module resource not found (" . $this->getModuleName() . ")", 404);
    }
    else
    {
      require_once($moduleFile);
    }

    /* check if required module class exists */
    if(!class_exists($moduleName) || !in_array("TS3WA_Module", class_parents($moduleName)))
    {
      throw new Exception("Module resource not found (" . $this->getModuleName() . ")", 404);
    }

    /* spawn new module */
    $this->module = new $moduleName($this);

    /* check if required action method exists */
    if(!method_exists($this->module, $this->getActionMethod()))
    {
      throw new Exception("Action resource not found (" . $this->getActionName() . ")", 404);
    }

    /* call required action method */
    call_user_func(array($this->module, $this->getActionMethod()));

    /* render template */
    if($this->module->isRender())
    {
      $this->module->dispatch();
    }
  }
}
