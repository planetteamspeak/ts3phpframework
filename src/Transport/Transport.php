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

namespace PlanetTeamSpeak\TeamSpeak3Framework\Transport;

use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

/**
 * Class Transport
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Transport
 * @class Transport
 * @brief Abstract class for connecting to a TeamSpeak 3 Server through different ways of transport.
 */
abstract class Transport
{
    /**
     * Stores user-provided configuration settings.
     *
     * @var array
     */
    protected array $config;

    /**
     * Stores the stream resource of the connection.
     *
     * @var resource
     */
    protected $stream = null;

    /**
     * Stores an optional stream session for the connection.
     *
     * @var resource
     */
    protected $session = null;

    /**
     * Stores the PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter object using this transport.
     *
     * @var Adapter|null
     */
    protected ?Adapter $adapter = null;

    /**
     * The PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport constructor.
     *
     * @param array $config
     * @return Transport
     * @throws TransportException
     */
    public function __construct(array $config)
    {
        if (!array_key_exists("host", $config)) {
            throw new TransportException("config must have a key for 'host' which specifies the server host name");
        }

        if (!array_key_exists("port", $config)) {
            throw new TransportException("config must have a key for 'port' which specifies the server port number");
        }

        if (!array_key_exists("timeout", $config)) {
            $config["timeout"] = 10;
        }

        if (!array_key_exists("blocking", $config)) {
            $config["blocking"] = 1;
        }

        $this->config = $config;
        return $this;
    }

    /**
     * Commit pending data.
     *
     * @return array
     */
    public function __sleep()
    {
        return ["config"];
    }

    /**
     * Reconnects to the remote server.
     *
     * @return void
     * @throws TransportException
     */
    public function __wakeup()
    {
        $this->connect();
    }

    /**
     * The PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->adapter instanceof Adapter) {
            $this->adapter->__destruct();
        }

        $this->disconnect();
    }

    /**
     * Connects to a remote server.
     *
     * @return void
     * @throws TransportException
     */
    abstract public function connect(): void;

    /**
     * Disconnects from a remote server.
     *
     * @return void
     */
    abstract public function disconnect(): void;

    /**
     * Reads data from the stream.
     *
     * @param integer $length
     * @return StringHelper
     * @throws TransportException
     */
    abstract public function read(int $length = 4096): StringHelper;

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return void
     */
    abstract public function send(string $data): void;

    /**
     * Returns the underlying stream resource.
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Returns the configuration variables in this adapter.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return array|string
     */
    public function getConfig(string $key = null, mixed $default = null): array|string|int
    {
        if ($key !== null) {
            return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
        }

        return $this->config;
    }

    /**
     * Sets the PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter object using this transport.
     *
     * @param Adapter $adapter
     * @return void
     */
    public function setAdapter(Adapter $adapter): void
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter object using this transport.
     *
     * @return Adapter|null
     */
    public function getAdapter(): ?Adapter
    {
        return $this->adapter;
    }

    /**
     * Returns the adapter type.
     *
     * @return string
     */
    public function getAdapterType(): string
    {
        if ($this->adapter instanceof Adapter) {
            $string = StringHelper::factory(get_class($this->adapter));

            return $string->substr($string->findLast("\\"))->replace(["\\", " "], "")->toString();
        }

        return "Unknown";
    }

    /**
     * Returns header/meta data from stream pointer.
     *
     * @return array
     * @throws TransportException
     */
    public function getMetaData(): array
    {
        if ($this->stream === null) {
            throw new TransportException("unable to retrieve header/meta data from stream pointer");
        }

        return stream_get_meta_data($this->stream);
    }

    /**
     * Returns TRUE if the transport is connected.
     *
     * @return boolean
     */
    public function isConnected(): bool
    {
        return ($this->getStream() === null) ? false : true;
    }

    /**
     * Blocks a stream until data is available for reading if the stream is connected
     * in non-blocking mode.
     *
     * @param integer $time
     * @return void
     */
    protected function waitForReadyRead(int $time = 0): void
    {
        if (!$this->isConnected() || $this->config["blocking"]) {
            return;
        }

        do {
            $read = [$this->stream];
            $null = null;

            if ($time) {
                Signal::getInstance()
                    ->emit(strtolower($this->getAdapterType()) . "WaitTimeout", $time, $this->getAdapter());
            }

            $time = $time + $this->config["timeout"];
        } while (@stream_select($read, $null, $null, $this->config["timeout"]) == 0);
    }
}
