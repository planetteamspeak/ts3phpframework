<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Transport;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

/**
 * Class TCP
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Transport
 * @class TCP
 * @brief Class for connecting to a remote server through TCP.
 */
class TCP extends Transport
{
    /**
     * Connects to a remote server.
     *
     * @return void
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function connect(): void
    {
        if ($this->stream !== null) {
            return;
        }

        $host = strval($this->config["host"]);
        $port = strval($this->config["port"]);
        $timeout = intval($this->config["timeout"]);
        $blocking = intval($this->config["blocking"]);

        if (empty($this->config["ssh"])) {
            $address = "tcp://" . (str_contains($host, ":") ? "[" . $host . "]" : $host) . ":" . $port;
            $options = empty($this->config["tls"]) ? [] : ["ssl" => ["allow_self_signed" => true, "verify_peer" => false, "verify_peer_name" => false]];

            $this->stream = @stream_socket_client($address, $errno, $errstr, $this->config["timeout"], STREAM_CLIENT_CONNECT, stream_context_create($options));

            if ($this->stream === false) {
                throw new TransportException(StringHelper::factory($errstr)->toUtf8()->toString(), $errno);
            }

            if (!empty($this->config["tls"])) {
                stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
            }
        } else {
            $this->session = @ssh2_connect($host, $port);

            if ($this->session === false) {
                throw new TransportException("failed to establish secure shell connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "'");
            }

            if (!@ssh2_auth_password($this->session, $this->config["username"], $this->config["password"])) {
                throw new ServerQueryException("invalid loginname or password", 0x208);
            }

            $this->stream = @ssh2_shell($this->session, "raw");

            if ($this->stream === false) {
                throw new TransportException("failed to open a secure shell on server '" . $this->config["host"] . ":" . $this->config["port"] . "'");
            }
        }

        @stream_set_timeout($this->stream, $timeout);
        @stream_set_blocking($this->stream, $blocking ? 1 : 0);
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

        if (is_resource($this->session)) {
            @ssh2_disconnect($this->session);
        }

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "Disconnected");
    }

    /**
     * Reads data from the stream.
     *
     * @param integer $length
     * @return StringHelper
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function read(int $length = 4096): StringHelper
    {
        $this->connect();
        $this->waitForReadyRead();

        $data = @stream_get_contents($this->stream, $length);

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

        if ($data === false) {
            throw new TransportException("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
        }

        return new StringHelper($data);
    }

    /**
     * Reads a single line of data from the stream.
     *
     * @param string $token
     * @return StringHelper
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function readLine(string $token = "\n"): StringHelper
    {
        $this->connect();

        $line = StringHelper::factory("");

        while (!$line->endsWith($token)) {
            $this->waitForReadyRead();

            $data = @fgets($this->stream, 4096);

            Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

            if ($data === false) {
                if ($line->count()) {
                    $line->append($token);
                }
            } else {
                $line->append($data);
            }
        }

        return $line->trim();
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return void
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function send(string $data): void
    {
        $this->connect();

        @fwrite($this->stream, $data);

        Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataSend", $data);
    }

    /**
     * Writes a line of data to the stream.
     *
     * @param string $data
     * @param string $separator
     * @return void
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function sendLine(string $data, string $separator = "\n"): void
    {
        $size = strlen($data);
        $pack = 4096;

        for ($seek = 0; $seek < $size;) {
            $rest = $size - $seek;
            $pack = min($rest, $pack);
            $buff = substr($data, $seek, $pack);
            $seek = $seek + $pack;

            if ($seek >= $size) {
                $buff .= $separator;
            }

            $this->send($buff);
        }
    }
}
