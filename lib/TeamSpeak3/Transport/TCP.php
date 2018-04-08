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

/**
 * @class TeamSpeak3_Transport_TCP
 * @brief Class for connecting to a remote server through TCP.
 */
class TeamSpeak3_Transport_TCP extends TeamSpeak3_Transport_Abstract
{
  /**
   * Connects to a remote server.
   *
   * @throws TeamSpeak3_Transport_Exception
   * @return void
   */
  public function connect()
  {
    if($this->stream !== null) return;

    $host = strval($this->config["host"]);
    $port = strval($this->config["port"]);

    $address = "tcp://" . (strstr($host, ":") !== FALSE ? "[" . $host . "]" : $host) . ":" . $port;
    $options = empty($this->config["tls"]) ? array() : array("ssl" => array("allow_self_signed" => TRUE, "verify_peer" => FALSE, "verify_peer_name" => FALSE));
    $timeout = (int) $this->config["timeout"];

    $this->stream = @stream_socket_client($address, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, stream_context_create($options));

    if($this->stream === FALSE)
    {
      throw new TeamSpeak3_Transport_Exception(TeamSpeak3_Helper_String::factory($errstr)->toUtf8()->toString(), $errno);
    }

    if(!empty($this->config["tls"]))
    {
      stream_socket_enable_crypto($this->stream, TRUE, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
    }

    @stream_set_timeout($this->stream, $timeout);
    @stream_set_blocking($this->stream, $this->config["blocking"] ? 1 : 0);
  }

  /**
   * Disconnects from a remote server.
   *
   * @return void
   */
  public function disconnect()
  {
    if($this->stream === null) return;

    $this->stream = null;

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "Disconnected");
  }

  /**
   * Reads data from the stream.
   *
   * @param  integer $length
   * @throws TeamSpeak3_Transport_Exception
   * @return TeamSpeak3_Helper_String
   */
  public function read($length = 4096)
  {
    $this->connect();
    $this->waitForReadyRead();

    $data = @stream_get_contents($this->stream, $length);

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

    if($data === FALSE)
    {
      throw new TeamSpeak3_Transport_Exception("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
    }

    return new TeamSpeak3_Helper_String($data);
  }

  /**
   * Reads a single line of data from the stream.
   *
   * @param  string $token
   * @throws TeamSpeak3_Transport_Exception
   * @return TeamSpeak3_Helper_String
   */
  public function readLine($token = "\n")
  {
    $this->connect();

    $line = TeamSpeak3_Helper_String::factory("");

    while(!$line->endsWith($token))
    {
      $this->waitForReadyRead();

      $data = @fgets($this->stream, 4096);

      TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

      if($data === FALSE)
      {
        if($line->count())
        {
          $line->append($token);
        }
        else
        {
          throw new TeamSpeak3_Transport_Exception("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
        }
      }
      else
      {
        $line->append($data);
      }
    }

    return $line->trim();
  }

  /**
   * Writes data to the stream.
   *
   * @param  string $data
   * @return void
   */
  public function send($data)
  {
    $this->connect();

    @fwrite($this->stream, $data);

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataSend", $data);
  }

  /**
   * Writes a line of data to the stream.
   *
   * @param  string $data
   * @param  string $separator
   * @return void
   */
  public function sendLine($data, $separator = "\n")
  {
    $size = strlen($data);
    $pack = 4096;

    for($seek = 0 ;$seek < $size;)
    {
      $rest = $size-$seek;
      $pack = $rest < $pack ? $rest : $pack;
      $buff = substr($data, $seek, $pack);
      $seek = $seek+$pack;

      if($seek >= $size) $buff .= $separator;

      $this->send($buff);
    }
  }
}
