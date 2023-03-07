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
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class Server
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @class Server
 * @brief Class describing a TeamSpeak 3 virtual server and all it's parameters.
 */
class Server extends Node
{
    /**
     * @ignore
     */
    protected array|null $channelList = null;

    /**
     * @ignore
     */
    protected array|null $clientList = null;

    /**
     * @ignore
     */
    protected array|null $sgroupList = null;

    /**
     * @ignore
     */
    protected array|null $cgroupList = null;

    /**
     * Server constructor.
     *
     * @param Host $host
     * @param array $info
     * @param string $index
     * @throws ServerQueryException
     */
    public function __construct(Host $host, array $info, string $index = "virtualserver_id")
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function request(string $cmd, bool $throw = true): Reply
    {
        if ($this->getId() != $this->getParent()->serverSelectedId()) {
            $this->getParent()->serverSelect($this->getId());
        }

        return $this->getParent()->request($cmd, $throw);
    }

    /**
     * Returns an array filled with PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel objects.
     *
     * @param array $filter
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelList(array $filter = []): array
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
    public function channelListReset(): void
    {
        $this->resetNodeList();
        $this->channelList = null;
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel object representing the default channel.
     *
     * @return Channel
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGetDefault(): Channel
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelCreate(array $properties): int
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
     * @param integer|Node $cid
     * @param boolean $force
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelDelete(int|Node $cid, bool $force = false): void
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
     * @param integer|null $order
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelMove(int $cid, int $pid, int $order = null): void
    {
        $this->execute("channelmove", ["cid" => $cid, "cpid" => $pid, "order" => $order]);
        $this->channelListReset();
    }

    /**
     * Returns TRUE if the given PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel object is a spacer.
     *
     * @param Channel $channel
     * @return boolean
     */
    public function channelIsSpacer(Channel $channel): bool
    {
        return preg_match("/\[[^]]*spacer[^]]*]/", $channel) && $channel["channel_flag_permanent"] && !$channel["pid"];
    }

