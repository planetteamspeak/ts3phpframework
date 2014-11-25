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
 * @class TS3WA_Module_Prvkey
 */
class TS3WA_Module_Prvkey extends TS3WA_Module
{
  /**
   * Performs actions to initialize the controller.
   *
   * @return void
   */
  public function init()
  {
    $this->isAuthorized();
    $this->assignTitle("Privilege Key List");
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
      /* query existing privilege keys */
      $this->tpl->ts3_prvkeys = $this->ts3->serverGetSelected()->privilegeKeyList(TRUE);
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);

      /* fallback to default values */
      $this->tpl->ts3_prvkeys = array();
    }

    $this->assignLastEvent();
  }

  /**
   * Controller action: create
   *
   * @return void
   */
  public function createAction()
  {
    $this->setNoRender();

    try
    {
      /* identify most powerful server group and create a token for it */
      $this->ts3->serverGetSelected()->serverGroupIdentify()->privilegeKeyCreate("additional serveradmin privilege key");

      /* save custom ok mesage */
      $this->setLastEvent("The additional serveradmin privilege key has been created.");
    }
    catch(Exception $e)
    {
      /* save catched error mesage */
      $this->setLastEvent($e->getMessage(), TRUE);
    }

    /* goto indexAction() */
    $this->redirect("prvkey");
  }
}
