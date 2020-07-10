<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework
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
 * @package   TeamSpeak3
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) Planet TeamSpeak. All rights reserved.
 */

/**
 * @class TeamSpeak3_Node_Host
 * @brief Class describing a TeamSpeak 3 server instance and all it's parameters.
 */
class TeamSpeak3_Node_Host extends TeamSpeak3_Node_Abstract
{
  /**
   * @ignore
   */
  protected $whoami = null;

  /**
   * @ignore
   */
  protected $version = null;

  /**
   * @ignore
   */
  protected $serverList = null;

  /**
   * @ignore
   */
  protected $permissionEnds = null;

  /**
   * @ignore
   */
  protected $permissionList = null;

  /**
   * @ignore
   */
  protected $permissionCats = null;

  /**
   * @ignore
   */
  protected $predefined_query_name = null;

  /**
   * @ignore
   */
  protected $exclude_query_clients = FALSE;

  /**
   * @ignore
   */
  protected $start_offline_virtual = FALSE;

  /**
   * @ignore
   */
  protected $sort_clients_channels = FALSE;

  /**
   * The TeamSpeak3_Node_Host constructor.
   *
   * @param  TeamSpeak3_Adapter_ServerQuery $squery
   * @return TeamSpeak3_Node_Host
   */
  public function __construct(TeamSpeak3_Adapter_ServerQuery $squery)
  {
    $this->parent = $squery;
  }

  /**
   * Returns the primary ID of the selected virtual server.
   *
   * @return integer
   */
  public function serverSelectedId()
  {
    return $this->whoamiGet("virtualserver_id", 0);
  }

  /**
   * Returns the primary UDP port of the selected virtual server.
   *
   * @return integer
   */
  public function serverSelectedPort()
  {
    return $this->whoamiGet("virtualserver_port", 0);
  }

  /**
   * Returns the servers version information including platform and build number.
   *
   * @param  string $ident
   * @return mixed
   */
  public function version($ident = null)
  {
    if($this->version === null)
    {
      $this->version = $this->request("version")->toList();
    }

    return ($ident && isset($this->version[$ident])) ? $this->version[$ident] : $this->version;
  }

  /**
   * Selects a virtual server by ID to allow further interaction.
   *
   * @param  integer $sid
   * @param  boolean $virtual
   * @todo   remove additional clientupdate call (breaks compatibility with server versions <= 3.4.0)
   * @return void
   */
  public function serverSelect($sid, $virtual = null)
  {
    if($this->whoami !== null && $this->serverSelectedId() == $sid) return;

    $virtual = ($virtual !== null) ? $virtual : $this->start_offline_virtual;
    $getargs = func_get_args();

    if($sid != 0 && $this->predefined_query_name !== null)
    {
      $this->execute("use", array("sid" => $sid, "client_nickname" => (string) $this->predefined_query_name, $virtual ? "-virtual" : null));
    }
    else
    {
      $this->execute("use", array("sid" => $sid, $virtual ? "-virtual" : null));
    }

    $this->whoamiReset();

    if($sid != 0 && $this->predefined_query_name !== null && $this->whoamiGet("client_nickname") != $this->predefined_query_name)
    {
      $this->execute("clientupdate", array("client_nickname" => (string) $this->predefined_query_name));
    }

    $this->setStorage("_server_use", array(__FUNCTION__, $getargs));

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerselected", $this);
  }

  /**
   * Alias for serverSelect().
   *
   * @param  integer $sid
   * @param  boolean $virtual
   * @return void
   */
  public function serverSelectById($sid, $virtual = null)
  {
    $this->serverSelect($sid, $virtual);
  }