    /**
     * Creates a new channel spacer and returns the new ID. The first parameter $ident is used to create a
     * unique spacer name on the virtual server.
     *
     * @param string $ident
     * @param mixed|int $type
     * @param integer $align
     * @param integer|null $order
     * @param integer $maxclients
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelSpacerCreate(
        string $ident,
        int    $type = TeamSpeak3::SPACER_SOLIDLINE,
        int    $align = TeamSpeak3::SPACER_ALIGN_REPEAT,
        int    $order = null,
        int    $maxclients = 0
    ): int {
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

        $properties["channel_name"] = match ($align) {
            TeamSpeak3::SPACER_ALIGN_REPEAT => "[*spacer" . $ident . "]",
            TeamSpeak3::SPACER_ALIGN_LEFT => "[lspacer" . $ident . "]",
            TeamSpeak3::SPACER_ALIGN_RIGHT => "[rspacer" . $ident . "]",
            TeamSpeak3::SPACER_ALIGN_CENTER => "[cspacer" . $ident . "]",
            default => throw new ServerQueryException("missing required parameter", 0x606),
        };

        $properties["channel_name"] .= match ($type) {
            (string)TeamSpeak3::SPACER_SOLIDLINE => "___",
            (string)TeamSpeak3::SPACER_DASHLINE => "---",
            (string)TeamSpeak3::SPACER_DOTLINE => "...",
            (string)TeamSpeak3::SPACER_DASHDOTLINE => "-.-",
            (string)TeamSpeak3::SPACER_DASHDOTDOTLINE => "-..",
            default => strval($type),
        };

        return $this->channelCreate($properties);
    }

    /**
     * Returns the possible type of a channel spacer.
     *
     * @param integer $cid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelSpacerGetType(int $cid): int
    {
        $channel = $this->channelGetById($cid);

        if (!$this->channelIsSpacer($channel)) {
            throw new ServerQueryException("invalid channel flags", 0x307);
        }

        return match ($channel["channel_name"]->section("]", 1)) {
            "___" => TeamSpeak3::SPACER_SOLIDLINE,
            "---" => TeamSpeak3::SPACER_DASHLINE,
            "..." => TeamSpeak3::SPACER_DOTLINE,
            "-.-" => TeamSpeak3::SPACER_DASHDOTLINE,
            "-.." => TeamSpeak3::SPACER_DASHDOTDOTLINE,
            default => TeamSpeak3::SPACER_CUSTOM,
        };
    }

    /**
     * Returns the possible alignment of a channel spacer.
     *
     * @param integer $cid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelSpacerGetAlign(int $cid): int
    {
        $channel = $this->channelGetById($cid);

        if (!$this->channelIsSpacer($channel) || !preg_match("/\[(.*)spacer.*]/", $channel, $matches) || !isset($matches[1])) {
            throw new ServerQueryException("invalid channel flags", 0x307);
        }

        return match ($matches[1]) {
            "*" => TeamSpeak3::SPACER_ALIGN_REPEAT,
            "c" => TeamSpeak3::SPACER_ALIGN_CENTER,
            "r" => TeamSpeak3::SPACER_ALIGN_RIGHT,
            default => TeamSpeak3::SPACER_ALIGN_LEFT,
        };
    }

    /**
     * Returns a list of permissions defined for a specific channel.
     *
     * @param integer $cid
     * @param boolean $permsid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelPermList(int $cid, bool $permsid = false): array
    {
        return $this->execute("channelpermlist", ["cid" => $cid, $permsid ? "-permsid" : null])
            ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to a channel. Multiple permissions can be added by
     * providing the two parameters of each permission.
     *
     * @param integer $cid
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelPermAssign(int $cid, int|array $permid, int|array $permvalue): void
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
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelPermRemove(int $cid, int|array $permid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelClientPermList(int $cid, int $cldbid, bool $permsid = false): array
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
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelClientPermAssign(int $cid, int $cldbid, int|array $permid, int|array $permvalue): void
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
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelClientPermRemove(int $cid, int $cldbid, int|array $permid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelFileList(int $cid, string $cpw = "", string $path = "/", bool $recursive = false): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelFileInfo(int $cid, string $cpw = "", string $name = "/"): array
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
     * @param integer|null $tcid
     * @param string|null $tcpw
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelFileRename(int $cid, string $cpw = "", string $oldname = "/", string $newname = "/", int $tcid = null, string $tcpw = null): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelFileDelete(int $cid, string $cpw = "", string $name = "/"): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelDirCreate(int $cid, string $cpw = "", string $dirname = "/"): void
    {
        $this->execute("ftcreatedir", ["cid" => $cid, "cpw" => $cpw, "dirname" => $dirname]);
    }

    /**
     * Returns the level of a channel.
     *
     * @param integer $cid
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGetLevel(int $cid): int
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGetPathway(int $cid): string
    {
        $channel = $this->channelGetById($cid);
        $pathway = $channel["channel_name"];

        if ($channel["pid"]) {
            $pathway = $this->channelGetPathway($channel["pid"]) . "/" . $channel["channel_name"];
        }

        return $pathway;
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel object matching the given ID.
     *
     * @param integer $cid
     * @return Channel
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGetById(int $cid): Channel
    {
        if (!array_key_exists((string)$cid, $this->channelList())) {
            throw new ServerQueryException("invalid channelID", 0x300);
        }

        return $this->channelList[intval((string)$cid)];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel object matching the given name.
     *
     * @param string $name
     * @return Channel
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGetByName(string $name): Channel
    {
        foreach ($this->channelList() as $channel) {
            if ($channel["channel_name"] == $name) {
                return $channel;
            }
        }

        throw new ServerQueryException("invalid channelID", 0x300);
    }

    /**
     * Returns an array filled with PlanetTeamSpeak\TeamSpeak3Framework\Node\Client objects.
     *
     * @param array $filter
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientList(array $filter = []): array
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
    public function clientListReset(): void
    {
        $this->resetNodeList();
        $this->clientList = null;
    }

    /**
     * Returns a list of clients matching a given name pattern.
     *
     * @param string $pattern
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientFind(string $pattern): array
    {
        return $this->execute("clientfind", ["pattern" => $pattern])->toAssocArray("clid");
    }

    /**
     * Returns a list of client identities known by the virtual server. By default, the server spits out 25 entries
     * at once.
     *
     * @param integer|null $offset
     * @param integer|null $limit
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientListDb(int $offset = null, int $limit = null): array
    {
        return $this->execute("clientdblist -count", ["start" => $offset, "duration" => $limit])
            ->toAssocArray("cldbid");
    }

    /**
     * Returns the number of client identities known by the virtual server.
     *
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientCountDb(): int
    {
        return current($this->execute("clientdblist -count", ["duration" => 1])->toList("count"));
    }

    /**
     * Returns a list of properties from the database for the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientInfoDb(int $cldbid): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientFindDb(string $pattern, bool $uid = false): array
    {
        return array_keys($this->execute("clientdbfind", ["pattern" => $pattern, ($uid) ? "-uid" : null, "-details"])
            ->toAssocArray("cldbid"));
    }

    /**
     * Returns the number of regular clients online.
     *
     * @return integer
     */
    public function clientCount(): int
    {
        if ($this->isOffline()) {
            return 0;
        }

        return $this["virtualserver_clientsonline"] - $this["virtualserver_queryclientsonline"];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Client object matching the given ID.
     *
     * @param integer $clid
     * @return Client
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetById(int $clid): Client
    {
        if (!array_key_exists((string)$clid, $this->clientList())) {
            throw new ServerQueryException("invalid clientID", 0x200);
        }

        return $this->clientList[intval((string)$clid)];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Client object matching the given name.
     *
     * @param string $name
     * @return Client
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetByName(string $name): Client
    {
        foreach ($this->clientList() as $client) {
            if ($client["client_nickname"] == $name) {
                return $client;
            }
        }

        throw new ServerQueryException("invalid clientID", 0x200);
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Client object matching the given unique identifier.
     *
     * @param string $uid
     * @return Client
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetByUid(string $uid): Client
    {
        foreach ($this->clientList() as $client) {
            if ($client["client_unique_identifier"] == $uid) {
                return $client;
            }
        }

        throw new ServerQueryException("invalid clientID", 0x200);
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Client object matching the given database ID.
     *
     * @param integer $dbid
     * @return Client
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetByDbid(int $dbid): Client
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetNameByUid(string $cluid): array
    {
        return $this->execute("clientgetnamefromuid", ["cluid" => $cluid])->toList();
    }

    /**
     * Returns an array containing a list of active client connections using the unique identifier
     * specified with $cluid.
     *
     * @param string $cluid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetIdsByUid(string $cluid): array
    {
        return $this->execute("clientgetids", ["cluid" => $cluid])->toAssocArray("clid");
    }

    /**
     * Returns an array containing the last known nickname and the unique identifier of the client
     * matching the database ID specified with $cldbid.
     *
     * @param string $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetNameByDbid(string $cldbid): array
    {
        return $this->execute("clientgetnamefromdbid", ["cldbid" => $cldbid])->toList();
    }

    /**
     * Returns an array containing the names and IDs of all server groups the client specified with
     * $cldbid is is currently residing in.
     *
     * @param string $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientGetServerGroupsByDbid(string $cldbid): array
    {
        return $this->execute("servergroupsbyclientid", ["cldbid" => $cldbid])->toAssocArray("sgid");
    }

    /**
     * Moves a client to another channel.
     *
     * @param int|Node $clid
     * @param int|Node $cid
     * @param null $cpw
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientMove(int|Node $clid, int|Node $cid, $cpw = null): void
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
     * @param null $reasonmsg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientKick(int $clid, int $reasonid = TeamSpeak3::KICK_CHANNEL, $reasonmsg = null): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientPoke(int $clid, string $msg): void
    {
        $this->execute("clientpoke", ["clid" => $clid, "msg" => $msg]);
    }

    /**
     * Bans the client specified with ID $clid from the server. Please note that this will create three separate
     * ban rules for the targeted clients IP address, the unique identifier and the myTeamSpeak ID (if available).
     *
     * @param integer $clid
     * @param integer|null $timeseconds
     * @param string|null $reason
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientBan(int $clid, int $timeseconds = null, string $reason = null): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientModifyDb(string $cldbid, array $properties): void
    {
        $properties["cldbid"] = $cldbid;

        $this->execute("clientdbedit", $properties);
    }

    /**
     * Deletes a clients properties from the database.
     *
     * @param string $cldbid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientDeleteDb(string $cldbid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientSetChannelGroup(int $cldbid, int $cid, int $cgid): void
    {
        $this->execute("setclientchannelgroup", ["cldbid" => $cldbid, "cid" => $cid, "cgid" => $cgid]);
    }

    /**
     * Returns a list of permissions defined for a client.
     *
     * @param integer $cldbid
     * @param boolean $permsid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientPermList(int $cldbid, bool $permsid = false): array
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
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @param bool|bool[] $permskip
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientPermAssign(int $cldbid, int|array $permid, int|array $permvalue, bool|array $permskip = false): void
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
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function clientPermRemove(int $cldbid, int|array $permid): void
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
     * @return array
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function serverGroupList(array $filter = []): array
    {
        if ($this->sgroupList === null) {
            $this->sgroupList = $this->request("servergrouplist")->toAssocArray("sgid");

            foreach ($this->sgroupList as $sgid => $group) {
                $this->sgroupList[$sgid] = new ServerGroup($this, $group);
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
    public function serverGroupListReset(): void
    {
        $this->sgroupList = null;
    }

    /**
     * Creates a new server group using the name specified with $name and returns its ID.
     *
     * @param string $name
     * @param integer $type
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupCreate(string $name, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        $this->serverGroupListReset();

        $sgid = $this->execute("servergroupadd", ["name" => $name, "type" => $type])->toList();

        return $sgid["sgid"];
    }

    /**
     * Creates a copy of an existing server group specified by $ssgid and returns the new groups ID.
     *
     * @param integer $ssgid
     * @param string|null $name
     * @param integer $tsgid
     * @param integer $type
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupCopy(int $ssgid, string $name = null, int $tsgid = 0, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        $this->serverGroupListReset();

        $sgid = $this->execute("servergroupcopy", ["ssgid" => $ssgid, "tsgid" => $tsgid, "name" => $name, "type" => $type])
            ->toList();

        if ($tsgid && $name) {
            $this->serverGroupRename($tsgid, $name);
        }

        return count($sgid) ? $sgid["sgid"] : $tsgid;
    }

    /**
     * Renames the server group specified with $sgid.
     *
     * @param integer $sgid
     * @param string $name
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupRename(int $sgid, string $name): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupDelete(int $sgid, bool $force = false): void
    {
        $this->serverGroupListReset();

        $this->execute("servergroupdel", ["sgid" => $sgid, "force" => $force]);
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Servergroup object matching the given ID.
     *
     * @param integer $sgid
     * @return ServerGroup
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function serverGroupGetById(int $sgid): ServerGroup
    {
        if (!array_key_exists((string)$sgid, $this->serverGroupList())) {
            throw new ServerQueryException("invalid groupID", 0xA00);
        }

        return $this->sgroupList[intval((string)$sgid)];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Servergroup object matching the given name.
     *
     * @param string $name
     * @param integer $type
     * @return ServerGroup
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function serverGroupGetByName(string $name, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): ServerGroup
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupPermList(int $sgid, bool $permsid = false): array
    {
        return $this->execute("servergrouppermlist", ["sgid" => $sgid, $permsid ? "-permsid" : null])
            ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to the server group specified. Multiple permissions
     * can be added by providing the four parameters of each permission in separate arrays.
     *
     * @param integer $sgid
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @param integer|integer[] $permnegated
     * @param bool|bool[] $permskip
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupPermAssign(int $sgid, int|array $permid, int|array $permvalue, int|array $permnegated = 0, bool|array $permskip = false): void
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
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupPermRemove(int $sgid, int|array $permid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupClientList(int $sgid): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupClientAdd(int $sgid, int $cldbid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function serverGroupClientDel(int $sgid, int $cldbid): void
    {
        $this->execute("servergroupdelclient", ["sgid" => $sgid, "cldbid" => $cldbid]);
    }

    /**
     * Returns an ordered array of regular server groups available based on a pre-defined
     * set of rules.
     *
     * @param integer $type
     * @return array
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function serverGroupGetProfiles(int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): array
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
     * @return ServerGroup
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function serverGroupIdentify(
        int $mode = TeamSpeak3::GROUP_IDENTIFIY_STRONGEST,
        int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR
    ): ServerGroup {
        $profiles = $this->serverGroupGetProfiles($type);

        $best_guess_profile = ($mode == TeamSpeak3::GROUP_IDENTIFIY_STRONGEST) ? array_shift($profiles) : array_pop($profiles);

        return $this->serverGroupGetById($best_guess_profile["__sgid"]);
    }

    /**
     * Returns a list of channel groups available.
     *
     * @param array $filter
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupList(array $filter = []): array
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
    public function channelGroupListReset(): void
    {
        $this->cgroupList = null;
    }

    /**
     * Creates a new channel group using the name specified with $name and returns its ID.
     *
     * @param string $name
     * @param integer $type
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupCreate(string $name, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        $this->channelGroupListReset();

        $cgid = $this->execute("channelgroupadd", ["name" => $name, "type" => $type])->toList();

        return $cgid["cgid"];
    }

    /**
     * Creates a copy of an existing channel group specified by $scgid and returns the new groups ID.
     *
     * @param integer $scgid
     * @param string|null $name
     * @param integer $tcgid
     * @param integer $type
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupCopy(int $scgid, string $name = null, int $tcgid = 0, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        $this->channelGroupListReset();

        $cgid = $this->execute("channelgroupcopy", ["scgid" => $scgid, "tcgid" => $tcgid, "name" => $name, "type" => $type])
            ->toList();

        if ($tcgid && $name) {
            $this->channelGroupRename($tcgid, $name);
        }

        return count($cgid) ? $cgid["cgid"] : $tcgid;
    }

    /**
     * Renames the channel group specified with $cgid.
     *
     * @param integer $cgid
     * @param string $name
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupRename(int $cgid, string $name): void
    {
        $this->channelGroupListReset();

        $this->execute("channelgrouprename", ["cgid" => $cgid, "name" => $name]);
    }

    /**
     * Deletes the channel group specified with $cgid. If $force is set to 1, the channel group
     * will be deleted even if there are clients within.
     *
     * @param integer $cgid
     * @param boolean $force
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupDelete(int $cgid, bool $force = false): void
    {
        $this->channelGroupListReset();

        $this->execute("channelgroupdel", ["cgid" => $cgid, "force" => $force]);
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Channelgroup object matching the given ID.
     *
     * @param integer $cgid
     * @return ChannelGroup
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupGetById(int $cgid): ChannelGroup
    {
        if (!array_key_exists((string)$cgid, $this->channelGroupList())) {
            throw new ServerQueryException("invalid groupID", 0xA00);
        }

        return $this->cgroupList[intval((string)$cgid)];
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Node\Channelgroup object matching the given name.
     *
     * @param string $name
     * @param integer $type
     * @return ChannelGroup
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupGetByName(string $name, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): ChannelGroup
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupPermList(int $cgid, bool $permsid = false): array
    {
        return $this->execute("channelgrouppermlist", ["cgid" => $cgid, $permsid ? "-permsid" : null])
            ->toAssocArray($permsid ? "permsid" : "permid");
    }

    /**
     * Adds a set of specified permissions to the channel group specified. Multiple permissions
     * can be added by providing the two parameters of each permission in separate arrays.
     *
     * @param integer $cgid
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @return void
     * @throws ServerQueryException
     * @throws AdapterException
     */
    public function channelGroupPermAssign(int $cgid, int|array $permid, int|array $permvalue): void
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
     * @param integer|integer[] $permid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupPermRemove(int $cgid, int|array $permid): void
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function channelGroupClientList(int $cgid = null, int $cid = null, int $cldbid = null, bool $resolve = false): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permReset(): StringHelper
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permRemoveAny(int $permid): int
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function transferInitUpload(
        int    $clientftfid,
        int    $cid,
        string $name,
        int    $size,
        string $cpw = "",
        bool   $overwrite = false,
        bool   $resume = false
    ): array {
        $upload = $this->execute("ftinitupload", ["clientftfid" => $clientftfid, "cid" => $cid, "name" => $name, "cpw" => $cpw, "size" => $size, "overwrite" => $overwrite, "resume" => $resume])
            ->toList();

        if (array_key_exists("status", $upload) && $upload["status"] != 0x00) {
            throw new ServerQueryException($upload["msg"], $upload["status"]);
        }

        $upload["cid"] = $cid;
        $upload["file"] = $name;

        if (!array_key_exists("ip", $upload) || $upload["ip"]->startsWith("0.0.0.0")) {
            $upload["ip"] = $this->getParent()->getAdapterHost();
        } else {
            $upload["ip"] = $upload["ip"]->section(",");
        }
        $upload["host"] = $upload["ip"];

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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function transferInitDownload(int $clientftfid, int $cid, string $name, string $cpw = "", int $seekpos = 0): array
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
        } else {
            $download["ip"] = $download["ip"]->section(",");
        }
        $download["host"] = $download["ip"];

        Signal::getInstance()->emit("filetransferDownloadInit", $download["ftkey"], $download);

        return $download;
    }

    /**
     * Displays a list of running file transfers on the selected virtual server. The output contains the path to
     * which a file is uploaded to, the current transfer rate in bytes per second, etc.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function transferList(): array
    {
        return $this->request("ftlist")->toAssocArray("serverftfid");
    }

    /**
     * Stops the running file transfer with server-side ID $serverftfid.
     *
     * @param integer $serverftfid
     * @param boolean $delete
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function transferStop(int $serverftfid, bool $delete = false): void
    {
        $this->execute("ftstop", ["serverftfid" => $serverftfid, "delete" => $delete]);
    }

    /**
     * Downloads and returns the servers icon file content.
     *
     * @param string|null $iconname
     * @return StringHelper|void
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function iconDownload(string $iconname = null)
    {
        if ($iconname) {
            $name = new StringHelper("/" . $iconname);
        } else {
            $iconid = floatval($this['virtualserver_icon_id']);

            if ($this->iconIsLocal("virtualserver_icon_id") || $iconid == 0) {
                return;
            }
            $name = $this->iconGetName("virtualserver_icon_id");
        }

        $download = $this->transferInitDownload(rand(0x0000, 0xFFFF), 0, $name);
        $transfer = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"]);

        return $transfer->download($download["ftkey"], $download["size"]);
    }

    /**
     * Uploads a given icon file content to the server and returns the ID of the icon.
     *
     * @param string $data
     * @return integer
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function iconUpload(string $data): int
    {
        $crc = crc32($data);
        $size = strlen($data);

        $upload = $this->transferInitUpload(rand(0x0000, 0xFFFF), 0, "/icon_" . $crc, $size);
        $transfer = TeamSpeak3::factory("filetransfer://" . (str_contains($upload["host"], ":") ? "[" . $upload["host"] . "]" : $upload["host"]) . ":" . $upload["port"]);

        $transfer->upload($upload["ftkey"], $upload["seekpos"], $data);

        return $crc;
    }

    /**
     * Changes the virtual server configuration using given properties.
     *
     * @param array $properties
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function modify(array $properties): void
    {
        $this->execute("serveredit", $properties);
        $this->resetNodeInfo();
    }

    /**
     * Sends a text message to all clients on the virtual server.
     *
     * @param string $msg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function message(string $msg): void
    {
        $this->execute("sendtextmessage", ["msg" => $msg, "target" => $this->getId(), "targetmode" => TeamSpeak3::TEXTMSG_SERVER]);
    }

    /**
     * Returns a list of offline messages you've received. The output contains the senders unique identifier,
     * the messages subject, etc.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function messageList(): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function messageCreate(string $cluid, string $subject, string $message): void
    {
        $this->execute("messageadd", ["cluid" => $cluid, "subject" => $subject, "message" => $message]);
    }

    /**
     * Deletes an existing offline message with ID $msgid from your inbox.
     *
     * @param integer $msgid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function messageDelete(int $msgid): void
    {
        $this->execute("messagedel", ["msgid" => $msgid]);
    }

    /**
     * Returns an existing offline message with ID $msgid from your inbox.
     *
     * @param integer $msgid
     * @param boolean $flag_read
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function messageRead(int $msgid, bool $flag_read = true): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function snapshotCreate(int $mode = TeamSpeak3::SNAPSHOT_STRING): string
    {
        $snapshot = $this->request("serversnapshotcreate")->toString(false);

        return match ($mode) {
            TeamSpeak3::SNAPSHOT_BASE64 => $snapshot->toBase64(),
            TeamSpeak3::SNAPSHOT_HEXDEC => $snapshot->toHex(),
            default => (string)$snapshot,
        };
    }

    /**
     * Deploys snapshot data on the selected virtual server. If no virtual server is selected (ID 0),
     * the data will be used to create a new virtual server from scratch.
     *
     * @param string $data
     * @param int $mode
     * @return array
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function snapshotDeploy(string $data, int $mode = TeamSpeak3::SNAPSHOT_STRING): array
    {
        $data = match ($mode) {
            TeamSpeak3::SNAPSHOT_BASE64 => StringHelper::fromBase64($data),
            TeamSpeak3::SNAPSHOT_HEXDEC => StringHelper::fromHex($data),
            default => StringHelper::factory($data),
        };

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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function notifyRegister(string $event, int $id = 0): void
    {
        $this->execute("servernotifyregister", ["event" => $event, "id" => $id]);
    }

    /**
     * Unregisters all events previously registered with servernotifyregister so you will no
     * longer receive notification messages.
     *
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function notifyUnregister(): void
    {
        $this->request("servernotifyunregister");
    }

    /**
     * Alias for privilegeKeyList().
     *
     * @param bool $translate
     * @return array
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     * @deprecated
     */
    public function tokenList(bool $translate = false): array
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
     * @throws AdapterException
     * @throws NodeException
     * @throws ServerQueryException
     */
    public function privilegeKeyList(bool $resolve = false): array
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

                if ($array["token_type"]) {
                    $tokens[$token]["token_id2"] = $this->channelGetById($array["token_id2"])->getPathway();
                }
            }
        }

        return $tokens;
    }

