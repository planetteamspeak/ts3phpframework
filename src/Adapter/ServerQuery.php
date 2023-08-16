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

namespace PlanetTeamSpeak\TeamSpeak3Framework\Adapter;

use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Event;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport;

/**
 * @class ServerQuery
 * @brief Provides low-level methods for ServerQuery communication with a TeamSpeak 3 Server.
 */
class ServerQuery extends Adapter
{
    /**
     * Stores a singleton instance of the active Host object.
     *
     * @var Host|null
     */
    protected ?Host $host = null;

    /**
     * Stores the timestamp of the last command.
     *
     * @var integer|null
     */
    protected ?int $timer = null;

    /**
     * Number of queries executed on the server.
     *
     * @var integer
     */
    protected int $count = 0;

    /**
     * Stores an array with unsupported commands.
     *
     * @var array
     */
    protected array $block = ["help"];

    /**
     * Connects the Transport object and performs initial actions on the remote
     * server.
     *
     * @return void
     * @throws AdapterException
     */
    protected function syn(): void
    {
        $this->initTransport($this->options);
        $this->transport->setAdapter($this);

        Profiler::init(spl_object_hash($this));

        $rdy = $this->getTransport()->readLine();

        if (!$rdy->startsWith(TeamSpeak3::TS3_PROTO_IDENT) && !$rdy->startsWith(TeamSpeak3::TEA_PROTO_IDENT) && !(defined("CUSTOM_PROTO_IDENT") && $rdy->startsWith(CUSTOM_PROTO_IDENT))) {
            throw new AdapterException("invalid reply from the server (" . $rdy . ")");
        }

        Signal::getInstance()->emit("serverqueryConnected", $this);
    }

    /**
     * The ServerQuery destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        // do not disconnect, when acting as bot in non-blocking mode
        if (! $this->getTransport()->getConfig("blocking")) {
            return;
        }

        if ($this->getTransport() instanceof Transport && $this->transport->isConnected()) {
            try {
                $this->request("quit");
            } catch (AdapterException) {
                return;
            }
        }
    }

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
        $query = StringHelper::factory($cmd)->section(TeamSpeak3::SEPARATOR_CELL);

        if (strstr($cmd, "\r") || strstr($cmd, "\n")) {
            throw new AdapterException("illegal characters in command '" . $query . "'");
        } elseif (in_array($query, $this->block)) {
            throw new ServerQueryException("command not found", 0x100);
        }

        Signal::getInstance()->emit("serverqueryCommandStarted", $cmd);

        $this->getProfiler()->start();
        $this->getTransport()->sendLine($cmd);
        $this->timer = time();
        $this->count++;

        $rpl = [];

        do {
            if (! $this->getTransport()->isConnected()) {
                break;
            }
            $str = $this->getTransport()->readLine();
            $rpl[] = $str;
        } while ($str->section(TeamSpeak3::SEPARATOR_CELL) != TeamSpeak3::ERROR);

        $this->getProfiler()->stop();

        $reply = new Reply($rpl, $cmd, $this->getHost(), $throw);

        Signal::getInstance()->emit("serverqueryCommandFinished", $cmd, $reply);

        return $reply;
    }

    /**
     * Waits for the server to send a notification message and returns the result.
     *
     * @return Event
     * @throws AdapterException
     */
    public function wait(): Event
    {
        if ($this->getTransport()->getConfig("blocking")) {
            throw new AdapterException("only available in non-blocking mode");
        }

        do {
            if (! $this->getTransport()->isConnected()) {
                break;
            }
            $evt = $this->getTransport()->readLine();
        } while (!$evt->section(TeamSpeak3::SEPARATOR_CELL)->startsWith(TeamSpeak3::EVENT));

        return new Event($evt, $this->getHost());
    }

    /**
     * Uses given parameters and returns a prepared ServerQuery command.
     *
     * @param string $cmd
     * @param array $params
     * @return string
     */
    public function prepare(string $cmd, array $params = []): string
    {
        $args = [];
        $cells = [];

        foreach ($params as $ident => $value) {
            $ident = is_numeric($ident) ? "" : strtolower($ident) . TeamSpeak3::SEPARATOR_PAIR;

            if (is_array($value)) {
                $value = array_values($value);

                for ($i = 0; $i < count($value); $i++) {
                    if ($value[$i] === null) {
                        continue;
                    } elseif ($value[$i] === false) {
                        $value[$i] = 0x00;
                    } elseif ($value[$i] === true) {
                        $value[$i] = 0x01;
                    } elseif ($value[$i] instanceof Node) {
                        $value[$i] = $value[$i]->getId();
                    }

                    $cells[$i][] = $ident . StringHelper::factory($value[$i])->escape()->toUtf8();
                }
            } else {
                if ($value === null) {
                    continue;
                } elseif ($value === false) {
                    $value = 0x00;
                } elseif ($value === true) {
                    $value = 0x01;
                } elseif ($value instanceof Node) {
                    $value = $value->getId();
                }

                $args[] = $ident . StringHelper::factory($value)->escape()->toUtf8();
            }
        }

        foreach (array_keys($cells) as $ident) {
            $cells[$ident] = implode(TeamSpeak3::SEPARATOR_CELL, $cells[$ident]);
        }

        if (count($args)) {
            $cmd .= " " . implode(TeamSpeak3::SEPARATOR_CELL, $args);
        }
        if (count($cells)) {
            $cmd .= " " . implode(TeamSpeak3::SEPARATOR_LIST, $cells);
        }

        return trim($cmd);
    }

    /**
     * Returns the timestamp of the last command.
     *
     * @return int|null
     */
    public function getQueryLastTimestamp(): ?int
    {
        return $this->timer;
    }

    /**
     * Returns the number of queries executed on the server.
     *
     * @return integer
     */
    public function getQueryCount(): int
    {
        return $this->count;
    }

    /**
     * Returns the total runtime in microseconds of all queries.
     *
     * @return float
     */
    public function getQueryRuntime(): float
    {
        return $this->getProfiler()->getRuntime();
    }

    /**
     * Returns the Host object of the current connection.
     *
     * @return Host|null
     */
    public function getHost(): ?Host
    {
        if ($this->host === null) {
            $this->host = new Host($this);
        }

        return $this->host;
    }
}