  /**
   * Selects a virtual server by UDP port to allow further interaction.
   *
   * @param  integer $port
   * @param  boolean $virtual
   * @todo   remove additional clientupdate call (breaks compatibility with server versions <= 3.4.0)
   * @return void
   */
  public function serverSelectByPort($port, $virtual = null)
  {
    if($this->whoami !== null && $this->serverSelectedPort() == $port) return;

    $virtual = ($virtual !== null) ? $virtual : $this->start_offline_virtual;
    $getargs = func_get_args();

    if($port != 0 && $this->predefined_query_name !== null)
    {
      $this->execute("use", array("port" => $port, "client_nickname" => (string) $this->predefined_query_name, $virtual ? "-virtual" : null));
    }
    else
    {
      $this->execute("use", array("port" => $port, $virtual ? "-virtual" : null));
    }

    $this->whoamiReset();

    if($port != 0 && $this->predefined_query_name !== null && $this->whoamiGet("client_nickname") != $this->predefined_query_name)
    {
      $this->execute("clientupdate", array("client_nickname" => (string) $this->predefined_query_name));
    }

    $this->setStorage("_server_use", array(__FUNCTION__, $getargs));

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerselected", $this);
  }

  /**
   * Deselects the active virtual server.
   *
   * @return void
   */
  public function serverDeselect()
  {
    $this->serverSelect(0);

    $this->delStorage("_server_use");
  }

  /**
   * Returns the ID of a virtual server matching the given port.
   *
   * @param  integer $port
   * @return integer
   */
  public function serverIdGetByPort($port)
  {
    $sid = $this->execute("serveridgetbyport", array("virtualserver_port" => $port))->toList();

    return $sid["server_id"];
  }

  /**
   * Returns the port of a virtual server matching the given ID.
   *
   * @param  integer $sid
   * @return integer
   */
  public function serverGetPortById($sid)
  {
    if(!array_key_exists((string) $sid, $this->serverList()))
    {
      throw new TeamSpeak3_Adapter_ServerQuery_Exception("invalid serverID", 0x400);
    }

    return $this->serverList[intval((string) $sid)]["virtualserver_port"];
  }

  /**
   * Returns the TeamSpeak3_Node_Server object matching the currently selected ID.
   *
   * @return TeamSpeak3_Node_Server
   */
  public function serverGetSelected()
  {
    return $this->serverGetById($this->serverSelectedId());
  }

  /**
   * Returns the TeamSpeak3_Node_Server object matching the given ID.
   *
   * @param  integer $sid
   * @return TeamSpeak3_Node_Server
   */
  public function serverGetById($sid)
  {
    $this->serverSelectById($sid);

    return new TeamSpeak3_Node_Server($this, array("virtualserver_id" => intval($sid)));
  }

  /**
   * Returns the TeamSpeak3_Node_Server object matching the given port number.
   *
   * @param  integer $port
   * @return TeamSpeak3_Node_Server
   */
  public function serverGetByPort($port)
  {
    $this->serverSelectByPort($port);

    return new TeamSpeak3_Node_Server($this, array("virtualserver_id" => $this->serverSelectedId()));
  }

  /**
   * Returns the first TeamSpeak3_Node_Server object matching the given name.
   *
   * @param  string $name
   * @throws TeamSpeak3_Adapter_ServerQuery_Exception
   * @return TeamSpeak3_Node_Server
   */
  public function serverGetByName($name)
  {
    foreach($this->serverList() as $server)
    {
      if($server["virtualserver_name"] == $name) return $server;
    }

    throw new TeamSpeak3_Adapter_ServerQuery_Exception("invalid serverID", 0x400);
  }

  /**
   * Returns the first TeamSpeak3_Node_Server object matching the given unique identifier.
   *
   * @param  string $uid
   * @throws TeamSpeak3_Adapter_ServerQuery_Exception
   * @return TeamSpeak3_Node_Server
   */
  public function serverGetByUid($uid)
  {
    foreach($this->serverList() as $server)
    {
      if($server["virtualserver_unique_identifier"] == $uid) return $server;
    }

    throw new TeamSpeak3_Adapter_ServerQuery_Exception("invalid serverID", 0x400);
  }