    /**
     * Alias for privilegeKeyCreate().
     *
     * @param int $id1
     * @param int $id2
     * @param int $type
     * @param string|null $description
     * @param array|null $customset
     * @return StringHelper
     * @throws AdapterException
     * @throws ServerQueryException
     * @deprecated
     */
    public function tokenCreate(
        int $id1,
        int $id2 = 0,
        int $type = TeamSpeak3::TOKEN_SERVERGROUP,
        string $description = null,
        array $customset = null
    ): StringHelper {
        return $this->privilegeKeyCreate($id1, $id2, $type, $description, $customset);
    }

    /**
     * Creates a new privilege key (token) and returns the key.
     * @param integer $id1
     * @param integer $id2
     * @param integer $type
     * @param string|null $description
     * @param string|null $customset
     * @return StringHelper
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function privilegeKeyCreate(
        int    $id1,
        int    $id2 = 0,
        int    $type = TeamSpeak3::TOKEN_SERVERGROUP,
        string $description = null,
        string $customset = null
    ): StringHelper {
        $token = $this->execute("privilegekeyadd", ["tokentype" => $type, "tokenid1" => $id1, "tokenid2" => $id2, "tokendescription" => $description, "tokencustomset" => $customset])
            ->toList();

        Signal::getInstance()->emit("notifyTokencreated", $this, $token["token"]);

        return $token["token"];
    }

    /**
     * Alias for privilegeKeyDelete().
     *
     * @param $token
     * @throws AdapterException
     * @throws ServerQueryException
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function privilegeKeyDelete(string $token): void
    {
        $this->execute("privilegekeydelete", ["token" => $token]);
    }

    /**
     * Alias for privilegeKeyUse().
     *
     * @param $token
     * @throws AdapterException
     * @throws ServerQueryException
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function privilegeKeyUse(string $token): void
    {
        $this->execute("privilegekeyuse", ["token" => $token]);
    }

    /**
     * Returns a list of custom client properties specified by $ident.
     *
     * @param string $ident
     * @param string $pattern
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function customSearch(string $ident, string $pattern = "%"): array
    {
        return $this->execute("customsearch", ["ident" => $ident, "pattern" => $pattern])->toArray();
    }

    /**
     * Returns a list of custom properties for the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function customInfo(int $cldbid): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function customSet(int $cldbid, string $ident, string $value): void
    {
        $this->execute("customset", ["cldbid" => $cldbid, "ident" => $ident, "value" => $value]);
    }

    /**
     * Removes a custom property from the client specified by $cldbid.
     *
     * @param integer $cldbid
     * @param string $ident
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function customDelete(int $cldbid, string $ident): void
    {
        $this->execute("customdelete", ["cldbid" => $cldbid, "ident" => $ident]);
    }

    /**
     * Returns a list of active bans on the selected virtual server.
     *
     * @param null $offset
     * @param null $limit
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function banList($offset = null, $limit = null): array
    {
        return $this->execute("banlist -count", ["start" => $offset, "duration" => $limit])->toAssocArray("banid");
    }

    /**
     * Returns the number of bans on the selected virtual server.
     *
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function banCount(): int
    {
        return current($this->execute("banlist -count", ["duration" => 1])->toList("count"));
    }

    /**
     * Deletes all active ban rules from the server.
     *
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function banListClear(): void
    {
        $this->request("bandelall");
    }

    /**
     * Adds a new ban rule on the selected virtual server. All parameters are optional but at least one
     * of the following rules must be set: ip, name, or uid.
     *
     * @param array $rules
     * @param integer|null $timeseconds
     * @param string|null $reason
     * @return integer
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function banCreate(array $rules, int $timeseconds = null, string $reason = null): int
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function banDelete(int $banid): void
    {
        $this->execute("bandel", ["banid" => $banid]);
    }

    /**
     * Returns a list of complaints on the selected virtual server. If $tcldbid is specified, only
     * complaints about the targeted client will be shown.
     *
     * @param integer|null $tcldbid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function complaintList(int $tcldbid = null): array
    {
        return $this->execute("complainlist", ["tcldbid" => $tcldbid])->toArray();
    }

    /**
     * Deletes all active complaints about the client with database ID $tcldbid from the server.
     *
     * @param integer $tcldbid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function complaintListClear(int $tcldbid): void
    {
        $this->execute("complaindelall", ["tcldbid" => $tcldbid]);
    }

    /**
     * Submits a complaint about the client with database ID $tcldbid to the server.
     *
     * @param integer $tcldbid
     * @param string $message
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function complaintCreate(int $tcldbid, string $message): void
    {
        $this->execute("complainadd", ["tcldbid" => $tcldbid, "message" => $message]);
    }

    /**
     * Deletes the complaint about the client with ID $tcldbid submitted by the client with ID $fcldbid from the server.
     *
     * @param integer $tcldbid
     * @param integer $fcldbid
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function complaintDelete(int $tcldbid, int $fcldbid): void
    {
        $this->execute("complaindel", ["tcldbid" => $tcldbid, "fcldbid" => $fcldbid]);
    }

    /**
     * Returns a list of temporary server passwords.
     *
     * @param boolean $resolve
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function tempPasswordList(bool $resolve = false): array
    {
        $passwords = $this->request("servertemppasswordlist")->toAssocArray("pw_clear");

        if ($resolve) {
            foreach ($passwords as $password => $array) {
                $channel = $this->channelGetById($array["tcid"]);

                $passwords[$password]["tcname"] = $channel->toString();
                $passwords[$password]["tcpath"] = $channel->getPathway();
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function tempPasswordCreate(string $pw, int $duration, int $tcid = 0, string $tcpw = "", string $desc = ""): void
    {
        $this->execute("servertemppasswordadd", ["pw" => $pw, "duration" => $duration, "tcid" => $tcid, "tcpw" => $tcpw, "desc" => $desc]);
    }

    /**
     * Deletes the temporary server password specified with $pw.
     *
     * @param string $pw
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function tempPasswordDelete(string $pw): void
    {
        $this->execute("servertemppassworddel", ["pw" => $pw]);
    }

    /**
     * Displays a specified number of entries (1-100) from the servers log.
     *
     * @param integer $lines
     * @param integer|null $begin_pos
     * @param boolean|null $reverse
     * @param boolean|null $instance
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function logView(int $lines = 30, int $begin_pos = null, bool $reverse = null, bool $instance = null): array
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
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function logAdd(string $logmsg, int $loglevel = TeamSpeak3::LOGLEVEL_INFO): void
    {
        $this->execute("logadd", ["logmsg" => $logmsg, "loglevel" => $loglevel]);
    }

    /**
     * Returns detailed connection information of the virtual server.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function connectionInfo(): array
    {
        return $this->request("serverrequestconnectioninfo")->toList();
    }

    /**
     * Deletes the virtual server.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->getParent()->serverDelete($this->getId());
    }

    /**
     * Starts the virtual server.
     *
     * @return void
     */
    public function start(): void
    {
        $this->getParent()->serverStart($this->getId());
    }

