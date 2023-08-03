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

use ArrayAccess;
use Countable;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\NodeException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Viewer\ViewerInterface;
use RecursiveIterator;
use RecursiveIteratorIterator;

/**
 * Class Node
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Node
 * @class Abstract
 * @brief Abstract class describing a TeamSpeak 3 node and all it's parameters.
 */
abstract class Node implements RecursiveIterator, ArrayAccess, Countable
{
    /**
     * @var Node|ServerQuery|null
     */
    protected Node|null|ServerQuery $parent = null;

    /**
     * @ignore
     */
    protected array|null $server = null;

    /**
     * @ignore
     */
    protected int $nodeId = 0x00;

    /**
     * @ignore
     */
    protected array|null $nodeList = null;

    /**
     * @ignore
     */
    protected array $nodeInfo = [];

    /**
     * @ignore
     */
    protected array $storage = [];

    /**
     * Sends a prepared command to the server and returns the result.
     *
     * @param string $cmd
     * @param boolean $throw
     * @return Reply
     * @throws AdapterException|ServerQueryException
     */
    public function request(string $cmd, bool $throw = true): Reply
    {
        return $this->getParent()->request($cmd, $throw);
    }

    /**
     * Uses given parameters and returns a prepared ServerQuery command.
     *
     * @param string $cmd
     * @param array $params
     * @return StringHelper
     */
    public function prepare(string $cmd, array $params = []): StringHelper
    {
        return StringHelper::factory($this->getParent()->prepare($cmd, $params));
    }

    /**
     * Prepares and executes a ServerQuery command and returns the result.
     *
     * @param $cmd
     * @param array $params
     * @return Reply
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function execute($cmd, array $params = []): Reply
    {
        return $this->request($this->prepare($cmd, $params));
    }

    /**
     * Returns the parent object of the current node.
     *
     * @return ServerQuery|Node|null
     */
    public function getParent(): ServerQuery|Node|null
    {
        return $this->parent;
    }

    /**
     * Returns the primary ID of the current node.
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->nodeId;
    }

    /**
     * Returns TRUE if the node icon has a local source.
     *
     * @param string $key
     * @return boolean
     */
    public function iconIsLocal(string $key): bool
    {
        $iconid = $this[$key];
        if (!is_int($iconid)) {
            $iconid = $iconid->toInt();
        }

        return $iconid > 0 && $iconid < 1000;
    }

    /**
     * Returns the internal path of the node icon.
     *
     * @param string $key
     * @return StringHelper
     */
    public function iconGetName(string $key): StringHelper
    {
        $iconid = $this[$key];
        if (!is_int($iconid)) {
            $iconid = $iconid->toInt();
        }

        $iconid = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;

        return new StringHelper("/icon_" . $iconid);
    }

    /**
     * Returns a possible classname for the node which can be used as an HTML property.
     *
     * @param string $prefix
     * @return string
     */
    public function getClass(string $prefix = "ts3_"): string
    {
        if ($this instanceof Channel && $this->isSpacer()) {
            return $prefix . "spacer";
        } elseif ($this instanceof Client && $this["client_type"]) {
            return $prefix . "query";
        }

        return $prefix . StringHelper::factory(get_class($this))->section("_", 2)->toLower();
    }

    /**
     * Returns a unique identifier for the node which can be used as an HTML property.
     *
     * @return string
     */
    abstract public function getUniqueId(): string;

    /**
     * Returns the name of a possible icon to display the node object.
     *
     * @return string
     */
    abstract public function getIcon(): string;

    /**
     * Returns a symbol representing the node.
     *
     * @return string
     */
    abstract public function getSymbol(): string;

    /**
     * Returns the HTML code to display a TeamSpeak 3 viewer.
     *
     * @param ViewerInterface $viewer
     * @return string
     */
    public function getViewer(ViewerInterface $viewer): string
    {
        $html = $viewer->fetchObject($this);

        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $node) {
            $siblings = [];

            for ($level = 0; $level < $iterator->getDepth(); $level++) {
                $siblings[] = ($iterator->getSubIterator($level)->hasNext()) ? 1 : 0;
            }

            $siblings[] = (!$iterator->getSubIterator($level)->hasNext()) ? 1 : 0;

            $html .= $viewer->fetchObject($node, $siblings);
        }

        if (empty($html) && method_exists($viewer, "toString")) {
            return $viewer->toString();
        }

