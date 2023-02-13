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

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class Client
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @class Client
 * @brief Class describing a TeamSpeak 3 client and all it's parameters.
 */
class Client extends Node
{
    /**
     * Client constructor.
     *
     * @param Server $server
     * @param array $info
     * @param string $index
     * @throws ServerQueryException
     */
    public function __construct(Server $server, array $info, string $index = "clid")
    {
        $this->parent = $server;
        $this->nodeInfo = $info;

        if (!array_key_exists($index, $this->nodeInfo)) {
            throw new ServerQueryException("invalid clientID", 0x200);
        }

        $this->nodeId = $this->nodeInfo[$index];
    }

    /**
     * Changes the clients properties using given properties.
     *
     * @param array $properties
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function modify(array $properties): void
    {
        $properties["clid"] = $this->getId();

        $this->execute("clientedit", $properties);
        $this->resetNodeInfo();
    }

    /**
     * Changes the clients properties using given properties.
     *
     * @param array $properties
     * @return void
     */
    public function modifyDb(array $properties): void
    {
        $this->getParent()->clientModifyDb($this["client_database_id"], $properties);
    }

    /**
     * Deletes the clients properties from the database.
     *
     * @return void
     */
    public function deleteDb(): void
    {
        $this->getParent()->clientDeleteDb($this["client_database_id"]);
    }

    /**
     * Returns a list of properties from the database for the client.
     *
     * @return array
     */
    public function infoDb(): array
    {
        return $this->getParent()->clientInfoDb($this["client_database_id"]);
    }

    /**
     * Sends a text message to the client.
     *
     * @param string $msg
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function message(string $msg): void
    {
        $this->execute("sendtextmessage", ["msg" => $msg, "target" => $this->getId(), "targetmode" => TeamSpeak3::TEXTMSG_CLIENT]);
    }

    /**
     * Moves the client to another channel.
     *
     * @param integer $cid
     * @param string|null $cpw
     * @return void
     */
    public function move(int $cid, string $cpw = null): void
    {
        $this->getParent()->clientMove($this->getId(), $cid, $cpw);
    }

    /**
     * Kicks the client from his currently joined channel or from the server.
     *
     * @param integer $reasonid
     * @param string|null $reasonmsg
     * @return void
     */
    public function kick(int $reasonid = TeamSpeak3::KICK_CHANNEL, string $reasonmsg = null): void
    {
        $this->getParent()->clientKick($this->getId(), $reasonid, $reasonmsg);
    }

    /**
     * Sends a poke message to the client.
     *
     * @param string $msg
     * @return void
     */
    public function poke(string $msg): void
    {
        $this->getParent()->clientPoke($this->getId(), $msg);
    }

    /**
     * Bans the client from the server. Please note that this will create two separate
     * ban rules for the targeted clients IP address and his unique identifier.
     *
     * @param integer|null $timeseconds
     * @param string|null $reason
     * @return array
     */
    public function ban(int $timeseconds = null, string $reason = null): array
    {
        return $this->getParent()->clientBan($this->getId(), $timeseconds, $reason);
    }

    /**
     * Returns a list of custom properties for the client.
     *
     * @return array
     */
    public function customInfo(): array
    {
        return $this->getParent()->customInfo($this["client_database_id"]);
    }

    /**
     * Creates or updates a custom property for the client.
     *
     * @param string $ident
     * @param string $value
     * @return void
     */
    public function customSet(string $ident, string $value): void
    {
        $this->getParent()->customSet($this["client_database_id"], $ident, $value);
    }

    /**
     * Removes a custom property from the client.
     *
     * @param string $ident
     * @return void
     */
    public function customDelete(string $ident): void
    {
        $this->getParent()->customDelete($this["client_database_id"], $ident);
    }

    /**
     * Returns an array containing the permission overview of the client.
     *
     * @param integer $cid
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function permOverview(int $cid): array
    {
        return $this->execute("permoverview", ["cldbid" => $this["client_database_id"], "cid" => $cid, "permid" => 0])->toArray();
    }

    /**
     * Returns a list of permissions defined for the client.
     *
     * @param boolean $permsid
     * @return array
     */
    public function permList(bool $permsid = false): array
    {
        return $this->getParent()->clientPermList($this["client_database_id"], $permsid);
    }

    /**
     * Adds a set of specified permissions to the client. Multiple permissions can be added by providing
     * the three parameters of each permission.
     *
     * @param integer|integer[] $permid
     * @param integer|integer[] $permvalue
     * @param bool|bool[] $permskip
     * @return void
     */
    public function permAssign(int|array $permid, int|array $permvalue, array|bool $permskip = false): void
    {
        $this->getParent()->clientPermAssign($this["client_database_id"], $permid, $permvalue, $permskip);
    }

    /**
     * Alias for permAssign().
     *
     * @deprecated
     */
    public function permAssignByName($permname, $permvalue, $permskip = false)
    {
        $this->permAssign($permname, $permvalue, $permskip);
    }

    /**
     * Removes a set of specified permissions from a client. Multiple permissions can be removed at once.
     *
     * @param integer $permid
     * @return void
     */
    public function permRemove(int $permid): void
    {
        $this->getParent()->clientPermRemove($this["client_database_id"], $permid);
    }

    /**
     * Alias for permRemove().
     *
     * @deprecated
     */
    public function permRemoveByName($permname)
    {
        $this->permRemove($permname);
    }