  /**
   * Creates a new virtual server using given properties and returns an assoc
   * array containing the new ID and initial admin token.
   *
   * @param  array $properties
   * @return array
   */
  public function serverCreate(array $properties = array())
  {
    $this->serverListReset();

    $detail = $this->execute("servercreate", $properties)->toList();
    $server = new TeamSpeak3_Node_Server($this, array("virtualserver_id" => intval($detail["sid"])));

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServercreated", $this, $detail["sid"]);
    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyTokencreated", $server, $detail["token"]);

    return $detail;
  }

  /**
   * Deletes the virtual server specified by ID.
   *
   * @param  integer $sid
   * @return void
   */
  public function serverDelete($sid)
  {
    if($sid == $this->serverSelectedId())
    {
      $this->serverDeselect();
    }

    $this->execute("serverdelete", array("sid" => $sid));
    $this->serverListReset();

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerdeleted", $this, $sid);
  }

  /**
   * Starts the virtual server specified by ID.
   *
   * @param  integer $sid
   * @return void
   */
  public function serverStart($sid)
  {
    if($sid == $this->serverSelectedId())
    {
      $this->serverDeselect();
    }

    $this->execute("serverstart", array("sid" => $sid));
    $this->serverListReset();

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerstarted", $this, $sid);
  }

  /**
   * Stops the virtual server specified by ID.
   *
   * @param  integer $sid
   * @param  string  $msg
   * @return void
   */
  public function serverStop($sid, $msg = null)
  {
    if($sid == $this->serverSelectedId())
    {
      $this->serverDeselect();
    }

    $this->execute("serverstop", array("sid" => $sid, "reasonmsg" => $msg));
    $this->serverListReset();

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServerstopped", $this, $sid);
  }

  /**
   * Stops the entire TeamSpeak 3 Server instance by shutting down the process.
   *
   * @param  string $msg
   * @return void
   */
  public function serverStopProcess($msg = null)
  {
    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyServershutdown", $this);

    $this->execute("serverprocessstop", array("reasonmsg" => $msg));
  }

  /**
   * Returns an array filled with TeamSpeak3_Node_Server objects.
   *
   * @param  array $filter
   * @return array
   */
  public function serverList(array $filter = array())
  {
    if($this->serverList === null)
    {
      $servers = $this->request("serverlist -uid")->toAssocArray("virtualserver_id");

      $this->serverList = array();

      foreach($servers as $sid => $server)
      {
        $this->serverList[$sid] = new TeamSpeak3_Node_Server($this, $server);
      }

      $this->resetNodeList();
    }

    return $this->filterList($this->serverList, $filter);
  }

  /**
   * Resets the list of virtual servers.
   *
   * @return void
   */
  public function serverListReset()
  {
    $this->resetNodeList();
    $this->serverList = null;
  }

  /**
   * Returns a list of IP addresses used by the server instance on multi-homed machines.
   *
   * @return array
   */
  public function bindingList($subsystem = "voice")
  {
    return $this->execute("bindinglist", array("subsystem" => $subsystem))->toArray();
  }

  /**
   * Returns the number of WebQuery API keys known by the virtual server.
   *
   * @return integer
   */
  public function apiKeyCount()
  {
    return current($this->execute("apikeylist -count", array("duration" => 1))->toList("count"));
  }

  /**
   * Returns a list of WebQuery API keys known by the virtual server. By default, the server spits out 25 entries
   * at once. When no $cldbid is specified, API keys for the invoker are returned. In addition, using '*' as $cldbid
   * will return all known API keys.
   *
   * @param  integer $offset
   * @param  integer $limit
   * @param  mixed   $cldbid
   * @return array
   */
  public function apiKeyList($offset = null, $limit = null, $cldbid = null)
  {
    return $this->execute("apikeylist -count", array("start" => $offset, "duration" => $limit, "cldbid" => $cldbid))->toAssocArray("id");
  }

