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

namespace PlanetTeamSpeak\TeamSpeak3Framework\Node;

use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Crypt;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use ReflectionClass;

/**
 * Class Host
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @class Host
 * @brief Class describing a TeamSpeak 3 server instance and all it's parameters.
 */
class Host extends Node
{
    /**
     * @ignore
     */
    protected array|null $whoami = null;

    /**
     * @ignore
     */
    protected array|null $version = null;

    /**
     * @ignore
     */
    protected array|null $serverList = null;

    /**
     * @ignore
     */
    protected array|null $permissionEnds = null;

    /**
     * @ignore
     */
    protected array|null $permissionList = null;

    /**
     * @ignore
     */
    protected array|null $permissionCats = null;

    /**
     * @ignore
     */
    protected string|null $predefined_query_name = null;

    /**
     * @ignore
     */
    protected bool $exclude_query_clients = false;

    /**
     * @ignore
     */
    protected bool $start_offline_virtual = false;

    /**
     * @ignore
     */
    protected bool $sort_clients_channels = false;

    /**
     * The PlanetTeamSpeak\TeamSpeak3Framework\Node\Host constructor.
     *
     * @param ServerQuery $squery
     */
    public function __construct(ServerQuery $squery)
    {
        $this->parent = $squery;
    }

    /**
     * Returns the primary ID of the selected virtual server.
     *
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverSelectedId(): int
    {
        return $this->whoamiGet("virtualserver_id", 0);
    }

    /**
     * Returns the primary UDP port of the selected virtual server.
     *
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverSelectedPort(): int
    {
        return $this->whoamiGet("virtualserver_port", 0);
    }

    /**
     * Returns the servers version information including platform and build number.
     *
     * @param string|null $ident
     * @return mixed
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function version(string $ident = null): mixed
    {
        if ($this->version === null) {
            $this->version = $this->request("version")->toList();
        }

        return ($ident && isset($this->version[$ident])) ? $this->version[$ident] : $this->version;
    }

    /**
     * Selects a virtual server by ID to allow further interaction.
     * todo   remove additional clientupdate call (breaks compatibility with server versions <= 3.4.0)
     *
     * @param integer $sid
     * @param boolean|null $virtual
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverSelect(int $sid, bool $virtual = null): void
    {
        if ($this->whoami !== null && $this->serverSelectedId() == $sid) {
            return;
        }

        $virtual = ($virtual !== null) ? $virtual : $this->start_offline_virtual;
        $getargs = func_get_args();

        if ($sid != 0 && $this->predefined_query_name !== null) {
            $this->execute("use", ["sid" => $sid, "client_nickname" => (string)$this->predefined_query_name, $virtual ? "-virtual" : null]);
        } else {
            $this->execute("use", ["sid" => $sid, $virtual ? "-virtual" : null]);
        }

        $this->whoamiReset();

        if ($sid != 0 && $this->predefined_query_name !== null && $this->whoamiGet("client_nickname") != $this->predefined_query_name) {
            $this->execute("clientupdate", ["client_nickname" => (string)$this->predefined_query_name]);
        }

        $this->setStorage("_server_use", [__FUNCTION__, $getargs]);

        Signal::getInstance()->emit("notifyServerselected", $this);
    }

    /**
     * Alias for serverSelect().
     *
     * @param integer $sid
     * @param boolean|null $virtual
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverSelectById(int $sid, bool $virtual = null): void
    {
        $this->serverSelect($sid, $virtual);
    }

    /**
     * Selects a virtual server by UDP port to allow further interaction.
     *
     * @param integer $port
     * @param boolean|null $virtual
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     * @todo   remove additional clientupdate call (breaks compatibility with server versions <= 3.4.0)
     */
    public function serverSelectByPort(int $port, bool $virtual = null): void
    {
        if ($this->whoami !== null && $this->serverSelectedPort() == $port) {
            return;
        }

        $virtual = ($virtual !== null) ? $virtual : $this->start_offline_virtual;
        $getargs = func_get_args();

        if ($port != 0 && $this->predefined_query_name !== null) {
            $this->execute("use", ["port" => $port, "client_nickname" => (string)$this->predefined_query_name, $virtual ? "-virtual" : null]);
        } else {
            $this->execute("use", ["port" => $port, $virtual ? "-virtual" : null]);
        }

        $this->whoamiReset();

        if ($port != 0 && $this->predefined_query_name !== null && $this->whoamiGet("client_nickname") != $this->predefined_query_name) {
            $this->execute("clientupdate", ["client_nickname" => (string)$this->predefined_query_name]);
        }

        $this->setStorage("_server_use", [__FUNCTION__, $getargs]);

        Signal::getInstance()->emit("notifyServerselected", $this);
    }