    /**
     * Sets the channel group of a client to the ID specified.
     *
     * @param integer $cid
     * @param integer $cgid
     * @return void
     */
    public function setChannelGroup(int $cid, int $cgid): void
    {
        $this->getParent()->clientSetChannelGroup($this["client_database_id"], $cid, $cgid);
    }

    /**
     * Adds the client to the server group specified with $sgid.
     *
     * @param integer $sgid
     * @return void
     */
    public function addServerGroup(int $sgid): void
    {
        $this->getParent()->serverGroupClientAdd($sgid, $this["client_database_id"]);
    }

    /**
     * Removes the client from the server group specified with $sgid.
     *
     * @param integer $sgid
     * @return void
     */
    public function remServerGroup(int $sgid): void
    {
        $this->getParent()->serverGroupClientDel($sgid, $this["client_database_id"]);
    }

    /**
     * Returns the possible name of the client's avatar.
     *
     * @return StringHelper
     */
    public function avatarGetName(): StringHelper
    {
        return new StringHelper("/avatar_" . $this["client_base64HashClientUID"]);
    }

    /**
     * Downloads and returns the clients avatar file content.
     *
     * @return StringHelper|void
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function avatarDownload()
    {
        if ($this["client_flag_avatar"] == null) {
            return;
        }

        $download = $this->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->avatarGetName());
        $transfer = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"]);

        return $transfer->download($download["ftkey"], $download["size"]);
    }

    /**
     * Returns a list of client connections using the same identity as this client.
     *
     * @return array
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function getClones(): array
    {
        return $this->execute("clientgetids", ["cluid" => $this["client_unique_identifier"]])->toAssocArray("clid");
    }

    /**
     * Returns TRUE if the client is using Overwolf.
     *
     * @return boolean
     */
    public function hasOverwolf(): bool
    {
        return str_contains($this["client_badges"], "overwolf=1");
    }

    /**
     * Returns a list of equipped badges for this client.
     *
     * @return array
     */
    public function getBadges(): array
    {
        $badges = [];

        foreach (explode(":", $this["client_badges"]) as $set) {
            if (str_starts_with($set, "badges=")) {
                $badges[] = array_map("trim", explode(",", substr($set, 7)));
            }
        }

        return $badges;
    }

    /**
     * Returns the revision/build number from the clients version string.
     *
     * @return int|null
     */
    public function getRev(): ?int
    {
        return $this["client_type"] ? null : $this["client_version"]->section("[", 1)->filterDigits();
    }

    /**
     * Returns all server and channel groups the client is currently residing in.
     *
     * @return array
     */
    public function memberOf(): array
    {
        $channelGroups = [$this->getParent()->channelGroupGetById($this["client_channel_group_id"])];
        $serverGroups = [];

        foreach (explode(",", $this["client_servergroups"]) as $sgid) {
            $serverGroups[] = $this->getParent()->serverGroupGetById($sgid);
        }

        uasort($serverGroups, [__CLASS__, "sortGroupList"]);

        return array_merge($channelGroups, $serverGroups);
    }

    /**
     * Downloads and returns the clients icon file content.
     *
     * @return StringHelper|void
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function iconDownload()
    {
        $iconid = $this['client_icon_id'];
        if (!is_int($iconid)) {
            $iconid = $iconid->toInt();
        }

        if ($this->iconIsLocal("client_icon_id") || $iconid == 0) {
            return;
        }

        $download = $this->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->iconGetName("client_icon_id"));
        $transfer = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"]);

        return $transfer->download($download["ftkey"], $download["size"]);
    }

    /**
     * Sends a plugin command to the client.
     *
     * @param string $plugin
     * @param string $data
     * @return void
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function sendPluginCmd(string $plugin, string $data): void
    {
        $this->execute("plugincmd", ["name" => $plugin, "data" => $data, "targetmode" => TeamSpeak3::PLUGINCMD_CLIENT, "target" => $this->getId()]);
    }

    /**
     * @throws AdapterException
     * @ignore
     */
    protected function fetchNodeInfo()
    {
        if ($this->offsetExists("client_type") && $this["client_type"] == 1) {
            return;
        }

        $this->nodeInfo = array_merge($this->nodeInfo, $this->execute("clientinfo", ["clid" => $this->getId()])->toList());
    }

    /**
     * Returns a unique identifier for the node which can be used as an HTML property.
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->getParent()->getUniqueId() . "_cl" . $this->getId();
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon(): string
    {
        if ($this["client_type"]) {
            return "client_query";
        } elseif ($this["client_away"]) {
            return "client_away";
        } elseif (!$this["client_output_hardware"]) {
            return "client_snd_disabled";
        } elseif ($this["client_output_muted"]) {
            return "client_snd_muted";
        } elseif (!$this["client_input_hardware"]) {
            return "client_mic_disabled";
        } elseif ($this["client_input_muted"]) {
            return "client_mic_muted";
        } elseif ($this["client_is_channel_commander"]) {
            return $this["client_flag_talking"] ? "client_cc_talk" : "client_cc_idle";
        } else {
            return $this["client_flag_talking"] ? "client_talk" : "client_idle";
        }
    }

    /**
     * Returns a symbol representing the node.
     *
     * @return string
     */
    public function getSymbol(): string
    {
        return "@";
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this["client_nickname"];
    }
}