  /**
   * Creates a new WebQuery API key and returns an assoc array containing its details. Use $lifetime to specify the API
   * key lifetime in days. Setting $lifetime to 0 means the key will be valid forever. $cldbid defaults to the invoker
   * database ID.
   *
   * @param  string  $scope
   * @param  integer $lifetime
   * @param  integer $cldbid
   * @return array
   */
  public function apiKeyCreate($scope = TeamSpeak3::APIKEY_READ, $lifetime = 14, $cldbid = null)
  {
    $detail = $this->execute("apikeyadd", array("scope" => $scope, "lifetime" => $lifetime, "cldbid" => $cldbid))->toList();

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyApikeycreated", $this, $detail["apikey"]);

    return $detail;
  }

  /**
   * Deletes an API key specified by $id.
   *
   * @param  integer $id
   * @return void
   */
  public function apiKeyDelete($id)
  {
    $this->execute("apikeydel", array("id" => $id));
  }

  /**
   * Returns a list of permissions available on the server instance.
   *
   * @return array
   */
  public function permissionList()
  {
    if($this->permissionList === null)
    {
      $this->fetchPermissionList();
    }

    foreach($this->permissionList as $permname => $permdata)
    {
      if(isset($permdata["permcatid"]) && $permdata["permgrant"])
      {
        continue;
      }

      $this->permissionList[$permname]["permcatid"] = $this->permissionGetCategoryById($permdata["permid"]);
      $this->permissionList[$permname]["permgrant"] = $this->permissionGetGrantById($permdata["permid"]);

      $grantsid = "i_needed_modify_power_" . substr($permname, 2);

      if(!$permdata["permname"]->startsWith("i_needed_modify_power_") && !isset($this->permissionList[$grantsid]))
      {
        $this->permissionList[$grantsid]["permid"]    = $this->permissionList[$permname]["permgrant"];
        $this->permissionList[$grantsid]["permname"]  = TeamSpeak3_Helper_String::factory($grantsid);
        $this->permissionList[$grantsid]["permdesc"]  = null;
        $this->permissionList[$grantsid]["permcatid"] = 0xFF;
        $this->permissionList[$grantsid]["permgrant"] = $this->permissionList[$permname]["permgrant"];
      }
    }

    return $this->permissionList;
  }

  /**
   * Returns a list of permission categories available on the server instance.
   *
   * @return array
   */
  public function permissionCats()
  {
    if($this->permissionCats === null)
    {
      $this->fetchPermissionCats();
    }

    return $this->permissionCats;
  }

  /**
   * Returns a list of permission category endings available on the server instance.
   *
   * @return array
   */
  public function permissionEnds()
  {
    if($this->permissionEnds === null)
    {
      $this->fetchPermissionList();
    }

    return $this->permissionCats;
  }

  /**
   * Returns an array filled with all permission categories known to the server including
   * their ID, name and parent.
   *
   * @return array
   */
  public function permissionTree()
  {
    $permtree = array();

    foreach($this->permissionCats() as $key => $val)
    {
      $permtree[$val]["permcatid"]      = $val;
      $permtree[$val]["permcathex"]     = "0x" . dechex($val);
      $permtree[$val]["permcatname"]    = TeamSpeak3_Helper_String::factory(TeamSpeak3_Helper_Convert::permissionCategory($val));
      $permtree[$val]["permcatparent"]  = $permtree[$val]["permcathex"]{3} == 0 ? 0 : hexdec($permtree[$val]["permcathex"]{2} . 0);
      $permtree[$val]["permcatchilren"] = 0;
      $permtree[$val]["permcatcount"]   = 0;

      if(isset($permtree[$permtree[$val]["permcatparent"]]))
      {
        $permtree[$permtree[$val]["permcatparent"]]["permcatchilren"]++;
      }

      if($permtree[$val]["permcatname"]->contains("/"))
      {
        $permtree[$val]["permcatname"] = $permtree[$val]["permcatname"]->section("/", 1)->trim();
      }

      foreach($this->permissionList() as $permission)
      {
        if($permission["permid"]["permcatid"] == $val)
        {
          $permtree[$val]["permcatcount"]++;
        }
      }
    }

    return $permtree;
  }

