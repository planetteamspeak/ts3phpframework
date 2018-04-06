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
 * @class TS3WA_Module_Login
 */
class TS3WA_Module_Login extends TS3WA_Module
{
  /**
   * Performs actions to initialize the controller.
   *
   * @return void
   */
  public function init()
  {
    $this->assignTitle("Login");
  }

  /**
   * Controller action: index
   *
   * @return void
   */
  public function indexAction()
  {
    $this->assignLastEvent();

    /* assign some default values */
    $this->tpl->defaultPort = 10011;
  }

  /**
   * Controller action: login
   *
   * @return void
   */
  public function loginAction()
  {
    $this->setNoRender();

    /* grab user parameters from $_REQUEST array */
    $host = TeamSpeak3_Helper_Uri::getUserParam("hostaddr");
    $port = TeamSpeak3_Helper_Uri::getUserParam("hostport");
    $user = TeamSpeak3_Helper_Uri::getUserParam("username");
    $pass = TeamSpeak3_Helper_Uri::getUserParam("password");

    try
    {
      /* fallback to default values */
      if(empty($host)) throw new Exception("Please enter a valid server address.");
      if(empty($port)) throw new Exception("Please enter a valid server port.");
      if(empty($user)) throw new Exception("Please enter a valid username.");
      if(empty($pass)) throw new Exception("Please enter a valid password.");

      /* validate host address */
      if(ip2long($host) === FALSE)
      {
        $addr = gethostbyname($host);

        if($addr == $host)
        {
          throw new Exception("Unable to resolve IPv4 address for hostname '" . $host. "'.");
        }

        $host = $addr;
      }

      /* set custom ServerQuery nickname and connect to server */
      $displayname = rawurlencode("TS3 PHP Framework Demo #" . mt_rand(1, 999999));
      $serverquery = TeamSpeak3::factory("serverquery://" . $user . ":" . $pass . "@" . $host . ":" . $port . "/?nickname=" . $displayname . "#use_offline_as_virtual");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* goto loginAction() */
      $this->redirect("login");
    }

    /* write session variables */
    $_SESSION["_logintime"] = time();
    $_SESSION["_serverqry"] = serialize($serverquery);
    $_SESSION["_loginname"] = $user;

    /* goto indexAction() */
    $this->redirect("index");
  }

  /**
   * Controller action: login
   *
   * @return void
   */
  public function logoutAction()
  {
    $this->setNoRender();

    /* kill session */
    session_destroy();

    /* goto loginAction() */
    $this->redirect("login");
  }
}
