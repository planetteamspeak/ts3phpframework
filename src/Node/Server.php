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

use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;

/**
 * @class TeamSpeak3_Node_Server
 * @brief Class describing a TeamSpeak 3 virtual server and all it's parameters.
 */
class Server extends Node
{
    /**
     * @ignore
     */
    protected $channelList = null;

    /**
     * @ignore
     */
    protected $clientList = null;

    /**
     * @ignore
     */
    protected $sgroupList = null;

    /**
     * @ignore
     */
    protected $cgroupList = null;

    /**
     * The TeamSpeak3_Node_Server constructor.
     *
     * @param Host $host
     * @param array $info
     * @param string $index
     * @throws ServerQueryException
     */
    public function __construct(Host $host, array $info, $index = "virtualserver_id")
    {
        $this->parent = $host;
        $this->nodeInfo = $info;

        if (!array_key_exists($index, $this->nodeInfo)) {
            throw new ServerQueryException("invalid serverID", 0x400);
        }

        $this->nodeId = $this->nodeInfo[$index];
    }

    /**
     * Sends a prepared command to the server and returns the result.
     *
     * @param string $cmd
     * @param boolean $throw
     * @return Reply
     */
    public function request($cmd, $throw = true)
    {
        if ($this->getId() != $this->getParent()->serverSelectedId()) {
            $this->getParent()->serverSelect($this->getId());
        }

        return $this->getParent()->request($cmd, $throw);
    }

    /**
     * Returns an array filled with TeamSpeak3_Node_Channel objects.
     *
     * @param array $filter
     * @return array|Channel[]
     */
    public function channelList(array $filter = [])
    {
        if ($this->channelList === null) {
            $channels = $this->request("channellist -topic -flags -voice -limits -icon")->toAssocArray("cid");

            $this->channelList = [];

            foreach ($channels as $cid => $channel) {
                $this->channelList[$cid] = new Channel($this, $channel);
            }

            $this->resetNodeList();
        }

        return $this->filterList($this->channelList, $filter);
    }

    /**
     * Resets the list of channels online.
     *
     * @return void
     */
    public function channelListReset()
    {
        $this->resetNodeList();
        $this->channelList = null;
    }

    /**
     * Returns the TeamSpeak3_Node_Channel object representing the default channel.
     *
     * @return Channel
     * @throws ServerQueryException
     */
    public function channelGetDefault()
    {
        foreach ($this->channelList() as $channel) {
            if ($channel["channel_flag_default"]) {
                return $channel;
            }
        }

        throw new ServerQueryException("invalid channelID", 0x300);
    }

    /**
     * Creates a new channel using given properties and returns the new ID.
     *
     * @param array $properties
     * @return integer
     */
    public function channelCreate(array $properties)
    {
        $cid = $this->execute("channelcreate", $properties)->toList();
        $this->channelListReset();

        if (!isset($properties["channel_flag_permanent"]) && !isset($properties["channel_flag_semi_permanent"])) {
            $this->getParent()->whoamiSet("client_channel_id", $cid["cid"]);
        }

        return $cid["cid"];
    }

    /**
     * Deletes the channel specified by $cid.
     *
     * @param integer $cid
     * @param boolean $force
     * @return void
     */
    public function channelDelete($cid, $force = false)
    {
        $this->execute("channeldelete", ["cid" => $cid, "force" => $force]);
        $this->channelListReset();

        if (($cid instanceof Node ? $cid->getId() : $cid) == $this->whoamiGet("client_channel_id")) {
            $this->getParent()->whoamiReset();
        }
    }

    /**
     * Moves the channel specified by $cid to the parent channel specified with $pid.
     *
     * @param integer $cid
     * @param integer $pid
     * @param integer $order
     * @return void
     */
    public function channelMove($cid, $pid, $order = null)
    {
        $this->execute("channelmove", ["cid" => $cid, "cpid" => $pid, "order" => $order]);
        $this->channelListReset();
    }

    /**
     * Returns TRUE if the given TeamSpeak3_Node_Channel object is a spacer.
     *
     * @param Channel $channel
     * @return boolean
     */
    public function channelIsSpacer(Channel $channel)
    {
        return (preg_match("/\[[^\]]*spacer[^\]]*\]/", $channel) && $channel["channel_flag_permanent"] && !$channel["pid"]) ? true : false;
    }

    /**
     * Creates a new channel spacer and returns the new ID. The first parameter $ident is used to create a
     * unique spacer name on the virtual server.
     *
     * @param string $ident
     * @param mixed $type
     * @param integer $align
     * @param integer $order
     * @param integer $maxclients
     * @return integer
     * @throws ServerQueryException
     */
    public function channelSpacerCreate(
        $ident,
        $type = TeamSpeak3::SPACER_SOLIDLINE,
        $align = TeamSpeak3::SPACER_ALIGN_REPEAT,
        $order = null,
        $maxclients = 0
    ) {
        $properties = [
            "channel_name_phonetic" => "channel spacer",
            "channel_codec" => TeamSpeak3::CODEC_OPUS_VOICE,
            "channel_codec_quality" => 0x00,
            "channel_flag_permanent" => true,
            "channel_flag_maxclients_unlimited" => false,
            "channel_flag_maxfamilyclients_unlimited" => false,
            "channel_flag_maxfamilyclients_inherited" => false,
            "channel_maxclients" => $maxclients,
            "channel_order" => $order,
        ];

        switch ($align) {
            case TeamSpeak3::SPACER_ALIGN_REPEAT:
                $properties["channel_name"] = "[*spacer" . strval($ident) . "]";
                break;

            case TeamSpeak3::SPACER_ALIGN_LEFT:
                $properties["channel_name"] = "[lspacer" . strval($ident) . "]";
                break;

            case TeamSpeak3::SPACER_ALIGN_RIGHT:
                $properties["channel_name"] = "[rspacer" . strval($ident) . "]";
                break;

            case TeamSpeak3::SPACER_ALIGN_CENTER:
                $properties["channel_name"] = "[cspacer" . strval($ident) . "]";
                break;

            default:
                throw new ServerQueryException("missing required parameter", 0x606);
                break;
        }

        switch ($type) {
            case (string)TeamSpeak3::SPACER_SOLIDLINE:
                $properties["channel_name"] .= "___";
                break;

            case (string)TeamSpeak3::SPACER_DASHLINE:
                $properties["channel_name"] .= "---";
                break;

            case (string)TeamSpeak3::SPACER_DOTLINE:
                $properties["channel_name"] .= "...";
                break;

            case (string)TeamSpeak3::SPACER_DASHDOTLINE:
                $properties["channel_name"] .= "-.-";
                break;

            case (string)TeamSpeak3::SPACER_DASHDOTDOTLINE:
                $properties["channel_name"] .= "-..";
                break;

            default:
                $properties["channel_name"] .= strval($type);
                break;
        }

        return $this->channelCreate($properties);
    }

    /**
     * Returns the possible type of a channel spacer.
     *
     * @param integer $cid
     * @return integer
     * @throws ServerQueryException
     */
    public function channelSpacerGetType($cid)
    {
        $channel = $this->channelGetById($cid);

        if (!$this->channelIsSpacer($channel)) {
            throw new ServerQueryException("invalid channel flags", 0x307);
        }

        switch ($channel["channel_name"]->section("]", 1)) {
            case "___":
                return TeamSpeak3::SPACER_SOLIDLINE;

            case "---":
                return TeamSpeak3::SPACER_DASHLINE;

            case "...":
                return TeamSpeak3::SPACER_DOTLINE;

            case "-.-":
                return TeamSpeak3::SPACER_DASHDOTLINE;

            case "-..":
                return TeamSpeak3::SPACER_DASHDOTDOTLINE;

            default:
                return TeamSpeak3::SPACER_CUSTOM;
        }
    }

    /**
     * Returns the possible alignment of a channel spacer.
     *
     * @param integer $cid
     * @return integer
     * @throws ServerQueryException
     */
    public function channelSpacerGetAlign($cid)
    {
        $channel = $this->channelGetById($cid);

        if (!$this->channelIsSpacer($channel) || !preg_match("/\[(.*)spacer.*\]/", $channel, $matches) || !isset($matches[1])) {
            throw new ServerQueryException("invalid channel flags", 0x307);
        }

        switch ($matches[1]) {
            case "*":
                return TeamSpeak3::SPACER_ALIGN_REPEAT;

            case "c":
                return TeamSpeak3::SPACER_ALIGN_CENTER;

            case "r":
                return TeamSpeak3::SPACER_ALIGN_RIGHT;

            default:
                return TeamSpeak3::SPACER_ALIGN_LEFT;
        }
    }