    /**
     * Deselects the active virtual server.
     *
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverDeselect(): void
    {
        $this->serverSelect(0);

        $this->delStorage("_server_use");
    }

    /**
     * Returns the ID of a virtual server matching the given port.
     *
     * @param integer $port
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverIdGetByPort(int $port): int
    {
        $sid = $this->execute("serveridgetbyport", ["virtualserver_port" => $port])->toList();

        return $sid["server_id"];
    }

    /**
     * Returns the port of a virtual server matching the given ID.
     *
     * @param integer $sid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetPortById(int $sid): int
    {
        if (!array_key_exists((string)$sid, $this->serverList())) {
            throw new ServerQueryException("invalid serverID", 0x400);
        }

        return $this->serverList[intval((string)$sid)]["virtualserver_port"];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Server object matching the currently selected ID.
     *
     * @return Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetSelected(): Server
    {
        return $this->serverGetById($this->serverSelectedId());
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Server object matching the given ID.
     *
     * @param integer $sid
     * @return Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetById(int $sid): Server
    {
        $this->serverSelectById($sid);

        return new Server($this, ["virtualserver_id" => $sid]);
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Server object matching the given port number.
     *
     * @param integer $port
     * @return Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetByPort(int $port): Server
    {
        $this->serverSelectByPort($port);

        return new Server($this, ["virtualserver_id" => $this->serverSelectedId()]);
    }

    /**
     * Returns the first PlanetTeamSpeak\TeamSpeak3Framework\Node\Server object matching the given name.
     *
     * @param string $name
     * @return Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetByName(string $name): Server
    {
        foreach ($this->serverList() as $server) {
            if ($server["virtualserver_name"] == $name) {
                return $server;
            }
        }

        throw new ServerQueryException("invalid serverID", 0x400);
    }

    /**
     * Returns the first PlanetTeamSpeak\TeamSpeak3Framework\Node\Server object matching the given unique identifier.
     *
     * @param string $uid
     * @return Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGetByUid(string $uid): Server
    {
        foreach ($this->serverList() as $server) {
            if ($server["virtualserver_unique_identifier"] == $uid) {
                return $server;
            }
        }

        throw new ServerQueryException("invalid serverID", 0x400);
    }

    /**
     * Creates a new virtual server using given properties and returns an assoc
     * array containing the new ID and initial admin token.
     *
     * @param array $properties
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverCreate(array $properties = []): array
    {
        $this->serverListReset();

        $detail = $this->execute("servercreate", $properties)->toList();
        $server = new Server($this, ["virtualserver_id" => intval($detail["sid"])]);

        Signal::getInstance()->emit("notifyServercreated", $this, $detail["sid"]);
        Signal::getInstance()->emit("notifyTokencreated", $server, $detail["token"]);

        return $detail;
    }

    /**
     * Deletes the virtual server specified by ID.
     *
     * @param integer $sid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverDelete(int $sid): void
    {
        if ($sid == $this->serverSelectedId()) {
            $this->serverDeselect();
        }

        $this->execute("serverdelete", ["sid" => $sid]);
        $this->serverListReset();

        Signal::getInstance()->emit("notifyServerdeleted", $this, $sid);
    }

    /**
     * Starts the virtual server specified by ID.
     *
     * @param integer $sid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverStart(int $sid): void
    {
        if ($sid == $this->serverSelectedId()) {
            $this->serverDeselect();
        }

        $this->execute("serverstart", ["sid" => $sid]);
        $this->serverListReset();

        Signal::getInstance()->emit("notifyServerstarted", $this, $sid);
    }

    /**
     * Stops the virtual server specified by ID.
     *
     * @param integer $sid
     * @param string|null $msg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverStop(int $sid, string $msg = null): void
    {
        if ($sid == $this->serverSelectedId()) {
            $this->serverDeselect();
        }

        $this->execute("serverstop", ["sid" => $sid, "reasonmsg" => $msg]);
        $this->serverListReset();

        Signal::getInstance()->emit("notifyServerstopped", $this, $sid);
    }

    /**
     * Stops the entire TeamSpeak 3 Server instance by shutting down the process.
     *
     * @param string|null $msg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverStopProcess(string $msg = null): void
    {
        Signal::getInstance()->emit("notifyServershutdown", $this);

        $this->execute("serverprocessstop", ["reasonmsg" => $msg]);
    }

    /**
     * Returns an array filled with PlanetTeamSpeak\TeamSpeak3Framework\Node\Server objects.
     *
     * @param array $filter
     * @return array|Server
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverList(array $filter = []): array|Server
    {
        if ($this->serverList === null) {
            $servers = $this->request("serverlist -uid")->toAssocArray("virtualserver_id");

            $this->serverList = [];

            foreach ($servers as $sid => $server) {
                $this->serverList[$sid] = new Server($this, $server);
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
    public function serverListReset(): void
    {
        $this->resetNodeList();
        $this->serverList = null;
    }

    /**
     * Returns a list of IP addresses used by the server instance on multi-homed machines.
     *
     * @param string $subsystem
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function bindingList(string $subsystem = "voice"): array
    {
        return $this->execute("bindinglist", ["subsystem" => $subsystem])->toArray();
    }

    /**
     * Returns the number of WebQuery API keys known by the virtual server.
     *
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function apiKeyCount(): int
    {
        return current($this->execute("apikeylist -count", ["duration" => 1])->toList("count"));
    }

    /**
     * Returns a list of WebQuery API keys known by the virtual server. By default, the server spits out 25 entries
     * at once. When no $cldbid is specified, API keys for the invoker are returned. In addition, using '*' as $cldbid
     * will return all known API keys.
     *
     * @param integer|null $offset
     * @param integer|null $limit
     * @param mixed|null $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function apiKeyList(int $offset = null, int $limit = null, mixed $cldbid = null): array
    {
        return $this->execute("apikeylist -count", ["start" => $offset, "duration" => $limit, "cldbid" => $cldbid])->toAssocArray("id");
    }

    /**
     * Creates a new WebQuery API key and returns an assoc array containing its details. Use $lifetime to specify the API
     * key lifetime in days. Setting $lifetime to 0 means the key will be valid forever. $cldbid defaults to the invoker
     * database ID.
     *
     * @param string $scope
     * @param integer $lifetime
     * @param integer|null $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function apiKeyCreate(string $scope = TeamSpeak3::APIKEY_READ, int $lifetime = 14, int $cldbid = null): array
    {
        $detail = $this->execute("apikeyadd", ["scope" => $scope, "lifetime" => $lifetime, "cldbid" => $cldbid])->toList();

        Signal::getInstance()->emit("notifyApikeycreated", $this, $detail["apikey"]);

        return $detail;
    }

    /**
     * Deletes an API key specified by $id.
     *
     * @param integer $id
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function apiKeyDelete(int $id): void
    {
        $this->execute("apikeydel", ["id" => $id]);
    }

    /**
     * Returns a list of permissions available on the server instance.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionList(): array
    {
        if ($this->permissionList === null) {
            $this->fetchPermissionList();
        }

        foreach ($this->permissionList as $permname => $permdata) {
            if (isset($permdata["permcatid"]) && $permdata["permgrant"]) {
                continue;
            }

            $this->permissionList[$permname]["permcatid"] = $this->permissionGetCategoryById($permdata["permid"]);
            $this->permissionList[$permname]["permgrant"] = $this->permissionGetGrantById($permdata["permid"]);

            $grantsid = "i_needed_modify_power_" . substr($permname, 2);

            if (!$permdata["permname"]->startsWith("i_needed_modify_power_") && !isset($this->permissionList[$grantsid])) {
                $this->permissionList[$grantsid]["permid"] = $this->permissionList[$permname]["permgrant"];
                $this->permissionList[$grantsid]["permname"] = StringHelper::factory($grantsid);
                $this->permissionList[$grantsid]["permdesc"] = null;
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
    public function permissionCats(): array
    {
        if ($this->permissionCats === null) {
            $this->fetchPermissionCats();
        }

        return $this->permissionCats;
    }

    /**
     * Returns a list of permission category endings available on the server instance.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionEnds(): array
    {
        if ($this->permissionEnds === null) {
            $this->fetchPermissionList();
        }

        return $this->permissionCats;
    }

    /**
     * Returns an array filled with all permission categories known to the server including
     * their ID, name and parent.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionTree(): array
    {
        $permtree = [];

        foreach ($this->permissionCats() as $val) {
            $permtree[$val]["permcatid"] = $val;
            $permtree[$val]["permcathex"] = "0x" . dechex($val);
            $permtree[$val]["permcatname"] = StringHelper::factory(Convert::permissionCategory($val));
            $permtree[$val]["permcatparent"] = $permtree[$val]["permcathex"][3] == 0 ? 0 : hexdec($permtree[$val]["permcathex"][2] . 0);
            $permtree[$val]["permcatchilren"] = 0;
            $permtree[$val]["permcatcount"] = 0;

            if (isset($permtree[$permtree[$val]["permcatparent"]])) {
                $permtree[$permtree[$val]["permcatparent"]]["permcatchilren"]++;
            }

            if ($permtree[$val]["permcatname"]->contains("/")) {
                $permtree[$val]["permcatname"] = $permtree[$val]["permcatname"]->section("/", 1)->trim();
            }

            foreach ($this->permissionList() as $permission) {
                if ($permission["permid"]["permcatid"] == $val) {
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
     * @param integer|integer[] $permissionId
     * @return array
     * @throws ServerQueryException
     * @throws AdapterException
     */
    public function permissionFind(int|array $permissionId): array
    {
        if (!is_array($permissionId)) {
            $permident = (is_numeric($permissionId)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permissionId))) ? "permid" : "permsid";
        }

        return $this->execute("permfind", [$permident => $permissionId])->toArray();
    }

    /**
     * Returns the ID of the permission matching the given name.
     *
     * @param string $name
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionGetIdByName(string $name): int
    {
        if (!array_key_exists($name, $this->permissionList())) {
            throw new ServerQueryException("invalid permission ID", 0xA02);
        }

        return $this->permissionList[$name]["permid"];
    }

    /**
     * Returns the name of the permission matching the given ID.
     *
     * @param integer $permissionId
     * @return StringHelper
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionGetNameById(int $permissionId): StringHelper
    {
        foreach ($this->permissionList() as $name => $perm) {
            if ($perm["permid"] == $permissionId) {
                return new StringHelper($name);
            }
        }

        throw new ServerQueryException("invalid permission ID", 0xA02);
    }

    /**
     * Returns the internal category of the permission matching the given ID.
     *
     * All pre-3.0.7 permission IDs are 2 bytes wide. The first byte identifies the category while
     * the second byte is the permission count within that group.
     *
     * @param integer $permid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionGetCategoryById(int $permid): int
    {
        if (!is_numeric($permid)) {
            $permid = $this->permissionGetIdByName($permid);
        }

        if ($permid < 0x1000) {
            if ($this->permissionEnds === null) {
                $this->fetchPermissionList();
            }

            if ($this->permissionCats === null) {
                $this->fetchPermissionCats();
            }

            $catids = array_values($this->permissionCats());

            foreach ($this->permissionEnds as $key => $val) {
                if ($val >= $permid && isset($catids[$key])) {
                    return $catids[$key];
                }
            }

            return 0;
        } else {
            return (int)$permid >> 8;
        }
    }

    /**
     * Returns the internal ID of the i_needed_modify_power_* or grant permission.
     *
     * Every permission has an associated i_needed_modify_power_* permission, for example b_client_ban_create has an
     * associated permission called i_needed_modify_power_client_ban_create.
     *
     * @param integer $permid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permissionGetGrantById(int $permid): int
    {
        if (!is_numeric($permid)) {
            $permid = $this->permissionGetIdByName($permid);
        }

        if ($permid < 0x1000) {
            return (int)$permid + 0x8000;
        } else {
            return (int)bindec(substr(decbin($permid), -8)) + 0xFF00;
        }
    }

    /**
     * Adds a set of specified permissions to all regular server groups on all virtual servers. The target groups will
     * be identified by the value of their i_group_auto_update_type permission specified with $sgtype.
     *
     * @param integer $sgtype
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @param integer|integer[] $permnegated
     * @param bool|bool[] $permskip
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupPermAutoAssign(int $sgtype, int|array $permid, int|array $permvalue, int|array $permnegated = 0, array|bool $permskip = false): void
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("servergroupautoaddperm", ["sgtype" => $sgtype, $permident => $permid, "permvalue" => $permvalue, "permnegated" => $permnegated, "permskip" => $permskip]);
    }

    /**
     * Removes a set of specified permissions from all regular server groups on all virtual servers. The target groups
     * will be identified by the value of their i_group_auto_update_type permission specified with $sgtype.
     *
     * @param integer $sgtype
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupPermAutoRemove(int $sgtype, int|array $permid): void
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("servergroupautodelperm", ["sgtype" => $sgtype, $permident => $permid]);
    }

    /**
     * Returns an array containing the value of a specified permission for your own client.
     *
     * @param integer|integer[] $permid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function selfPermCheck(int|array $permid): array
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        return $this->execute("permget", [$permident => $permid])->toAssocArray("permsid");
    }

    /**
     * Changes the server instance configuration using given properties.
     *
     * @param array $properties
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function modify(array $properties): void
    {
        $this->execute("instanceedit", $properties);
        $this->resetNodeInfo();
    }

    /**
     * Sends a text message to all clients on all virtual servers in the TeamSpeak 3 Server instance.
     *
     * @param string $msg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function message(string $msg): void
    {
        $this->execute("gm", ["msg" => $msg]);
    }

    /**
     * Displays a specified number of entries (1-100) from the servers log.
     *
     * @param integer $lines
     * @param integer|null $begin_pos
     * @param boolean|null $reverse
     * @param boolean $instance
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function logView(int $lines = 30, int $begin_pos = null, bool $reverse = null, bool $instance = true): array
    {
        return $this->execute("logview", ["lines" => $lines, "begin_pos" => $begin_pos, "instance" => $instance, "reverse" => $reverse])->toArray();
    }

    /**
     * Writes a custom entry into the server instance log.
     *
     * @param string $logmsg
     * @param integer $loglevel
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function logAdd(string $logmsg, int $loglevel = TeamSpeak3::LOGLEVEL_INFO): void
    {
        $sid = $this->serverSelectedId();

        $this->serverDeselect();
        $this->execute("logadd", ["logmsg" => $logmsg, "loglevel" => $loglevel]);
        $this->serverSelect($sid);
    }

    /**
     * Authenticates with the TeamSpeak 3 Server instance using given ServerQuery login credentials.
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function login(string $username, string $password): void
    {
        $this->execute("login", ["client_login_name" => $username, "client_login_password" => $password]);
        $this->whoamiReset();

        $crypt = new Crypt($username);

        $this->setStorage("_login_user", $username);
        $this->setStorage("_login_pass", $crypt->encrypt($password));

        Signal::getInstance()->emit("notifyLogin", $this);
    }

    /**
     * Deselects the active virtual server and logs out from the server instance.
     *
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function logout(): void
    {
        $this->request("logout");
        $this->whoamiReset();

        $this->delStorage("_login_user");
        $this->delStorage("_login_pass");

        Signal::getInstance()->emit("notifyLogout", $this);
    }

    /**
     * Returns the number of ServerQuery logins on the selected virtual server.
     *
     * @param string|null $pattern
     * @return mixed
     * @throws ServerQueryException
     * @throws AdapterException
     */
    public function queryCountLogin(string $pattern = null): mixed
    {
        return current($this->execute("queryloginlist -count", ["duration" => 1, "pattern" => $pattern])->toList("count"));
    }

    /**
     * Returns a list of ServerQuery logins on the selected virtual server. By default, the server spits out 25 entries
     * at once.
     *
     * @param integer|null $offset
     * @param integer|null $limit
     * @param string|null $pattern
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function queryListLogin(int $offset = null, int $limit = null, string $pattern = null): array
    {
        return $this->execute("queryloginlist -count", ["start" => $offset, "duration" => $limit, "pattern" => $pattern])->toAssocArray("cldbid");
    }

    /**
     * Creates a new ServerQuery login, or enables ServerQuery logins for an existing client. When no virtual server is
     * selected, the command will create global ServerQuery login, otherwise a ServerQuery login will be added for an
     * existing client (cldbid must be specified).
     *
     * @param string $username
     * @param int $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function queryLoginCreate(string $username, int $cldbid = 0): array
    {
        if ($this->serverSelectedId()) {
            return $this->execute("queryloginadd", ["client_login_name" => $username, "cldbid" => $cldbid])->toList();
        } else {
            return $this->execute("queryloginadd", ["client_login_name" => $username])->toList();
        }
    }

    /**
     * Deletes an existing ServerQuery login.
     *
     * @param integer $cldbid
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function queryLoginDelete(int $cldbid)
    {
        $this->execute("querylogindel", ["cldbid" => $cldbid]);
    }

    /**
     * Returns information about your current ServerQuery connection.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function whoami(): array
    {
        if ($this->whoami === null) {
            $this->whoami = $this->request("whoami")->toList();
        }

        return $this->whoami;
    }

    /**
     * Returns a single value from the current ServerQuery connection info.
     *
     * @param string $ident
     * @param mixed|null $default
     * @return mixed|null
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function whoamiGet(string $ident, mixed $default = null): mixed
    {
        if (array_key_exists($ident, $this->whoami())) {
            return $this->whoami[$ident];
        }

        return $default;
    }

    /**
     * Sets a single value in the current ServerQuery connection info.
     *
     * @param string $ident
     * @param mixed|null $value
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function whoamiSet(string $ident, mixed $value = null)
    {
        $this->whoami();

        $this->whoami[$ident] = (is_numeric($value)) ? (int)$value : StringHelper::factory($value);
    }

    /**
     * Resets the current ServerQuery connection info.
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
    public function getAdapterHost(): string
    {
        return $this->getParent()->getTransportHost();
    }

    /**
     * Returns the network port the adapter is connected to.
     *
     * @return string
     */
    public function getAdapterPort(): string
    {
        return $this->getParent()->getTransportPort();
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     * @ignore
     */
    protected function fetchNodeList()
    {
        $servers = $this->serverList();

        foreach ($servers as $server) {
            $this->nodeList[] = $server;
        }
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     * @ignore
     */
    protected function fetchNodeInfo()
    {
        $info1 = $this->request("hostinfo")->toList();
        $info2 = $this->request("instanceinfo")->toList();

        $this->nodeInfo = array_merge($this->nodeInfo, $info1, $info2);
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     * @ignore
     */
    protected function fetchPermissionList()
    {
        $reply = $this->request("permissionlist -new")->toArray();
        $start = 1;

        $this->permissionEnds = [];
        $this->permissionList = [];

        foreach ($reply as $line) {
            if (array_key_exists("group_id_end", $line)) {
                $this->permissionEnds[] = $line["group_id_end"];
            } else {
                $this->permissionList[$line["permname"]->toString()] = array_merge(["permid" => $start++], $line);
            }
        }
    }

    /**
     * @ignore
     */
    protected function fetchPermissionCats()
    {
        $permcats = [];
        $reflects = new ReflectionClass("TeamSpeak3");

        foreach ($reflects->getConstants() as $key => $val) {
            if (!StringHelper::factory($key)->startsWith("PERM_CAT") || $val == 0xFF) {
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
     * @param string|null $name
     */
    public function setPredefinedQueryName(string $name = null)
    {
        $this->setStorage("_query_nick", $name);

        $this->predefined_query_name = $name;
    }

    /**
     * Returns the pre-defined nickname for ServerQuery clients which will be used automatically
     * after selecting a virtual server.
     *
     * @return string|null
     */
    public function getPredefinedQueryName(): ?string
    {
        return $this->predefined_query_name;
    }

    /**
     * Sets the option to decide whether ServerQuery clients should be excluded from node
     * lists or not.
     *
     * @param boolean $exclude
     * @return void
     */
    public function setExcludeQueryClients(bool $exclude = false): void
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
    public function getExcludeQueryClients(): bool
    {
        return $this->exclude_query_clients;
    }

    /**
     * Sets the option to decide whether offline servers will be started in virtual mode
     * by default or not.
     *
     * @param boolean $virtual
     * @return void
     */
    public function setUseOfflineAsVirtual(bool $virtual = false): void
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
    public function getUseOfflineAsVirtual(): bool
    {
        return $this->start_offline_virtual;
    }

    /**
     * Sets the option to decide whether clients should be sorted before sub-channels to support
     * the new TeamSpeak 3 Client display mode or not.
     *
     * @param boolean $first
     */
    public function setLoadClientlistFirst(bool $first = false)
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
    public function getLoadClientlistFirst(): bool
    {
        return $this->sort_clients_channels;
    }

    /**
     * Returns the underlying PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery object.
     *
     * @return ServerQuery
     */
    public function getAdapter(): ServerQuery
    {
        return $this->getParent();
    }

    /**
     * Returns a unique identifier for the node which can be used as an HTML property.
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        return "ts3_h";
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return "host";
    }

    /**
     * Returns a symbol representing the node.
     *
     * @return string
     */
    public function getSymbol(): string
    {
        return "+";
    }

    /**
     * Re-authenticates with the TeamSpeak 3 Server instance using given ServerQuery login
     * credentials and re-selects a previously selected virtual server.
     *
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function __wakeup()
    {
        $username = $this->getStorage("_login_user");
        $password = $this->getStorage("_login_pass");

        if ($username && $password) {
            $crypt = new Crypt($username);

            $this->login($username, $crypt->decrypt($password));
        }

        $this->predefined_query_name = $this->getStorage("_query_nick");
        $this->exclude_query_clients = $this->getStorage("_query_hide", false);
        $this->start_offline_virtual = $this->getStorage("_do_virtual", false);
        $this->sort_clients_channels = $this->getStorage("_client_top", false);

        if ($server = $this->getStorage("_server_use")) {
            $func = array_shift($server);
            $args = array_shift($server);

            call_user_func_array([$this, $func], $args);
        }
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAdapterHost();
    }
}
