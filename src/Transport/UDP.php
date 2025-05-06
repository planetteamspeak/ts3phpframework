<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Transport;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

/**
 * Class UDP
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Transport
 * @class UDP
 * @brief Class for connecting to a remote server through UDP.
 */
class UDP extends Transport
{
    /**
     * Connects to a remote server.
     *
     * @return void
     * @throws TransportException
     */
    public function connect(): void
    {
        if ($this->stream !== null) {
            return;
        }

        $host = strval($this->config["host"]);
        $port = strval($this->config["port"]);

        $address = "udp://" . (str_contains($host, ":") ? "[" . $host . "]" : $host) . ":" . $port;
        $timeout = (int)$this->config["timeout"];

        $this->stream = @stream_socket_client($address, $errno, $errstr, $timeout);

        if ($this->stream === false) {
            throw new TransportException(StringHelper::factory($errstr)->toUtf8()->toString(), $errno);
        }

        @stream_set_timeout($this->stream, $timeout);
        @stream_set_blocking($this->stream, $this->config["blocking"] ? 1 : 0);
    }

    /**
     * Disconnects from a remote server.
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->stream === null) {
            return;
        }

        $this->stream = null;

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "Disconnected");
    }

    /**
     * Reads data from the stream.
     *
     * @param integer $length
     * @return StringHelper
     * @throws TransportException
     */
    public function read(int $length = 4096): StringHelper
    {
        $this->connect();
        $this->waitForReadyRead();

        $data = @fread($this->stream, $length);

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

        if ($data === false) {
            throw new TransportException("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
        }

        return new StringHelper($data);
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return void
     * @throws TransportException
     */
    public function send(string $data): void
    {
        $this->connect();

        @stream_socket_sendto($this->stream, $data);

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataSend", $data);
    }
}