  /**
   * Returns the IDs of all clients, channels or groups using the permission with the
   * specified ID.
   *
   * @param  integer $permid
   * @return array
   */
  public function permissionFind($permid)
  {
    if(!is_array($permid))
    {
      $permident = (is_numeric($permid)) ? "permid" : "permsid";
    }
    else
    {
      $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
    }

    return $this->execute("permfind", array($permident => $permid))->toArray();
  }

  /**
   * Returns the ID of the permission matching the given name.
   *
   * @param  string $name
   * @throws TeamSpeak3_Adapter_ServerQuery_Exception
   * @return integer
   */
  public function permissionGetIdByName($name)
  {
    if(!array_key_exists((string) $name, $this->permissionList()))
    {
      throw new TeamSpeak3_Adapter_ServerQuery_Exception("invalid permission ID", 0xA02);
    }

    return $this->permissionList[(string) $name]["permid"];
  }

  /**
   * Returns the name of the permission matching the given ID.
   *
   * @param  integer $permid
   * @throws TeamSpeak3_Adapter_ServerQuery_Exception
   * @return TeamSpeak3_Helper_String
   */
  public function permissionGetNameById($permid)
  {
    foreach($this->permissionList() as $name => $perm)
    {
      if($perm["permid"] == $permid) return new TeamSpeak3_Helper_String($name);
    }

    throw new TeamSpeak3_Adapter_ServerQuery_Exception("invalid permission ID", 0xA02);
  }

  /**
   * Returns the internal category of the permission matching the given ID.
   *
   * All pre-3.0.7 permission IDs are are 2 bytes wide. The first byte identifies the category while 
   * the second byte is the permission count within that group.
   *
   * @param  integer $permid
   * @return integer
   */
  public function permissionGetCategoryById($permid)
  {
    if(!is_numeric($permid))
    {
      $permid = $this->permissionGetIdByName($permid);
    }

    if($permid < 0x1000)
    {
      if($this->permissionEnds === null)
      {
        $this->fetchPermissionList();
      }

      if($this->permissionCats === null)
      {
        $this->fetchPermissionCats();
      }

      $catids = array_values($this->permissionCats());

      foreach($this->permissionEnds as $key => $val)
      {
        if($val >= $permid && isset($catids[$key]))
        {
          return $catids[$key];
        }
      }

      return 0;
    }
    else
    {
      return (int) $permid >> 8;
    }
  }

  /**
   * Returns the internal ID of the i_needed_modify_power_* or grant permission.
   *
   * Every permission has an associated i_needed_modify_power_* permission, for example b_client_ban_create has an
   * associated permission called i_needed_modify_power_client_ban_create.
   *
   * @param  integer $permid
   * @return integer
   */
  public function permissionGetGrantById($permid)
  {
    if(!is_numeric($permid))
    {
      $permid = $this->permissionGetIdByName($permid);
    }

    if($permid < 0x1000)
    {
      return (int) $permid+0x8000;
    }
    else
    {
      return (int) bindec(substr(decbin($permid), -8))+0xFF00;
    }
  }

  /**
   * Adds a set of specified permissions to all regular server groups on all virtual servers. The target groups will
   * be identified by the value of their i_group_auto_update_type permission specified with $sgtype.
   *
   * @param  integer $sgtype
   * @param  integer $permid
   * @param  integer $permvalue
   * @param  integer $permnegated
   * @param  integer $permskip
   * @return void
   */
  public function serverGroupPermAutoAssign($sgtype, $permid, $permvalue, $permnegated = FALSE, $permskip = FALSE)
  {
    if(!is_array($permid))
    {
      $permident = (is_numeric($permid)) ? "permid" : "permsid";
    }
    else
    {
      $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
    }

    $this->execute("servergroupautoaddperm", array("sgtype" => $sgtype, $permident => $permid, "permvalue" => $permvalue, "permnegated" => $permnegated, "permskip" => $permskip));
  }