        return $html;
    }

    /**
     * Filters given node list array using specified filter rules.
     *
     * @param array $nodes
     * @param array $rules
     * @return array
     */
    protected function filterList(array $nodes = [], array $rules = []): array
    {
        if (!empty($rules)) {
            foreach ($nodes as $node) {
                if (!$node instanceof Node) {
                    continue;
                }

                $props = $node->getInfo(false);
                $props = array_intersect_key($props, $rules);

                foreach ($props as $key => $val) {
                    if ($val instanceof StringHelper) {
                        $match = $val->contains($rules[$key], true);
                    } else {
                        $match = $val == $rules[$key];
                    }

                    if ($match === false) {
                        unset($nodes[$node->getId()]);
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Returns all information available on this node. If $convert is enabled, some property
     * values will be converted to human-readable values.
     *
     * @param boolean $extend
     * @param boolean $convert
     * @return array
     */
    public function getInfo(bool $extend = true, bool $convert = false): array
    {
        if ($extend) {
            $this->fetchNodeInfo();
        }

        if ($convert) {
            $info = $this->nodeInfo;

            foreach ($info as $key => $val) {
                $key = StringHelper::factory($key);

                if ($key->contains("_bytes_")) {
                    $info[$key->toString()] = Convert::bytes($val);
                } elseif ($key->contains("_bandwidth_")) {
                    $info[$key->toString()] = Convert::bytes($val) . "/s";
                } elseif ($key->contains("_packets_")) {
                    $info[$key->toString()] = number_format($val, 0, null, ".");
                } elseif ($key->contains("_packetloss_")) {
                    $info[$key->toString()] = sprintf("%01.2f", floatval($val instanceof StringHelper ? $val->toString() : strval($val)) * 100) . "%";
                } elseif ($key->endsWith("_uptime")) {
                    $info[$key->toString()] = Convert::seconds($val);
                } elseif ($key->endsWith("_version")) {
                    $info[$key->toString()] = Convert::version($val);
                } elseif ($key->endsWith("_icon_id")) {
                    $info[$key->toString()] = $this->iconGetName($key)->filterDigits();
                }
            }

            return $info;
        }

        return $this->nodeInfo;
    }

    /**
     * Returns the specified property or a pre-defined default value from the node info array.
     *
     * @param string $property
     * @param mixed|null $default
     * @return mixed
     */
    public function getProperty(string $property, mixed $default = null): mixed
    {
        if (!$this->offsetExists($property)) {
            $this->fetchNodeInfo();
        }

        if (!$this->offsetExists($property)) {
            return $default;
        }

        return $this->nodeInfo[$property];
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Returns an assoc array filled with current node info properties.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->nodeList;
    }

    /**
     * Called whenever we're using an unknown method.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws NodeException
     */
    public function __call(string $name, array $args)
    {
        if ($this->getParent() instanceof Node) {
            return call_user_func_array([$this->getParent(), $name], $args);
        }

        throw new NodeException("node method '" . $name . "()' does not exist");
    }

    /**
     * Writes data to the internal storage array.
     *
     * @param string $key
     * @param mixed $val
     * @return void
     */
    protected function setStorage(string $key, mixed $val): void
    {
        $this->storage[$key] = $val;
    }

    /**
     * Returns data from the internal storage array.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getStorage(string $key, mixed $default = null): mixed
    {
        return !empty($this->storage[$key]) ? $this->storage[$key] : $default;
    }

    /**
     * Deletes data from the internal storage array.
     *
     * @param string $key
     * @return void
     */
    protected function delStorage(string $key): void
    {
        unset($this->storage[$key]);
    }

    /**
     * Commit pending data.
     *
     * @return array
     */
    public function __sleep()
    {
        return ["parent", "storage", "nodeId"];
    }

    /**
     * @ignore
     */
    protected function fetchNodeList()
    {
        $this->nodeList = [];
    }

    /**
     * @ignore
     */
    protected function fetchNodeInfo()
    {
    }

    /**
     * @ignore
     */
    protected function resetNodeInfo()
    {
        $this->nodeInfo = [];
    }

    /**
     * @ignore
     */
    protected function verifyNodeList()
    {
        if ($this->nodeList === null) {
            $this->fetchNodeList();
        }
    }

    /**
     * @ignore
     */
    protected function resetNodeList()
    {
        $this->nodeList = null;
    }

    /**
     * @ignore
     */
    public function count(): int
    {
        $this->verifyNodeList();

        return count($this->nodeList);
    }

    /**
     * @ignore
     */
    public function current(): mixed
    {
        $this->verifyNodeList();

        return current($this->nodeList);
    }

    /**
     * @ignore
     */
    public function getChildren(): null|RecursiveIterator
    {
        $this->verifyNodeList();

        return $this->current();
    }

    /**
     * @ignore
     */
    public function hasChildren(): bool
    {
        $this->verifyNodeList();

        return $this->current()->count() > 0;
    }

    /**
     * @ignore
     */
    public function hasNext(): bool
    {
        $this->verifyNodeList();

        return $this->key() + 1 < $this->count();
    }

    /**
     * @ignore
     */
    public function key(): string|int|null
    {
        $this->verifyNodeList();

        return key($this->nodeList);
    }

    /**
     * @ignore
     */
    public function valid(): bool
    {
        $this->verifyNodeList();

        return $this->key() !== null;
    }

    /**
     * @ignore
     */
    public function next(): void
    {
        $this->verifyNodeList();

        next($this->nodeList);
    }

    /**
     * @ignore
     */
    public function rewind(): void
    {
        $this->verifyNodeList();

        reset($this->nodeList);
    }

    /**
     * @ignore
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists((string)$offset, $this->nodeInfo);
    }

    /**
     * @throws NodeException
     * @ignore
     */
    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            $this->fetchNodeInfo();
        }

        if (!$this->offsetExists($offset)) {
            throw new NodeException("node '" . get_class($this) . "' has no property named '" . $offset . "'");
        }

        return $this->nodeInfo[(string)$offset];
    }

    /**
     * @throws NodeException
     * @ignore
     */
    public function offsetSet($offset, $value): void
    {
        if (method_exists($this, "modify")) {
            $this->modify([(string)$offset => $value]);
            return;
        }

        throw new NodeException("node '" . get_class($this) . "' is read only");
    }

    /**
     * @ignore
     */
    public function offsetUnset($offset): void
    {
        unset($this->nodeInfo[(string)$offset]);
    }

    /**
     * @throws NodeException
     * @ignore
     */
    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    /**
     * @throws NodeException
     * @ignore
     */
    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }
}