    /**
     * Returns a list of permissions defined for a specific channel.
     *
     * @param integer $cid
     * @param boolean $permsid
     * @return array
     */
    public function channelPermList($cid, $permsid = false)
    {
        return $this->execute("channelpermlist", ["cid" => $cid, $permsid ? "-permsid" : null])
                    ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to a channel. Multiple permissions can be added by
     * providing the two parameters of each permission.
     *
     * @param integer $cid
     * @param integer $permid
     * @param integer $permvalue
     * @return void
     */
    public function channelPermAssign($cid, $permid, $permvalue)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channeladdperm", ["cid" => $cid, $permident => $permid, "permvalue" => $permvalue]);
    }

    /**
     * Removes a set of specified permissions from a channel. Multiple permissions can be removed at once.
     *
     * @param integer $cid
     * @param integer $permid
     * @return void
     */
    public function channelPermRemove($cid, $permid)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channeldelperm", ["cid" => $cid, $permident => $permid]);
    }

    /**
     * Returns a list of permissions defined for a client in a specific channel.
     *
     * @param integer $cid
     * @param integer $cldbid
     * @param boolean $permsid
     * @return array
     */
    public function channelClientPermList($cid, $cldbid, $permsid = false)
    {
        return $this->execute("channelclientpermlist", ["cid" => $cid, "cldbid" => $cldbid, $permsid ? "-permsid" : null])
                    ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to a client in a specific channel. Multiple permissions can be added by
     * providing the two parameters of each permission.
     *
     * @param integer $cid
     * @param integer $cldbid
     * @param integer $permid
     * @param integer $permvalue
     * @return void
     */
    public function channelClientPermAssign($cid, $cldbid, $permid, $permvalue)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channelclientaddperm", ["cid" => $cid, "cldbid" => $cldbid, $permident => $permid, "permvalue" => $permvalue]);
    }

    /**
     * Removes a set of specified permissions from a client in a specific channel. Multiple permissions can be removed at once.
     *
     * @param integer $cid
     * @param integer $cldbid
     * @param integer $permid
     * @return void
     */
    public function channelClientPermRemove($cid, $cldbid, $permid)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channelclientdelperm", ["cid" => $cid, "cldbid" => $cldbid, $permident => $permid]);
    }

    /**
     * Returns a list of files and directories stored in the specified channels file repository.
     *
     * @param integer $cid
     * @param string $cpw
     * @param string $path
     * @param boolean $recursive
     * @return array
     */
    public function channelFileList($cid, $cpw = "", $path = "/", $recursive = false)
    {
        $files = $this->execute("ftgetfilelist", ["cid" => $cid, "cpw" => $cpw, "path" => $path])->toArray();
        $count = count($files);

        for ($i = 0; $i < $count; $i++) {
            $files[$i]["sid"] = $this->getId();
            $files[$i]["cid"] = $files[0]["cid"];
            $files[$i]["path"] = $files[0]["path"];
            $files[$i]["src"] = new StringHelper($cid ? $files[$i]["path"] : "/");

            if (!$files[$i]["src"]->endsWith("/")) {
                $files[$i]["src"]->append("/");
            }

            $files[$i]["src"]->append($files[$i]["name"]);

            if ($recursive && $files[$i]["type"] == TeamSpeak3::FILE_TYPE_DIRECTORY) {
                $files = array_merge($files, $this->channelFileList($cid, $cpw, $path . $files[$i]["name"], $recursive));
            }
        }

        uasort($files, [__CLASS__, "sortFileList"]);

        return $files;
    }

    /**
     * Returns detailed information about the specified file stored in a channels file repository.
     *
     * @param integer $cid
     * @param string $cpw
     * @param string $name
     * @return array
     */
    public function channelFileInfo($cid, $cpw = "", $name = "/")
    {
        $info = $this->execute("ftgetfileinfo", ["cid" => $cid, "cpw" => $cpw, "name" => $name])->toArray();

        return array_pop($info);
    }

    /**
     * Renames a file in a channels file repository. If the two parameters $tcid and $tcpw are specified, the file
     * will be moved into another channels file repository.
     *
     * @param integer $cid
     * @param string $cpw
     * @param string $oldname
     * @param string $newname
     * @param integer $tcid
     * @param string $tcpw
     * @return void
     */
    public function channelFileRename($cid, $cpw = "", $oldname = "/", $newname = "/", $tcid = null, $tcpw = null)
    {
        $this->execute("ftrenamefile", ["cid" => $cid, "cpw" => $cpw, "oldname" => $oldname, "newname" => $newname, "tcid" => $tcid, "tcpw" => $tcpw]);
    }

    /**
     * Deletes one or more files stored in a channels file repository.
     *
     * @param integer $cid
     * @param string $cpw
     * @param string $name
     * @return void
     */
    public function channelFileDelete($cid, $cpw = "", $name = "/")
    {
        $this->execute("ftdeletefile", ["cid" => $cid, "cpw" => $cpw, "name" => $name]);
    }

    /**
     * Creates new directory in a channels file repository.
     *
     * @param integer $cid
     * @param string $cpw
     * @param string $dirname
     * @return void
     */
    public function channelDirCreate($cid, $cpw = "", $dirname = "/")
    {
        $this->execute("ftcreatedir", ["cid" => $cid, "cpw" => $cpw, "dirname" => $dirname]);
    }

    /**
     * Returns the level of a channel.
     *
     * @param integer $cid
     * @return integer
     */
    public function channelGetLevel($cid)
    {
        $channel = $this->channelGetById($cid);
        $levelno = 0;

        if ($channel["pid"]) {
            $levelno = $this->channelGetLevel($channel["pid"]) + 1;
        }

        return $levelno;
    }

    /**
     * Returns the pathway of a channel which can be used as a clients default channel.
     *
     * @param integer $cid
     * @return string
     */
    public function channelGetPathway($cid)
    {
        $channel = $this->channelGetById($cid);
        $pathway = $channel["channel_name"];

        if ($channel["pid"]) {
            $pathway = $this->channelGetPathway($channel["pid"]) . "/" . $channel["channel_name"];
        }

        return $pathway;
    }

    /**
     * Returns the TeamSpeak3_Node_Channel object matching the given ID.
     *
     * @param integer $cid
     * @return Channel
     * @throws ServerQueryException
     */
    public function channelGetById($cid)
    {
        if (!array_key_exists((string)$cid, $this->channelList())) {
            throw new ServerQueryException("invalid channelID", 0x300);
        }

        return $this->channelList[intval((string)$cid)];
    }

    /**
     * Returns the TeamSpeak3_Node_Channel object matching the given name.
     *
     * @param string $name
     * @return Channel
     * @throws ServerQueryException
     */
    public function channelGetByName($name)
    {
        foreach ($this->channelList() as $channel) {
            if ($channel["channel_name"] == $name) {
                return $channel;
            }
        }

        throw new ServerQueryException("invalid channelID", 0x300);
    }

    /**
     * Returns an array filled with TeamSpeak3_Node_Client objects.
     *
     * @param array $filter
     * @return array|Client[]
     */
    public function clientList(array $filter = [])
    {
        if ($this->clientList === null) {
            $clients = $this->request("clientlist -uid -away -badges -voice -info -times -groups -icon -country -ip")
                            ->toAssocArray("clid");

            $this->clientList = [];

            foreach ($clients as $clid => $client) {
                if ($this->getParent()->getExcludeQueryClients() && $client["client_type"]) {
                    continue;
                }

                $this->clientList[$clid] = new Client($this, $client);
            }

            uasort($this->clientList, [__CLASS__, "sortClientList"]);

            $this->resetNodeList();
        }

        return $this->filterList($this->clientList, $filter);
    }

    /**
     * Resets the list of clients online.
     *
     * @return void
     */
    public function clientListReset()
    {
        $this->resetNodeList();
        $this->clientList = null;
    }

    /**
     * Returns a list of clients matching a given name pattern.
     *
     * @param string $pattern
     * @return array
     */
    public function clientFind($pattern)
    {
        return $this->execute("clientfind", ["pattern" => $pattern])->toAssocArray("clid");
    }

    /**
     * Returns a list of client identities known by the virtual server. By default, the server spits out 25 entries
     * at once.
     *
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function clientListDb($offset = null, $limit = null)
    {
        return $this->execute("clientdblist -count", ["start" => $offset, "duration" => $limit])
                    ->toAssocArray("cldbid");
    }

    /**
     * Returns the number of client identities known by the virtual server.
     *
     * @return integer
     */
    public function clientCountDb()
    {
        return current($this->execute("clientdblist -count", ["duration" => 1])->toList("count"));
    }

    /**
     * Returns a list of properties from the database for the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @return array
     */
    public function clientInfoDb($cldbid)
    {
        return $this->execute("clientdbinfo", ["cldbid" => $cldbid])->toList();
    }

    /**
     * Returns a list of client database information matching a given pattern. You can either search for a clients
     * last known nickname or his unique identity by using the $uid option.
     *
     * @param string $pattern
     * @param boolean $uid
     * @return array
     */
    public function clientFindDb($pattern, $uid = false)
    {
        return array_keys($this->execute("clientdbfind", ["pattern" => $pattern, ($uid) ? "-uid" : null, "-details"])
                               ->toAssocArray("cldbid"));
    }

    /**
     * Returns the number of regular clients online.
     *
     * @return integer
     */
    public function clientCount()
    {
        if ($this->isOffline()) {
            return 0;
        }

        return $this["virtualserver_clientsonline"] - $this["virtualserver_queryclientsonline"];
    }

    /**
     * Returns the TeamSpeak3_Node_Client object matching the given ID.
     *
     * @param integer $clid
     * @return Client
     * @throws ServerQueryException
     */
    public function clientGetById($clid)
    {
        if (!array_key_exists((string)$clid, $this->clientList())) {
            throw new ServerQueryException("invalid clientID", 0x200);
        }

        return $this->clientList[intval((string)$clid)];
    }

    /**
     * Returns the TeamSpeak3_Node_Client object matching the given name.
     *
     * @param string $name
     * @return Client
     * @throws ServerQueryException
     */
    public function clientGetByName($name)
    {
        foreach ($this->clientList() as $client) {
            if ($client["client_nickname"] == $name) {
                return $client;
            }
        }

        throw new ServerQueryException("invalid clientID", 0x200);
    }

    /**
     * Returns the TeamSpeak3_Node_Client object matching the given unique identifier.
     *
     * @param string $uid
     * @return Client
     * @throws ServerQueryException
     */
    public function clientGetByUid($uid)
    {
        foreach ($this->clientList() as $client) {
            if ($client["client_unique_identifier"] == $uid) {
                return $client;
            }
        }

        throw new ServerQueryException("invalid clientID", 0x200);
    }

    /**
     * Returns the TeamSpeak3_Node_Client object matching the given database ID.
     *
     * @param integer $dbid
     * @return Client
     * @throws ServerQueryException
     */
    public function clientGetByDbid($dbid)
    {
        foreach ($this->clientList() as $client) {
            if ($client["client_database_id"] == $dbid) {
                return $client;
            }
        }

        throw new ServerQueryException("invalid clientID", 0x200);
    }

    /**
     * Returns an array containing the last known nickname and the database ID of the client matching
     * the unique identifier specified with $cluid.
     *
     * @param string $cluid
     * @return array
     */
    public function clientGetNameByUid($cluid)
    {
        return $this->execute("clientgetnamefromuid", ["cluid" => $cluid])->toList();
    }

    /**
     * Returns an array containing a list of active client connections using the unique identifier
     * specified with $cluid.
     *
     * @param string $cluid
     * @return array
     */
    public function clientGetIdsByUid($cluid)
    {
        return $this->execute("clientgetids", ["cluid" => $cluid])->toAssocArray("clid");
    }

    /**
     * Returns an array containing the last known nickname and the unique identifier of the client
     * matching the database ID specified with $cldbid.
     *
     * @param string $cldbid
     * @return array
     */
    public function clientGetNameByDbid($cldbid)
    {
        return $this->execute("clientgetnamefromdbid", ["cldbid" => $cldbid])->toList();
    }

    /**
     * Returns an array containing the names and IDs of all server groups the client specified with
     * $cldbid is is currently residing in.
     *
     * @param string $cldbid
     * @return array
     */
    public function clientGetServerGroupsByDbid($cldbid)
    {
        return $this->execute("servergroupsbyclientid", ["cldbid" => $cldbid])->toAssocArray("sgid");
    }

    /**
     * Moves a client to another channel.
     *
     * @param integer $clid
     * @param integer $cid
     * @param string $cpw
     * @return void
     */
    public function clientMove($clid, $cid, $cpw = null)
    {
        $this->clientListReset();

        $this->execute("clientmove", ["clid" => $clid, "cid" => $cid, "cpw" => $cpw]);

        if ($clid instanceof Node) {
            $clid = $clid->getId();
        }

        if ($cid instanceof Node) {
            $cid = $cid->getId();
        }

        if (!is_array($clid) && $clid == $this->whoamiGet("client_id")) {
            $this->getParent()->whoamiSet("client_channel_id", $cid);
        }
    }

    /**
     * Kicks one or more clients from their currently joined channel or from the server.
     *
     * @param integer $clid
     * @param integer $reasonid
     * @param string $reasonmsg
     * @return void
     */
    public function clientKick($clid, $reasonid = TeamSpeak3::KICK_CHANNEL, $reasonmsg = null)
    {
        $this->clientListReset();

        $this->execute("clientkick", ["clid" => $clid, "reasonid" => $reasonid, "reasonmsg" => $reasonmsg]);
    }

    /**
     * Sends a poke message to a client.
     *
     * @param integer $clid
     * @param string $msg
     * @return void
     */
    public function clientPoke($clid, $msg)
    {
        $this->execute("clientpoke", ["clid" => $clid, "msg" => $msg]);
    }

    /**
     * Bans the client specified with ID $clid from the server. Please note that this will create three separate
     * ban rules for the targeted clients IP address, the unique identifier and the myTeamSpeak ID (if available).
     *
     * @param integer $clid
     * @param integer $timeseconds
     * @param string $reason
     * @return array
     */
    public function clientBan($clid, $timeseconds = null, $reason = null)
    {
        $this->clientListReset();

        $bans = $this->execute("banclient", ["clid" => $clid, "time" => $timeseconds, "banreason" => $reason])
                     ->toAssocArray("banid");

        return array_keys($bans);
    }

    /**
     * Changes the clients properties using given properties.
     *
     * @param string $cldbid
     * @param array $properties
     * @return void
     */
    public function clientModifyDb($cldbid, array $properties)
    {
        $properties["cldbid"] = $cldbid;

        $this->execute("clientdbedit", $properties);
    }

    /**
     * Deletes a clients properties from the database.
     *
     * @param string $cldbid
     * @return void
     */
    public function clientDeleteDb($cldbid)
    {
        $this->execute("clientdbdelete", ["cldbid" => $cldbid]);
    }

    /**
     * Sets the channel group of a client to the ID specified.
     *
     * @param integer $cldbid
     * @param integer $cid
     * @param integer $cgid
     * @return void
     */
    public function clientSetChannelGroup($cldbid, $cid, $cgid)
    {
        $this->execute("setclientchannelgroup", ["cldbid" => $cldbid, "cid" => $cid, "cgid" => $cgid]);
    }

    /**
     * Returns a list of permissions defined for a client.
     *
     * @param integer $cldbid
     * @param boolean $permsid
     * @return array
     */
    public function clientPermList($cldbid, $permsid = false)
    {
        $this->clientListReset();

        return $this->execute("clientpermlist", ["cldbid" => $cldbid, $permsid ? "-permsid" : null])
                    ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to a client. Multiple permissions can be added by providing
     * the three parameters of each permission.
     *
     * @param integer $cldbid
     * @param integer $permid
     * @param integer $permvalue
     * @param integer $permskip
     * @return void
     */
    public function clientPermAssign($cldbid, $permid, $permvalue, $permskip = false)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("clientaddperm -continueonerror", ["cldbid" => $cldbid, $permident => $permid, "permvalue" => $permvalue, "permskip" => $permskip]);
    }

    /**
     * Removes a set of specified permissions from a client. Multiple permissions can be removed at once.
     *
     * @param integer $cldbid
     * @param integer $permid
     * @return void
     */
    public function clientPermRemove($cldbid, $permid)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("clientdelperm", ["cldbid" => $cldbid, $permident => $permid]);
    }

    /**
     * Returns a list of server groups available.
     *
     * @param array $filter
     * @return array|Servergroup[]
     */
    public function serverGroupList(array $filter = [])
    {
        if ($this->sgroupList === null) {
            $this->sgroupList = $this->request("servergrouplist")->toAssocArray("sgid");

            foreach ($this->sgroupList as $sgid => $group) {
                $this->sgroupList[$sgid] = new Servergroup($this, $group);
            }

            uasort($this->sgroupList, [__CLASS__, "sortGroupList"]);
        }

        return $this->filterList($this->sgroupList, $filter);
    }

    /**
     * Resets the list of server groups.
     *
     * @return void
     */
    public function serverGroupListReset()
    {
        $this->sgroupList = null;
    }

    /**
     * Creates a new server group using the name specified with $name and returns its ID.
     *
     * @param string $name
     * @param integer $type
     * @return integer
     */
    public function serverGroupCreate($name, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        $this->serverGroupListReset();

        $sgid = $this->execute("servergroupadd", ["name" => $name, "type" => $type])->toList();

        return $sgid["sgid"];
    }

    /**
     * Creates a copy of an existing server group specified by $ssgid and returns the new groups ID.
     *
     * @param integer $ssgid
     * @param string $name
     * @param integer $tsgid
     * @param integer $type
     * @return integer
     */
    public function serverGroupCopy($ssgid, $name = null, $tsgid = 0, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        $this->serverGroupListReset();

        $sgid = $this->execute("servergroupcopy", ["ssgid" => $ssgid, "tsgid" => $tsgid, "name" => $name, "type" => $type])
                     ->toList();

        if ($tsgid && $name) {
            $this->serverGroupRename($tsgid, $name);
        }

        return count($sgid) ? $sgid["sgid"] : intval($tsgid);
    }

    /**
     * Renames the server group specified with $sgid.
     *
     * @param integer $sgid
     * @param string $name
     * @return void
     */
    public function serverGroupRename($sgid, $name)
    {
        $this->serverGroupListReset();

        $this->execute("servergrouprename", ["sgid" => $sgid, "name" => $name]);
    }

    /**
     * Deletes the server group specified with $sgid. If $force is set to 1, the server group
     * will be deleted even if there are clients within.
     *
     * @param integer $sgid
     * @param boolean $force
     * @return void
     */
    public function serverGroupDelete($sgid, $force = false)
    {
        $this->serverGroupListReset();

        $this->execute("servergroupdel", ["sgid" => $sgid, "force" => $force]);
    }

    /**
     * Returns the TeamSpeak3_Node_Servergroup object matching the given ID.
     *
     * @param integer $sgid
     * @return Servergroup
     * @throws ServerQueryException
     */
    public function serverGroupGetById($sgid)
    {
        if (!array_key_exists((string)$sgid, $this->serverGroupList())) {
            throw new ServerQueryException("invalid groupID", 0xA00);
        }

        return $this->sgroupList[intval((string)$sgid)];
    }

    /**
     * Returns the TeamSpeak3_Node_Servergroup object matching the given name.
     *
     * @param string $name
     * @param integer $type
     * @return Servergroup
     * @throws ServerQueryException
     */
    public function serverGroupGetByName($name, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        foreach ($this->serverGroupList() as $group) {
            if ($group["name"] == $name && $group["type"] == $type) {
                return $group;
            }
        }

        throw new ServerQueryException("invalid groupID", 0xA00);
    }

    /**
     * Returns a list of permissions assigned to the server group specified.
     *
     * @param integer $sgid
     * @param boolean $permsid
     * @return array
     */
    public function serverGroupPermList($sgid, $permsid = false)
    {
        return $this->execute("servergrouppermlist", ["sgid" => $sgid, $permsid ? "-permsid" : null])
                    ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to the server group specified. Multiple permissions
     * can be added by providing the four parameters of each permission in separate arrays.
     *
     * @param integer $sgid
     * @param integer $permid
     * @param integer $permvalue
     * @param integer $permnegated
     * @param integer $permskip
     * @return void
     */
    public function serverGroupPermAssign($sgid, $permid, $permvalue, $permnegated = 0, $permskip = 0)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("servergroupaddperm", ["sgid" => $sgid, $permident => $permid, "permvalue" => $permvalue, "permnegated" => $permnegated, "permskip" => $permskip]);
    }

    /**
     * Removes a set of specified permissions from the server group specified with $sgid. Multiple
     * permissions can be removed at once.
     *
     * @param integer $sgid
     * @param integer $permid
     * @return void
     */
    public function serverGroupPermRemove($sgid, $permid)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("servergroupdelperm", ["sgid" => $sgid, $permident => $permid]);
    }

    /**
     * Returns a list of clients assigned to the server group specified.
     *
     * @param integer $sgid
     * @return array
     */
    public function serverGroupClientList($sgid)
    {
        if ($this["virtualserver_default_server_group"] == $sgid) {
            return [];
        }

        return $this->execute("servergroupclientlist", ["sgid" => $sgid, "-names"])->toAssocArray("cldbid");
    }

    /**
     * Adds a client to the server group specified. Please note that a client cannot be
     * added to default groups or template groups.
     *
     * @param integer $sgid
     * @param integer $cldbid
     * @return void
     */
    public function serverGroupClientAdd($sgid, $cldbid)
    {
        $this->clientListReset();

        $this->execute("servergroupaddclient", ["sgid" => $sgid, "cldbid" => $cldbid]);
    }

    /**
     * Removes a client from the server group specified.
     *
     * @param integer $sgid
     * @param integer $cldbid
     * @return void
     */
    public function serverGroupClientDel($sgid, $cldbid)
    {
        $this->execute("servergroupdelclient", ["sgid" => $sgid, "cldbid" => $cldbid]);
    }

    /**
     * Returns an ordered array of regular server groups available based on a pre-defined
     * set of rules.
     *
     * @param integer $type
     * @return array
     * @throws ServerQueryException
     */
    public function serverGroupGetProfiles($type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        $profiles = [];

        foreach ($this->serverGroupList() as $sgid => $sgroup) {
            if ($sgroup["type"] != $type) {
                continue;
            }

            $profiles[$sgid] = [
                "b_permission_modify_power_ignore" => 0,
                "i_group_member_add_power" => 0,
                "i_group_member_remove_power" => 0,
                "i_needed_modify_power_count" => 0,
                "i_needed_modify_power_total" => 0,
                "i_permission_modify_power" => 0,
                "i_group_modify_power" => 0,
                "i_client_modify_power" => 0,
                "b_virtualserver_servergroup_create" => 0,
                "b_virtualserver_servergroup_delete" => 0,
                "b_client_ignore_bans" => 0,
                "b_client_ignore_antiflood" => 0,
                "b_group_is_permanent" => 0,
                "i_client_needed_ban_power" => 0,
                "i_client_needed_kick_power" => 0,
                "i_client_needed_move_power" => 0,
                "i_client_talk_power" => 0,
                "__sgid" => $sgid,
                "__name" => $sgroup->toString(),
                "__node" => $sgroup,
            ];

            try {
                $perms = $this->serverGroupPermList($sgid, true);
                $grant = isset($perms["i_permission_modify_power"]) ? $perms["i_permission_modify_power"]["permvalue"] : null;
            } catch (ServerQueryException $e) {
                /* ERROR_database_empty_result */
                if ($e->getCode() != 0x501) {
                    throw $e;
                }

                $perms = [];
                $grant = null;
            }

            foreach ($perms as $permsid => $perm) {
                if (in_array($permsid, array_keys($profiles[$sgid]))) {
                    $profiles[$sgid][$permsid] = $perm["permvalue"];
                } elseif (StringHelper::factory($permsid)->startsWith("i_needed_modify_power_")) {
                    if (!$grant || $perm["permvalue"] > $grant) {
                        continue;
                    }

                    $profiles[$sgid]["i_needed_modify_power_total"] = $profiles[$sgid]["i_needed_modify_power_total"] + $perm["permvalue"];
                    $profiles[$sgid]["i_needed_modify_power_count"]++;
                }
            }
        }

        array_multisort($profiles, SORT_DESC);

        return $profiles;
    }

    /**
     * Tries to identify the post powerful/weakest server group on the virtual server and returns
     * the ID.
     *
     * @param integer $mode
     * @param integer $type
     * @return Servergroup
     * @throws ServerQueryException
     */
    public function serverGroupIdentify(
        $mode = TeamSpeak3::GROUP_IDENTIFIY_STRONGEST,
        $type = TeamSpeak3::GROUP_DBTYPE_REGULAR
    ) {
        $profiles = $this->serverGroupGetProfiles($type);

        $best_guess_profile = ($mode == TeamSpeak3::GROUP_IDENTIFIY_STRONGEST) ? array_shift($profiles) : array_pop($profiles);

        return $this->serverGroupGetById($best_guess_profile["__sgid"]);
    }

    /**
     * Returns a list of channel groups available.
     *
     * @param array $filter
     * @return array|ChannelGroup[]
     * @throws ServerQueryException
     */
    public function channelGroupList(array $filter = [])
    {
        if ($this->cgroupList === null) {
            $this->cgroupList = $this->request("channelgrouplist")->toAssocArray("cgid");

            foreach ($this->cgroupList as $cgid => $group) {
                $this->cgroupList[$cgid] = new ChannelGroup($this, $group);
            }

            uasort($this->cgroupList, [__CLASS__, "sortGroupList"]);
        }

        return $this->filterList($this->cgroupList, $filter);
    }

    /**
     * Resets the list of channel groups.
     *
     * @return void
     */
    public function channelGroupListReset()
    {
        $this->cgroupList = null;
    }

    /**
     * Creates a new channel group using the name specified with $name and returns its ID.
     *
     * @param string $name
     * @param integer $type
     * @return integer
     */
    public function channelGroupCreate($name, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        $this->channelGroupListReset();

        $cgid = $this->execute("channelgroupadd", ["name" => $name, "type" => $type])->toList();

        return $cgid["cgid"];
    }

    /**
     * Creates a copy of an existing channel group specified by $scgid and returns the new groups ID.
     *
     * @param integer $scgid
     * @param string $name
     * @param integer $tcgid
     * @param integer $type
     * @return integer
     */
    public function channelGroupCopy($scgid, $name = null, $tcgid = 0, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        $this->channelGroupListReset();

        $cgid = $this->execute("channelgroupcopy", ["scgid" => $scgid, "tcgid" => $tcgid, "name" => $name, "type" => $type])
                     ->toList();

        if ($tcgid && $name) {
            $this->channelGroupRename($tcgid, $name);
        }

        return count($cgid) ? $cgid["cgid"] : intval($tcgid);
    }

    /**
     * Renames the channel group specified with $cgid.
     *
     * @param integer $cgid
     * @param string $name
     * @return void
     */
    public function channelGroupRename($cgid, $name)
    {
        $this->channelGroupListReset();

        $this->execute("channelgrouprename", ["cgid" => $cgid, "name" => $name]);
    }

    /**
     * Deletes the channel group specified with $cgid. If $force is set to 1, the channel group
     * will be deleted even if there are clients within.
     *
     * @param integer $sgid
     * @param boolean $force
     * @return void
     */
    public function channelGroupDelete($cgid, $force = false)
    {
        $this->channelGroupListReset();

        $this->execute("channelgroupdel", ["cgid" => $cgid, "force" => $force]);
    }

    /**
     * Returns the TeamSpeak3_Node_Channelgroup object matching the given ID.
     *
     * @param integer $cgid
     * @return ChannelGroup
     * @throws ServerQueryException
     */
    public function channelGroupGetById($cgid)
    {
        if (!array_key_exists((string)$cgid, $this->channelGroupList())) {
            throw new ServerQueryException("invalid groupID", 0xA00);
        }

        return $this->cgroupList[intval((string)$cgid)];
    }

    /**
     * Returns the TeamSpeak3_Node_Channelgroup object matching the given name.
     *
     * @param string $name
     * @param integer $type
     * @return ChannelGroup
     * @throws ServerQueryException
     */
    public function channelGroupGetByName($name, $type = TeamSpeak3::GROUP_DBTYPE_REGULAR)
    {
        foreach ($this->channelGroupList() as $group) {
            if ($group["name"] == $name && $group["type"] == $type) {
                return $group;
            }
        }

        throw new ServerQueryException("invalid groupID", 0xA00);
    }

    /**
     * Returns a list of permissions assigned to the channel group specified.
     *
     * @param integer $cgid
     * @param boolean $permsid
     * @return array
     */
    public function channelGroupPermList($cgid, $permsid = false)
    {
        return $this->execute("channelgrouppermlist", ["cgid" => $cgid, $permsid ? "-permsid" : null])
                    ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to the channel group specified. Multiple permissions
     * can be added by providing the two parameters of each permission in separate arrays.
     *
     * @param integer $cgid
     * @param integer $permid
     * @param integer $permvalue
     * @return void
     */
    public function channelGroupPermAssign($cgid, $permid, $permvalue)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channelgroupaddperm", ["cgid" => $cgid, $permident => $permid, "permvalue" => $permvalue]);
    }

    /**
     * Removes a set of specified permissions from the channel group specified with $cgid. Multiple
     * permissions can be removed at once.
     *
     * @param integer $cgid
     * @param integer $permid
     * @return void
     */
    public function channelGroupPermRemove($cgid, $permid)
    {
        if (!is_array($permid)) {
            $permident = (is_numeric($permid)) ? "permid" : "permsid";
        } else {
            $permident = (is_numeric(current($permid))) ? "permid" : "permsid";
        }

        $this->execute("channelgroupdelperm", ["cgid" => $cgid, $permident => $permid]);
    }

    /**
     * Returns all the client and/or channel IDs currently assigned to channel groups. All three
     * parameters are optional so you're free to choose the most suitable combination for your
     * requirements.
     *
     * @param integer|null $cgid
     * @param integer|null $cid
     * @param integer|null $cldbid
     * @param boolean $resolve
     * @return array
     * @throws ServerQueryException
     */
    public function channelGroupClientList($cgid = null, $cid = null, $cldbid = null, $resolve = false)
    {
        if ($this["virtualserver_default_channel_group"] == $cgid) {
            return [];
        }

        try {
            $result = $this->execute("channelgroupclientlist", ["cgid" => $cgid, "cid" => $cid, "cldbid" => $cldbid])
                           ->toArray();
        } catch (ServerQueryException $e) {
            /* ERROR_database_empty_result */
            if ($e->getCode() != 0x501) {
                throw $e;
            }

            $result = [];
        }

        if ($resolve) {
            foreach ($result as $k => $v) {
                $result[$k] = array_merge($v, $this->clientInfoDb($v["cldbid"]));
            }
        }

        return $result;
    }

    /**
     * Restores the default permission settings on the virtual server and returns a new initial
     * administrator privilege key.
     *
     * @return StringHelper
     */
    public function permReset()
    {
        $token = $this->request("permreset")->toList();

        Signal::getInstance()->emit("notifyTokencreated", $this, $token["token"]);

        return $token["token"];
    }

    /**
     * Removes any assignment of the permission specified with $permid on the selected virtual server
     * and returns the number of removed assignments on success.
     *
     * @param integer $permid
     * @return integer
     * @throws ServerQueryException
     */
    public function permRemoveAny($permid)
    {
        $assignments = $this->permissionFind($permid);

        foreach ($assignments as $assignment) {
            switch ($assignment["t"]) {
                case TeamSpeak3::PERM_TYPE_SERVERGROUP:
                    $this->serverGroupPermRemove($assignment["id1"], $assignment["p"]);
                    break;

                case TeamSpeak3::PERM_TYPE_CLIENT:
                    $this->clientPermRemove($assignment["id1"], $assignment["p"]);
                    break;

                case TeamSpeak3::PERM_TYPE_CHANNEL:
                    $this->channelPermRemove($assignment["id1"], $assignment["p"]);
                    break;

                case TeamSpeak3::PERM_TYPE_CHANNELGROUP:
                    $this->channelGroupPermRemove($assignment["id2"], $assignment["p"]);
                    break;

                case TeamSpeak3::PERM_TYPE_CHANNELCLIENT:
                    $this->channelClientPermRemove($assignment["id1"], $assignment["id2"], $assignment["p"]);
                    break;

                default:
                    throw new ServerQueryException("convert error", 0x604);
            }
        }

        return count($assignments);
    }

    /**
     * Initializes a file transfer upload. $clientftfid is an arbitrary ID to identify the file transfer on client-side.
     *
     * @param integer $clientftfid
     * @param integer $cid
     * @param string $name
     * @param integer $size
     * @param string $cpw
     * @param boolean $overwrite
     * @param boolean $resume
     * @return array
     * @throws ServerQueryException
     */
    public function transferInitUpload(
        $clientftfid,
        $cid,
        $name,
        $size,
        $cpw = "",
        $overwrite = false,
        $resume = false
    ) {
        $upload = $this->execute("ftinitupload", ["clientftfid" => $clientftfid, "cid" => $cid, "name" => $name, "cpw" => $cpw, "size" => $size, "overwrite" => $overwrite, "resume" => $resume])
                       ->toList();

        if (array_key_exists("status", $upload) && $upload["status"] != 0x00) {
            throw new ServerQueryException($upload["msg"], $upload["status"]);
        }

        $upload["cid"] = $cid;
        $upload["file"] = $name;

        if (!array_key_exists("ip", $upload) || $upload["ip"]->startsWith("0.0.0.0")) {
            $upload["ip"] = $this->getParent()->getAdapterHost();
            $upload["host"] = $upload["ip"];
        } else {
            $upload["ip"] = $upload["ip"]->section(",");
            $upload["host"] = $upload["ip"];
        }

        Signal::getInstance()->emit("filetransferUploadInit", $upload["ftkey"], $upload);

        return $upload;
    }

    /**
     * Initializes a file transfer download. $clientftfid is an arbitrary ID to identify the file transfer on client-side.
     *
     * @param integer $clientftfid
     * @param integer $cid
     * @param string $name
     * @param string $cpw
     * @param integer $seekpos
     * @return array
     * @throws ServerQueryException
     */
    public function transferInitDownload($clientftfid, $cid, $name, $cpw = "", $seekpos = 0)
    {
        $download = $this->execute("ftinitdownload", ["clientftfid" => $clientftfid, "cid" => $cid, "name" => $name, "cpw" => $cpw, "seekpos" => $seekpos])
                         ->toList();

        if (array_key_exists("status", $download) && $download["status"] != 0x00) {
            throw new ServerQueryException($download["msg"], $download["status"]);
        }

        $download["cid"] = $cid;
        $download["file"] = $name;

        if (!array_key_exists("ip", $download) || $download["ip"]->startsWith("0.0.0.0")) {
            $download["ip"] = $this->getParent()->getAdapterHost();
            $download["host"] = $download["ip"];
        } else {
            $download["ip"] = $download["ip"]->section(",");
            $download["host"] = $download["ip"];
        }

        Signal::getInstance()->emit("filetransferDownloadInit", $download["ftkey"], $download);

        return $download;
    }

    /**
     * Displays a list of running file transfers on the selected virtual server. The output contains the path to
     * which a file is uploaded to, the current transfer rate in bytes per second, etc.
     *
     * @return array
     */
    public function transferList()
    {
        return $this->request("ftlist")->toAssocArray("serverftfid");
    }

    /**
     * Stops the running file transfer with server-side ID $serverftfid.
     *
     * @param integer $serverftfid
     * @param boolean $delete
     * @return void
     */
    public function transferStop($serverftfid, $delete = false)
    {
        $this->execute("ftstop", ["serverftfid" => $serverftfid, "delete" => $delete]);
    }

    /**
     * Downloads and returns the servers icon file content.
     *
     * @return StringHelper|void
     * @throws ServerQueryException
     */
    public function iconDownload()
    {
        if ($this->iconIsLocal("virtualserver_icon_id") || $this["virtualserver_icon_id"] == 0) {
            return;
        }

        $download = $this->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->iconGetName("virtualserver_icon_id"));
        $transfer = TeamSpeak3::factory("filetransfer://" . (strstr($download["host"], ":") !== false ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"]);

        return $transfer->download($download["ftkey"], $download["size"]);
    }

    /**
     * Uploads a given icon file content to the server and returns the ID of the icon.
     *
     * @param string $data
     * @return integer
     * @throws ServerQueryException
     */
    public function iconUpload($data)
    {
        $crc = crc32($data);
        $size = strlen($data);

        $upload = $this->transferInitUpload(rand(0x0000, 0xFFFF), 0, "/icon_" . $crc, $size);
        $transfer = TeamSpeak3::factory("filetransfer://" . (strstr($upload["host"], ":") !== false ? "[" . $upload["host"] . "]" : $upload["host"]) . ":" . $upload["port"]);

        $transfer->upload($upload["ftkey"], $upload["seekpos"], $data);

        return $crc;
    }

    /**
     * Changes the virtual server configuration using given properties.
     *
     * @param array $properties
     * @return void
     */
    public function modify(array $properties)
    {
        $this->execute("serveredit", $properties);
        $this->resetNodeInfo();
    }

    /**
     * Sends a text message to all clients on the virtual server.
     *
     * @param string $msg
     * @return void
     */
    public function message($msg)
    {
        $this->execute("sendtextmessage", ["msg" => $msg, "target" => $this->getId(), "targetmode" => TeamSpeak3::TEXTMSG_SERVER]);
    }

    /**
     * Returns a list of offline messages you've received. The output contains the senders unique identifier,
     * the messages subject, etc.
     *
     * @return array
     */
    public function messageList()
    {
        return $this->request("messagelist")->toAssocArray("msgid");
    }

    /**
     * Sends an offline message to the client specified by $cluid.
     *
     * @param string $cluid
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function messageCreate($cluid, $subject, $message)
    {
        $this->execute("messageadd", ["cluid" => $cluid, "subject" => $subject, "message" => $message]);
    }

    /**
     * Deletes an existing offline message with ID $msgid from your inbox.
     *
     * @param integer $msgid
     * @return void
     */
    public function messageDelete($msgid)
    {
        $this->execute("messagedel", ["msgid" => $msgid]);
    }

    /**
     * Returns an existing offline message with ID $msgid from your inbox.
     *
     * @param integer $msgid
     * @param boolean $flag_read
     * @return array
     */
    public function messageRead($msgid, $flag_read = true)
    {
        $msg = $this->execute("messageget", ["msgid" => $msgid])->toList();

        if ($flag_read) {
            $this->execute("messageget", ["msgid" => $msgid, "flag" => $flag_read]);
        }

        return $msg;
    }

    /**
     * Creates and returns snapshot data for the selected virtual server.
     *
     * @param int $mode
     * @return string
     */
    public function snapshotCreate($mode = TeamSpeak3::SNAPSHOT_STRING)
    {
        $snapshot = $this->request("serversnapshotcreate")->toString(false);

        switch ($mode) {
            case TeamSpeak3::SNAPSHOT_BASE64:
                return $snapshot->toBase64();
                break;

            case TeamSpeak3::SNAPSHOT_HEXDEC:
                return $snapshot->toHex();
                break;

            default:
                return (string)$snapshot;
                break;
        }
    }

    /**
     * Deploys snapshot data on the selected virtual server. If no virtual server is selected (ID 0),
     * the data will be used to create a new virtual server from scratch.
     *
     * @param string $data
     * @param int $mode
     * @return array
     * @throws HelperException
     */
    public function snapshotDeploy($data, $mode = TeamSpeak3::SNAPSHOT_STRING)
    {
        switch ($mode) {
            case TeamSpeak3::SNAPSHOT_BASE64:
                $data = StringHelper::fromBase64($data);
                break;

            case TeamSpeak3::SNAPSHOT_HEXDEC:
                $data = StringHelper::fromHex($data);
                break;

            default:
                $data = StringHelper::factory($data);
                break;
        }

        $detail = $this->request("serversnapshotdeploy -mapping " . $data)->toList();

        if (isset($detail[0]["sid"])) {
            Signal::getInstance()->emit("notifyServercreated", $this->getParent(), $detail[0]["sid"]);

            $server = array_shift($detail);
        } else {
            $server = [];
        }

        $server["mapping"] = $detail;

        return $server;
    }

    /**
     * Registers for a specified category of events on a virtual server to receive notification
     * messages. Depending on the notifications you've registered for, the server will send you
     * a message on every event.
     *
     * @param string $event
     * @param integer $id
     * @return void
     */
    public function notifyRegister($event, $id = 0)
    {
        $this->execute("servernotifyregister", ["event" => $event, "id" => $id]);
    }

    /**
     * Unregisters all events previously registered with servernotifyregister so you will no
     * longer receive notification messages.
     *
     * @return void
     */
    public function notifyUnregister()
    {
        $this->request("servernotifyunregister");
    }

    /**
     * Alias for privilegeKeyList().
     *
     * @deprecated
     */
    public function tokenList($translate = false)
    {
        return $this->privilegeKeyList();
    }

    /**
     * Returns a list of privilege keys (tokens) available. If $resolve is set to TRUE the values
     * of token_id1 and token_id2 will be translated into the appropriate group and/or channel
     * names.
     *
     * @param boolean $resolve
     * @return array
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function privilegeKeyList($resolve = false)
    {
        $tokens = $this->request("privilegekeylist")->toAssocArray("token");

        if ($resolve) {
            foreach ($tokens as $token => $array) {
                $func = $array["token_type"] ? "channelGroupGetById" : "serverGroupGetById";

                try {
                    $tokens[$token]["token_id1"] = $this->$func($array["token_id1"])->name;
                } catch (NodeException $e) {
                    /* ERROR_channel_invalid_id */
                    if ($e->getCode() != 0xA00) {
                        throw $e;
                    }
                }

                try {
                    if ($array["token_type"]) {
                        $tokens[$token]["token_id2"] = $this->channelGetById($array["token_id2"])->getPathway();
                    }
                } catch (NodeException $e) {
                    /* ERROR_permission_invalid_group_id */
                    if ($e->getCode() != 0x300) {
                        throw $e;
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * Alias for privilegeKeyCreate().
     *
     * @deprecated
     */
    public function tokenCreate(
        $type = TeamSpeak3::TOKEN_SERVERGROUP,
        $id1,
        $id2 = 0,
        $description = null,
        $customset = null
    ) {
        return $this->privilegeKeyCreate($type, $id1, $id2, $description, $customset);
    }

    /**
     * Creates a new privilege key (token) and returns the key.
     *
     * @param integer $type
     * @param integer $id1
     * @param integer $id2
     * @param string $description
     * @param string $customset
     * @return StringHelper
     */
    public function privilegeKeyCreate(
        $id1,
        $id2 = 0,
        $type = TeamSpeak3::TOKEN_SERVERGROUP,
        $description = null,
        $customset = null
    ) {
        $token = $this->execute("privilegekeyadd", ["tokentype" => $type, "tokenid1" => $id1, "tokenid2" => $id2, "tokendescription" => $description, "tokencustomset" => $customset])
                      ->toList();

        Signal::getInstance()->emit("notifyTokencreated", $this, $token["token"]);

        return $token["token"];
    }

    /**
     * Alias for privilegeKeyDelete().
     *
     * @deprecated
     */
    public function tokenDelete($token)
    {
        $this->privilegeKeyDelete($token);
    }

    /**
     * Deletes a token specified by key $token.
     *
     * @param string $token
     * @return void
     */
    public function privilegeKeyDelete($token)
    {
        $this->execute("privilegekeydelete", ["token" => $token]);
    }

    /**
     * Alias for privilegeKeyUse().
     *
     * @deprecated
     */
    public function tokenUse($token)
    {
        $this->privilegeKeyUse($token);
    }

    /**
     * Use a token key gain access to a server or channel group. Please note that the server will
     * automatically delete the token after it has been used.
     *
     * @param string $token
     * @return void
     */
    public function privilegeKeyUse($token)
    {
        $this->execute("privilegekeyuse", ["token" => $token]);
    }

    /**
     * Returns a list of custom client properties specified by $ident.
     *
     * @param string $ident
     * @param string $pattern
     * @return array
     */
    public function customSearch($ident, $pattern = "%")
    {
        return $this->execute("customsearch", ["ident" => $ident, "pattern" => $pattern])->toArray();
    }

    /**
     * Returns a list of custom properties for the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @return array
     */
    public function customInfo($cldbid)
    {
        return $this->execute("custominfo", ["cldbid" => $cldbid])->toArray();
    }

    /**
     * Creates or updates a custom property for the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @param string $ident
     * @param string $value
     * @return void
     */
    public function customSet($cldbid, $ident, $value)
    {
        $this->execute("customset", ["cldbid" => $cldbid, "ident" => $ident, "value" => $value]);
    }

    /**
     * Removes a custom property from the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @param string $ident
     * @return void
     */
    public function customDelete($cldbid, $ident)
    {
        $this->execute("customdelete", ["cldbid" => $cldbid, "ident" => $ident]);
    }

    /**
     * Returns a list of active bans on the selected virtual server.
     *
     * @return array
     */
    public function banList($offset = null, $limit = null)
    {
        return $this->execute("banlist -count", ["start" => $offset, "duration" => $limit])->toAssocArray("banid");
    }

    /**
     * Returns the number of bans on the selected virtual server.
     *
     * @return integer
     */
    public function banCount()
    {
        return current($this->execute("banlist -count", ["duration" => 1])->toList("count"));
    }

    /**
     * Deletes all active ban rules from the server.
     *
     * @return void
     */
    public function banListClear()
    {
        $this->request("bandelall");
    }

    /**
     * Adds a new ban rule on the selected virtual server. All parameters are optional but at least one
     * of the following rules must be set: ip, name, or uid.
     *
     * @param array $rules
     * @param integer $timeseconds
     * @param string $reason
     * @return integer
     */
    public function banCreate(array $rules, $timeseconds = null, $reason = null)
    {
        $rules["time"] = $timeseconds;
        $rules["banreason"] = $reason;

        $banid = $this->execute("banadd", $rules)->toList();

        return $banid["banid"];
    }

    /**
     * Deletes the specified ban rule from the server.
     *
     * @param integer $banid
     * @return void
     */
    public function banDelete($banid)
    {
        $this->execute("bandel", ["banid" => $banid]);
    }

    /**
     * Returns a list of complaints on the selected virtual server. If $tcldbid is specified, only
     * complaints about the targeted client will be shown.
     *
     * @param integer $tcldbid
     * @return array
     */
    public function complaintList($tcldbid = null)
    {
        return $this->execute("complainlist", ["tcldbid" => $tcldbid])->toArray();
    }

    /**
     * Deletes all active complaints about the client with database ID $tcldbid from the server.
     *
     * @param integer $tcldbid
     * @return void
     */
    public function complaintListClear($tcldbid)
    {
        $this->execute("complaindelall", ["tcldbid" => $tcldbid]);
    }

    /**
     * Submits a complaint about the client with database ID $tcldbid to the server.
     *
     * @param integer $tcldbid
     * @param string $message
     * @return void
     */
    public function complaintCreate($tcldbid, $message)
    {
        $this->execute("complainadd", ["tcldbid" => $tcldbid, "message" => $message]);
    }

    /**
     * Deletes the complaint about the client with ID $tcldbid submitted by the client with ID $fcldbid from the server.
     *
     * @param integer $tcldbid
     * @param integer $fcldbid
     * @return void
     */
    public function complaintDelete($tcldbid, $fcldbid)
    {
        $this->execute("complaindel", ["tcldbid" => $tcldbid, "fcldbid" => $fcldbid]);
    }

    /**
     * Returns a list of temporary server passwords.
     *
     * @param boolean $resolve
     * @return array
     */
    public function tempPasswordList($resolve = false)
    {
        $passwords = $this->request("servertemppasswordlist")->toAssocArray("pw_clear");

        if ($resolve) {
            foreach ($passwords as $password => $array) {
                try {
                    $channel = $this->channelGetById($array["tcid"]);

                    $passwords[$password]["tcname"] = $channel->toString();
                    $passwords[$password]["tcpath"] = $channel->getPathway();
                } catch (NodeException $e) {
                    /* ERROR_channel_invalid_id */
                    if ($e->getCode() != 0xA00) {
                        throw $e;
                    }
                }
            }
        }

        return $passwords;
    }

    /**
     * Sets a new temporary server password specified with $pw. The temporary password will be
     * valid for the number of seconds specified with $duration. The client connecting with this
     * password will automatically join the channel specified with $tcid. If tcid is set to 0,
     * the client will join the default channel.
     *
     * @param string $pw
     * @param integer $duration
     * @param integer $tcid
     * @param string $tcpw
     * @param string $desc
     * @return void
     */
    public function tempPasswordCreate($pw, $duration, $tcid = 0, $tcpw = "", $desc = "")
    {
        $this->execute("servertemppasswordadd", ["pw" => $pw, "duration" => $duration, "tcid" => $tcid, "tcpw" => $tcpw, "desc" => $desc]);
    }

    /**
     * Deletes the temporary server password specified with $pw.
     *
     * @param string $pw
     * @return void
     */
    public function tempPasswordDelete($pw)
    {
        $this->execute("servertemppassworddel", ["pw" => $pw]);
    }

    /**
     * Displays a specified number of entries (1-100) from the servers log.
     *
     * @param integer $lines
     * @param integer $begin_pos
     * @param boolean $reverse
     * @param boolean $instance
     * @return array
     */
    public function logView($lines = 30, $begin_pos = null, $reverse = null, $instance = null)
    {
        return $this->execute("logview", ["lines" => $lines, "begin_pos" => $begin_pos, "instance" => $instance, "reverse" => $reverse])
                    ->toArray();
    }

    /**
     * Writes a custom entry into the virtual server log.
     *
     * @param string $logmsg
     * @param integer $loglevel
     * @return void
     */
    public function logAdd($logmsg, $loglevel = TeamSpeak3::LOGLEVEL_INFO)
    {
        $this->execute("logadd", ["logmsg" => $logmsg, "loglevel" => $loglevel]);
    }

    /**
     * Returns detailed connection information of the virtual server.
     *
     * @return array
     */
    public function connectionInfo()
    {
        return $this->request("serverrequestconnectioninfo")->toList();
    }

    /**
     * Deletes the virtual server.
     *
     * @return void
     */
    public function delete()
    {
        $this->getParent()->serverDelete($this->getId());
    }

    /**
     * Starts the virtual server.
     *
     * @return void
     */
    public function start()
    {
        $this->getParent()->serverStart($this->getId());
    }

    /**
     * Stops the virtual server.
     *
     * @param string $msg
     * @return void
     */
    public function stop($msg = null)
    {
        $this->getParent()->serverStop($this->getId(), $msg);
    }

    /**
     * Sends a plugin command to all clients connected to the server.
     *
     * @param string $plugin
     * @param string $data
     * @return void
     */
    public function sendPluginCmd($plugin, $data)
    {
        $this->execute("plugincmd", ["name" => $plugin, "data" => $data, "targetmode" => TeamSpeak3::PLUGINCMD_SERVER]);
    }

    /**
     * Changes the properties of your own client connection.
     *
     * @param array $properties
     * @return void
     */
    public function selfUpdate(array $properties)
    {
        $this->execute("clientupdate", $properties);

        foreach ($properties as $ident => $value) {
            $this->whoamiSet($ident, $value);
        }
    }

    /**
     * Updates your own ServerQuery login credentials using a specified username. The password
     * will be auto-generated.
     *
     * @param string $username
     * @return TeamSpeak3_Helper_String
     */
    public function selfUpdateLogin($username)
    {
        $password = $this->execute("clientsetserverquerylogin", ["client_login_name" => $username])->toList();

        return $password["client_login_password"];
    }

    /**
     * Returns an array containing the permission overview of your own client.
     *
     * @return array
     */
    public function selfPermOverview()
    {
        return $this->execute("permoverview", ["cldbid" => $this->whoamiGet("client_database_id"), "cid" => $this->whoamiGet("client_channel_id"), "permid" => 0])
                    ->toArray();
    }

    /**
     * @ignore
     */
    protected function fetchNodeList()
    {
        $this->nodeList = [];

        foreach ($this->channelList() as $channel) {
            if ($channel["pid"] == 0) {
                $this->nodeList[] = $channel;
            }
        }
    }

    /**
     * @ignore
     */
    protected function fetchNodeInfo()
    {
        $this->nodeInfo = array_merge($this->nodeInfo, $this->request("serverinfo")->toList());
    }

    /**
     * Internal callback funtion for sorting of client objects.
     *
     * @param Client $a
     * @param Client $b
     * @return integer
     * @throws \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException
     */
    protected static function sortClientList(Client $a, Client $b)
    {
        if (get_class($a) != get_class($b)) {
            return 0;

            /* workaround for PHP bug #50688 */
            throw new \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException("invalid parameter", 0x602);
        }

        if (!$a instanceof Client) {
            return 0;

            /* workaround for PHP bug #50688 */
            throw new \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException("convert error", 0x604);
        }

        if ($a->getProperty("client_talk_power", 0) != $b->getProperty("client_talk_power", 0)) {
            return ($a->getProperty("client_talk_power", 0) > $b->getProperty("client_talk_power", 0)) ? -1 : 1;
        }

        if ($a->getProperty("client_is_talker", 0) != $b->getProperty("client_is_talker", 0)) {
            return ($a->getProperty("client_is_talker", 0) > $b->getProperty("client_is_talker", 0)) ? -1 : 1;
        }

        return strcmp(strtolower($a["client_nickname"]), strtolower($b["client_nickname"]));
    }

    /**
     * Internal callback funtion for sorting of group objects.
     *
     * @param Node $a
     * @param Node $b
     * @return integer
     * @throws \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException
     */
    protected static function sortGroupList(Node $a, Node $b)
    {
        if (get_class($a) != get_class($b)) {
            return 0;

            /* workaround for PHP bug #50688 */
            throw new \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException("invalid parameter", 0x602);
        }

        if (!$a instanceof Servergroup && !$a instanceof ChannelGroup) {
            return 0;

            /* workaround for PHP bug #50688 */
            throw new \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException("convert error", 0x604);
        }

        if ($a->getProperty("sortid", 0) != $b->getProperty("sortid", 0) && $a->getProperty("sortid", 0) != 0 && $b->getProperty("sortid", 0) != 0) {
            return ($a->getProperty("sortid", 0) < $b->getProperty("sortid", 0)) ? -1 : 1;
        }

        return ($a->getId() < $b->getId()) ? -1 : 1;
    }

    /**
     * Internal callback funtion for sorting of file list items.
     *
     * @param array $a
     * @param array $b
     * @return integer
     * @throws \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException
     */
    protected static function sortFileList(array $a, array $b)
    {
        if (!array_key_exists("src", $a) || !array_key_exists("src", $b) || !array_key_exists("type", $a) || !array_key_exists("type", $b)) {
            return 0;

            throw new \PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\ServerQueryException("invalid parameter", 0x602);
        }

        if ($a["type"] != $b["type"]) {
            return ($a["type"] < $b["type"]) ? -1 : 1;
        }

        return strcmp(strtolower($a["src"]), strtolower($b["src"]));
    }

    /**
     * Returns TRUE if the virtual server is online.
     *
     * @return boolean
     */
    public function isOnline()
    {
        return ($this["virtualserver_status"] == "online") ? true : false;
    }

    /**
     * Returns TRUE if the virtual server is offline.
     *
     * @return boolean
     */
    public function isOffline()
    {
        return ($this["virtualserver_status"] == "offline") ? true : false;
    }

    /**
     * Returns a unique identifier for the node which can be used as a HTML property.
     *
     * @return string
     */
    public function getUniqueId()
    {
        return $this->getParent()->getUniqueId() . "_s" . $this->getId();
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon()
    {
        if ($this["virtualserver_clientsonline"] - $this["virtualserver_queryclientsonline"] >= $this["virtualserver_maxclients"]) {
            return "server_full";
        } elseif ($this["virtualserver_flag_password"]) {
            return "server_pass";
        } else {
            return "server_open";
        }
    }

    /**
     * Returns a symbol representing the node.
     *
     * @return string
     */
    public function getSymbol()
    {
        return "$";
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this["virtualserver_name"];
    }
}
