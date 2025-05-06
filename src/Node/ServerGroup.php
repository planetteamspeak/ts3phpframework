<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Node;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * @class ServerGroup
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @brief Class describing a TeamSpeak 3 server group and all it's parameters.
 */
class ServerGroup extends Group
{
    /**
     * The ServerGroup constructor.
     *
     * @param Server $server
     * @param array $info
     * @param string $index
     * @throws NodeException
     */
    public function __construct(Server $server, array $info, string $index = "sgid")
    {
        $this->parent = $server;
        $this->nodeInfo = $info;

        if (!array_key_exists($index, $this->nodeInfo)) {
            throw new NodeException("invalid groupID", 0xA00);
        }

        $this->nodeId = $this->nodeInfo[$index];
    }

    /**
     * Renames the server group specified.
     *
     * @param string $name
     * @return void
     */
    public function rename(string $name): void
    {
        $this->getParent()->serverGroupRename($this->getId(), $name);
    }

    /**
     * Deletes the server group. If $force is set to 1, the server group will be
     * deleted even if there are clients within.
     *
     * @param boolean $force
     * @return void
     */
    public function delete(bool $force = false): void
    {
        $this->getParent()->serverGroupDelete($this->getId(), $force);
    }

    /**
     * Creates a copy of the server group and returns the new groups ID.
     *
     * @param string|null $name
     * @param integer $tsgid
     * @param integer $type
     * @return integer
     */
    public function copy(string $name = null, int $tsgid = 0, int $type = TeamSpeak3::GROUP_DBTYPE_REGULAR): int
    {
        return $this->getParent()->serverGroupCopy($this->getId(), $name, $tsgid, $type);
    }

    /**
     * Returns a list of permissions assigned to the server group.
     *
     * @param boolean $permsid
     * @return array
     */
    public function permList(bool $permsid = false): array
    {
        return $this->getParent()->serverGroupPermList($this->getId(), $permsid);
    }

    /**
     * Adds a set of specified permissions to the server group. Multiple permissions
     * can be added by providing the four parameters of each permission in separate arrays.
     *
     * @param integer $permid
     * @param integer $permvalue
     * @param integer $permnegated
     * @param integer $permskip
     * @return void
     */
    public function permAssign(int $permid, int $permvalue, int $permnegated = 0, int $permskip = 0): void
    {
        $this->getParent()->serverGroupPermAssign($this->getId(), $permid, $permvalue, $permnegated, $permskip);
    }

    /**
     * Alias for permAssign().
     *
     * @deprecated
     */
    public function permAssignByName($permname, $permvalue, $permnegated = false, $permskip = false)
    {
        $this->permAssign($permname, $permvalue, $permnegated, $permskip);
    }

    /**
     * Removes a set of specified permissions from the server group. Multiple
     * permissions can be removed at once.
     *
     * @param integer $permid
     * @return void
     */
    public function permRemove(int $permid): void
    {
        $this->getParent()->serverGroupPermRemove($this->getId(), $permid);
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
     * Returns a list of clients assigned to the server group specified.
     *
     * @return array
     */
    public function clientList(): array
    {
        return $this->getParent()->serverGroupClientList($this->getId());
    }

    /**
     * Adds a client to the server group specified. Please note that a client cannot be
     * added to default groups or template groups.
     *
     * @param integer $cldbid
     * @return void
     */
    public function clientAdd(int $cldbid): void
    {
        $this->getParent()->serverGroupClientAdd($this->getId(), $cldbid);
    }

    /**
     * Removes a client from the server group.
     *
     * @param integer $cldbid
     * @return void
     */
    public function clientDel(int $cldbid): void
    {
        $this->getParent()->serverGroupClientDel($this->getId(), $cldbid);
    }

    /**
     * Alias for privilegeKeyCreate().
     *
     * @deprecated
     */
    public function tokenCreate($description = null, $customset = null): string
    {
        return $this->privilegeKeyCreate($description, $customset);
    }

    /**
     * Creates a new privilege key (token) for the server group and returns the key.
     *
     * @param string|null $description
     * @param string|null $customset
     * @return string
     */
    public function privilegeKeyCreate(string $description = null, string $customset = null): string
    {
        return $this->getParent()
            ->privilegeKeyCreate($this->getId(), TeamSpeak3::TOKEN_SERVERGROUP, 0, $description, $customset);
    }

    /**
     * @ignore
     */
    protected function fetchNodeList()
    {
        $this->nodeList = [];

        foreach ($this->getParent()->clientList() as $client) {
            if (in_array($this->getId(), explode(",", $client["client_servergroups"]))) {
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
        return $this->getParent()->getUniqueId() . "_sg" . $this->getId();
    }

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return "group_server";
    }
}