    /**
     * Stops the virtual server.
     *
     * @param string|null $msg
     * @return void
     */
    public function stop(string $msg = null): void
    {
        $this->getParent()->serverStop($this->getId(), $msg);
    }

    /**
     * Sends a plugin command to all clients connected to the server.
     *
     * @param string $plugin
     * @param string $data
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function sendPluginCmd(string $plugin, string $data): void
    {
        $this->execute("plugincmd", ["name" => $plugin, "data" => $data, "targetmode" => TeamSpeak3::PLUGINCMD_SERVER]);
    }

    /**
     * Changes the properties of your own client connection.
     *
     * @param array $properties
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function selfUpdate(array $properties): void
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
     * @return StringHelper
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function selfUpdateLogin(string $username): StringHelper
    {
        $password = $this->execute("clientsetserverquerylogin", ["client_login_name" => $username])->toList();

        return $password["client_login_password"];
    }

    /**
     * Returns an array containing the permission overview of your own client.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function selfPermOverview(): array
    {
        return $this->execute("permoverview", ["cldbid" => $this->whoamiGet("client_database_id"), "cid" => $this->whoamiGet("client_channel_id"), "permid" => 0])
            ->toArray();
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
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
     * @throws AdapterException
     * @throws ServerQueryException
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
     */
    protected static function sortClientList(Client $a, Client $b): int
    {
        if (get_class($a) != get_class($b)) {
            return 0;
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
     */
    protected static function sortGroupList(Node $a, Node $b): int
    {
        if (get_class($a) != get_class($b)) {
            return 0;
        }

        if (!$a instanceof ServerGroup && !$a instanceof ChannelGroup) {
            return 0;
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
     */
    protected static function sortFileList(array $a, array $b): int
    {
        if (!array_key_exists("src", $a) || !array_key_exists("src", $b) || !array_key_exists("type", $a) || !array_key_exists("type", $b)) {
            return 0;
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
    public function isOnline(): bool
    {
        return $this["virtualserver_status"] == "online";
    }

    /**
     * Returns TRUE if the virtual server is offline.
     *
     * @return boolean
     */
    public function isOffline(): bool
    {
        return $this["virtualserver_status"] == "offline";
    }

    /**
     * Returns a unique identifier for the node which can be used as a HTML property.
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->getParent()->getUniqueId() . "_s" . $this->getId();
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon(): string
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
    public function getSymbol(): string
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
