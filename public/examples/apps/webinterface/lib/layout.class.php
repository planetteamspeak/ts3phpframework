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
 * @class TS3WA_Layout
 */
class TS3WA_Layout
{
  /**
   * Stores assigned template variables.
   *
   * @var array
   */
  private $_vars = array();

  /**
   * Constructor function.
   *
   * @return TS3WA_Layout
   */
  function __construct()
  {
    $this->assign("loginname", TeamSpeak3_Helper_Uri::getSessParam("_loginname", "Guest"));
    $this->assign("generator", "TeamSpeak 3 PHP Framework " . TeamSpeak3::LIB_VERSION);
    $this->assign("copyright", "&copy; " . date("Y") . " by Planet TeamSpeak. All rights reserved.");
  }

  /**
   * Includes a template script in a scope with only $this variables.
   *
   * @param  string $file
   * @return void
   */
  function render($file)
  {
    if(!file_exists(TS3WA_TPL . DS . $file) || !is_readable(TS3WA_TPL . DS . $file))
    {
      throw new Exception("Layout resource not found (" . $file . ")", 404);
    }

    require_once(TS3WA_TPL . DS . $file);
  }

  /**
   * Assigns a new template variable.
   *
   * @param string $key
   * @param mixed  $val
   */
  function assign($key, $val)
  {
    $this->_vars[$key] = $val;
  }

  /**
   * Assigns a new template variable.
   *
   * @param string $key
   * @param mixed  $val
   */
  public function __set($key, $val)
  {
    $this->assign($key, $val);
  }

  /**
   * Returns an existing template variable.
   *
   * @param  string $key
   * @return mixed
   */
  public function __get($key)
  {
    return array_key_exists($key, $this->_vars) ? $this->_vars[$key] : null;
  }

  /**
   * Deletes an existing template variable.
   *
   * @param  string $key
   * @return void
   */
  public function __unset($key)
  {
    unset($this->_vars[$key]);
  }
}
