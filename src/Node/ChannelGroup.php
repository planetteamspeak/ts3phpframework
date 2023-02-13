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

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class ChannelGroup
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @class ChannelGroup
 * @brief Class describing a TeamSpeak 3 channel group and all it's parameters.
 */
class ChannelGroup extends Group
{
    /**
     * ChannelGroup constructor.
     *
     * @param Server $server
     * @param array $info
     * @param string $index
     * @throws ServerQueryException
     */
    public function __construct(Server $server, array $info, string $index = "cgid")
    {
        $this->parent = $server;
        $this->nodeInfo = $info;

        if (!array_key_exists($index, $this->nodeInfo)) {
            throw new ServerQueryException("invalid groupID", 0xA00);
        }

        $this->nodeId = $this->nodeInfo[$index];
    }

    /**
     * Renames the channel group specified.
     *
     * @param string $name
     * @return void
     */
    public function rename(string $name): void
    {
        $this->getParent()->channelGroupRename($this->getId(), $name);
    }

    /**
     * Deletes the channel group. If $force is set to TRUE, the channel group will be
     * deleted even if there are clients within.
     *
     * @param boolean $force
     * @return void
     */
    public function delete(bool $force = false): void
    {
        $this->getParent()->channelGroupDelete($this->getId(), $force);
    }

    /**
     * Creates a copy of the channel group and returns the new groups ID.
     *
     * @param string|null $name
     * @param integer $tcgid
     * @param integer $type
     * @return integer
     */
    public function copy(string $name = null, int $tcgid = 0, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        return $this->getParent()->channelGroupCopy($this->getId(), $name, $tcgid, $type);
    }

    /**
     * Returns a list of permissions assigned to the channel group.
     *
     * @param boolean $permsid
     * @return array
     */
    public function permList(bool $permsid = false): array
    {
        return $this->getParent()->channelGroupPermList($this->getId(), $permsid);
    }

    /**
     * Adds a set of specified permissions to the channel group. Multiple permissions
     * can be added by providing the two parameters of each permission in separate arrays.
     *
     * @param integer $permid
     * @param integer $permvalue
     * @return void
     */
    public function permAssign(int $permid, int $permvalue): void
    {
        $this->getParent()->channelGroupPermAssign($this->getId(), $permid, $permvalue);
    }

    /**
     * Alias for permAssign().
     *
     * @deprecated
     */
    public function permAssignByName($permname, $permvalue)
    {
        $this->permAssign($permname, $permvalue);
    }

    /**
     * Removes a set of specified permissions from the channel group. Multiple
     * permissions can be removed at once.
     *
     * @param integer $permid
     * @return void
     */
    public function permRemove(int $permid): void
    {
        $this->getParent()->channelGroupPermRemove($this->getId(), $permid);
    }

    /**
     * Alias for permAssign().
     *
     * @deprecated
     */
    public function permRemoveByName($permname)
    {
        $this->permRemove($permname);
    }

    /**
     * Returns a list of clients assigned to the channel group specified.
     *
     * @param integer|null $cid
     * @param integer|null $cldbid
     * @param boolean $resolve
     * @return array
     */
    public function clientList(int $cid = null, int $cldbid = null, bool $resolve = false): array
    {
        return $this->getParent()->channelGroupClientList($this->getId(), $cid, $cldbid, $resolve);
    }

    /**
     * Alias for privilegeKeyCreate().
     *
     * @deprecated
     */
    public function tokenCreate($cid, $description = null, $customset = null): StringHelper
    {
        return $this->privilegeKeyCreate($cid, $description, $customset);
    }

    /**
     * Creates a new privilege key (token) for the channel group and returns the key.
     *
     * @param integer $cid
     * @param string|null $description
     * @param string|null $customset
     * @return StringHelper
     */
    public function privilegeKeyCreate(int $cid, string $description = null, string $customset = null): StringHelper
    {
        return $this->getParent()->privilegeKeyCreate($this->getId(), TeamSpeak3::TOKEN_CHANNELGROUP, $cid, $description, $customset);
    }

    /**
     * @ignore
     */
    protected function fetchNodeList()
    {
        $this->nodeList = [];

        foreach ($this->getParent()->clientList() as $client) {
            if ($client["client_channel_group_id"] == $this->getId()) {
                $this->nodeList[] = $client;
            }
        }
    }

    /**
     * Returns a unique identifier for the node which can be used as a HTML property.
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->getParent()->getUniqueId() . "_cg" . $this->getId();
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return "group_channel";
    }
}