  /**
   * Removes a set of specified permissions from all regular server groups on all virtual servers. The target groups
   * will be identified by the value of their i_group_auto_update_type permission specified with $sgtype.
   *
   * @param  integer $sgtype
   * @param  integer $permid
   * @return void
   */
  public function serverGroupPermAutoRemove($sgtype, $permid)
  {
    if(!is_array($permid))
    {
      $permident = (is_numeric($permid)) ? "permid" : "permsid";
    }
    else
    {
      $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
    }

    $this->execute("servergroupautodelperm", array("sgtype" => $sgtype, $permident => $permid));
  }

  /**
   * Returns an array containing the value of a specified permission for your own client.
   *
   * @param  integer $permid
   * @return array
   */
  public function selfPermCheck($permid)
  {
    if(!is_array($permid))
    {
      $permident = (is_numeric($permid)) ? "permid" : "permsid";
    }
    else
    {
      $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
    }

    return $this->execute("permget", array($permident => $permid))->toAssocArray("permsid");
  }

  /**
   * Changes the server instance configuration using given properties.
   *
   * @param  array $properties
   * @return void
   */
  public function modify(array $properties)
  {
    $this->execute("instanceedit", $properties);
    $this->resetNodeInfo();
  }

  /**
   * Sends a text message to all clients on all virtual servers in the TeamSpeak 3 Server instance.
   *
   * @param  string $msg
   * @return void
   */
  public function message($msg)
  {
    $this->execute("gm", array("msg" => $msg));
  }

  /**
   * Displays a specified number of entries (1-100) from the servers log.
   *
   * @param  integer $lines
   * @param  integer $begin_pos
   * @param  boolean $reverse
   * @param  boolean $instance
   * @return array
   */
  public function logView($lines = 30, $begin_pos = null, $reverse = null, $instance = TRUE)
  {
    return $this->execute("logview", array("lines" => $lines, "begin_pos" => $begin_pos, "instance" => $instance, "reverse" => $reverse))->toArray();
  }

  /**
   * Writes a custom entry into the server instance log.
   *
   * @param  string  $logmsg
   * @param  integer $loglevel
   * @return void
   */
  public function logAdd($logmsg, $loglevel = TeamSpeak3::LOGLEVEL_INFO)
  {
    $sid = $this->serverSelectedId();

    $this->serverDeselect();
    $this->execute("logadd", array("logmsg" => $logmsg, "loglevel" => $loglevel));
    $this->serverSelect($sid);
  }

  /**
   * Authenticates with the TeamSpeak 3 Server instance using given ServerQuery login credentials.
   *
   * @param  string $username
   * @param  string $password
   * @return void
   */
  public function login($username, $password)
  {
    $this->execute("login", array("client_login_name" => $username, "client_login_password" => $password));
    $this->whoamiReset();

    $crypt = new TeamSpeak3_Helper_Crypt($username);

    $this->setStorage("_login_user", $username);
    $this->setStorage("_login_pass", $crypt->encrypt($password));

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyLogin", $this);
  }

  /**
   * Deselects the active virtual server and logs out from the server instance.
   *
   * @return void
   */
  public function logout()
  {
    $this->request("logout");
    $this->whoamiReset();

    $this->delStorage("_login_user");
    $this->delStorage("_login_pass");

    TeamSpeak3_Helper_Signal::getInstance()->emit("notifyLogout", $this);
  }

  /**
   * Returns the number of ServerQuery logins on the selected virtual server.
   *
   * @return integer
   */
  public function queryCountLogin($pattern = null)
  {
    return current($this->execute("queryloginlist -count", array("duration" => 1, "pattern" => $pattern))->toList("count"));
  }

  /**
   * Returns a list of ServerQuery logins on the selected virtual server. By default, the server spits out 25 entries
   * at once.
   *
   * @param  integer $offset
   * @param  integer $limit
   * @return array
   */
  public function queryListLogin($offset = null, $limit = null, $pattern = null)
  {
    return $this->execute("queryloginlist -count", array("start" => $offset, "duration" => $limit, "pattern" => $pattern))->toAssocArray("cldbid");
  }

  /**
   * Creates a new ServerQuery login, or enables ServerQuery logins for an existing client. When no virtual server is
   * selected, the command will create global ServerQuery login. Otherwise a ServerQuery login will be added for an
   * existing client (cldbid must be specified).
   *
   * @param  string  $username
   * @param  integer $cldbid
   * @return array
   */
  public function queryLoginCreate($username, $cldbid = 0)
  {
    if($this->serverSelectedId())
    {
      return $this->execute("queryloginadd", array("client_login_name" => $username, "cldbid" => $cldbid))->toList();
    }
    else
    {
      return $this->execute("queryloginadd", array("client_login_name" => $username))->toList();
    }
  }

  /**
   * Deletes an existing ServerQuery login.
   *
   * @param  integer $cldbid
   * @return void
   */
  public function queryLoginDelete($cldbid)
  {
    $this->execute("querylogindel", array("cldbid" => $cldbid));
  }

  /**
   * Returns information about your current ServerQuery connection.
   *
   * @return array
   */
  public function whoami()
  {
    if($this->whoami === null)
    {
      $this->whoami = $this->request("whoami")->toList();
    }

    return $this->whoami;
  }

  /**
   * Returns a single value from the current ServerQuery connection info.
   *
   * @param  string $ident
   * @param  mixed  $default
   * @return mixed
   */
  public function whoamiGet($ident, $default = null)
  {
    if(array_key_exists($ident, $this->whoami()))
    {
      return $this->whoami[$ident];
    }

    return $default;
  }

  /**
   * Sets a single value in the current ServerQuery connection info.
   *
   * @param  string $ident
   * @param  mixed  $value
   * @return mixed
   */
  public function whoamiSet($ident, $value = null)
  {
    $this->whoami();

    $this->whoami[$ident] = (is_numeric($value)) ? (int) $value : TeamSpeak3_Helper_String::factory($value);
  }

  /**
   * Resets the current ServerQuery connection info.
   *
   * @return void
   */
  public function whoamiReset()
  {
    $this->whoami = null;
  }

  /**
   * Returns the hostname or IPv4 address the adapter is connected to.
   *
   * @return string
   */
  public function getAdapterHost()
  {
    return $this->getParent()->getTransportHost();
  }

  /**
   * Returns the network port the adapter is connected to.
   *
   * @return string
   */
  public function getAdapterPort()
  {
    return $this->getParent()->getTransportPort();
  }

  /**
   * @ignore
   */
  protected function fetchNodeList()
  {
    $servers = $this->serverList();

    foreach($servers as $server)
    {
      $this->nodeList[] = $server;
    }
  }

  /**
   * @ignore
   */
  protected function fetchNodeInfo()
  {
    $info1 = $this->request("hostinfo")->toList();
    $info2 = $this->request("instanceinfo")->toList();

    $this->nodeInfo = array_merge($this->nodeInfo, $info1, $info2);
  }

  /**
   * @ignore
   */
  protected function fetchPermissionList()
  {
    $reply = $this->request("permissionlist -new")->toArray();
    $start = 1;

    $this->permissionEnds = array();
    $this->permissionList = array();

    foreach($reply as $line)
    {
      if(array_key_exists("group_id_end", $line))
      {
        $this->permissionEnds[] = $line["group_id_end"];
      }
      else
      {
        $this->permissionList[$line["permname"]->toString()] = array_merge(array("permid" => $start++), $line);
      }
    }
  }

  /**
   * @ignore
   */
  protected function fetchPermissionCats()
  {
    $permcats = array();
    $reflects = new ReflectionClass("TeamSpeak3");

    foreach($reflects->getConstants() as $key => $val)
    {
      if(!TeamSpeak3_Helper_String::factory($key)->startsWith("PERM_CAT") || $val == 0xFF)
      {
        continue;
      }

      $permcats[$key] = $val;
    }

    $this->permissionCats = $permcats;
  }

  /**
   * Sets a pre-defined nickname for ServerQuery clients which will be used automatically
   * after selecting a virtual server.
   *
   * @param  string $name
   * @return void
   */
  public function setPredefinedQueryName($name = null)
  {
    $this->setStorage("_query_nick", $name);

    $this->predefined_query_name = $name;
  }

  /**
   * Returns the pre-defined nickname for ServerQuery clients which will be used automatically
   * after selecting a virtual server.
   *
   * @return string
   */
  public function getPredefinedQueryName()
  {
    return $this->predefined_query_name;
  }

  /**
   * Sets the option to decide whether ServerQuery clients should be excluded from node
   * lists or not.
   *
   * @param  boolean $exclude
   * @return void
   */
  public function setExcludeQueryClients($exclude = FALSE)
  {
    $this->setStorage("_query_hide", $exclude);

    $this->exclude_query_clients = $exclude;
  }

  /**
   * Returns the option to decide whether ServerQuery clients should be excluded from node
   * lists or not.
   *
   * @return boolean
   */
  public function getExcludeQueryClients()
  {
    return $this->exclude_query_clients;
  }

  /**
   * Sets the option to decide whether offline servers will be started in virtual mode
   * by default or not.
   *
   * @param  boolean $virtual
   * @return void
   */
  public function setUseOfflineAsVirtual($virtual = FALSE)
  {
    $this->setStorage("_do_virtual", $virtual);

    $this->start_offline_virtual = $virtual;
  }

  /**
   * Returns the option to decide whether offline servers will be started in virtual mode
   * by default or not.
   *
   * @return boolean
   */
  public function getUseOfflineAsVirtual()
  {
    return $this->start_offline_virtual;
  }

  /**
   * Sets the option to decide whether clients should be sorted before sub-channels to support
   * the new TeamSpeak 3 Client display mode or not.
   *
   * @param  boolean $first
   * @return void
   */
  public function setLoadClientlistFirst($first = FALSE)
  {
    $this->setStorage("_client_top", $first);

    $this->sort_clients_channels = $first;
  }

  /**
   * Returns the option to decide whether offline servers will be started in virtual mode
   * by default or not.
   *
   * @return boolean
   */
  public function getLoadClientlistFirst()
  {
    return $this->sort_clients_channels;
  }

  /**
   * Returns the underlying TeamSpeak3_Adapter_ServerQuery object.
   *
   * @return TeamSpeak3_Adapter_ServerQuery
   */
  public function getAdapter()
  {
    return $this->getParent();
  }

  /**
   * Returns a unique identifier for the node which can be used as a HTML property.
   *
   * @return string
   */
  public function getUniqueId()
  {
    return "ts3_h";
  }

  /**
   * Returns the name of a possible icon to display the node object.
   *
   * @return string
   */
  public function getIcon()
  {
    return "host";
  }

  /**
   * Returns a symbol representing the node.
   *
   * @return string
   */
  public function getSymbol()
  {
    return "+";
  }

  /**
   * Re-authenticates with the TeamSpeak 3 Server instance using given ServerQuery login
   * credentials and re-selects a previously selected virtual server.
   *
   * @return void
   */
  public function __wakeup()
  {
    $username = $this->getStorage("_login_user");
    $password = $this->getStorage("_login_pass");

    if($username && $password)
    {
      $crypt = new TeamSpeak3_Helper_Crypt($username);

      $this->login($username, $crypt->decrypt($password));
    }

    $this->predefined_query_name = $this->getStorage("_query_nick");
    $this->exclude_query_clients = $this->getStorage("_query_hide", FALSE);
    $this->start_offline_virtual = $this->getStorage("_do_virtual", FALSE);
    $this->sort_clients_channels = $this->getStorage("_client_top", FALSE);

    if($server = $this->getStorage("_server_use"))
    {
      $func = array_shift($server);
      $args = array_shift($server);
      
      try
      {
        call_user_func_array(array($this, $func), $args);
      }
      catch(Exception $e)
      {
        $class = get_class($e);
        
        throw new $class($e->getMessage(), $e->getCode());
      }
    }
  }

  /**
   * Returns a string representation of this node.
   *
   * @return string
   */
  public function __toString()
  {
    return (string) $this->getAdapterHost();
  }
}
