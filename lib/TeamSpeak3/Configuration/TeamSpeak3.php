<?php

namespace TeamSpeak3\Configuration;

use TeamSpeak3\Configuration\Authentication\BasicInterface;

class TeamSpeak3 extends Configuration implements BasicInterface
{
  
  const TEAMSPEAK3_ADAPTER_TYPES = ['voice', 'query', 'file', 'tsdns'];
  
  /**
   * @var string Common name for TeamSpeak3 package configuration
   */
  protected static $commonName = 'teamspeak3';
  
  /**
   * @var array ports used by TeamSpeak3 server
   */
  protected static $ports;
  
  /**
   * @var string ServerQuery login username
   */
  protected static $username;
  
  /**
   * @var string ServerQuery login password
   */
  protected static $password;
  
  /**
   * Validate and set config properties. @see Configuration::load()
   */
  protected function initConfig() {
    parent::initConfig();
    
    static::$instance->setPorts(static::$config['server']['ports']);
    static::$instance->setUsername(static::$config['auth']['query']['username']);
    static::$instance->setPassword(static::$config['auth']['query']['password']);
  }
  
  /**
   * Get ServerQuery login username
   * 
   * @return string ServerQuery username
   */
  public function getUsername() : string {
    return static::$username;
  }
  
  /**
   * Set ServerQuery login username
   *
   * @param string $username ServerQuery username
   *
   * @return TeamSpeak3
   */
  protected function setUsername(string $username): TeamSpeak3 {
    static::$username = $username;
    return static::$instance;
  }
  
  /**
   * Get ServerQuery password
   * 
   * @return string ServerQuery login password
   */
  public function getPassword() : string {
    return static::$password;
  }
  
  /**
   * Set ServerQuery login password
   *
   * @param string $password ServerQuery login password
   *
   * @return TeamSpeak3
   */
  protected function setPassword(string $password): TeamSpeak3 {
    static::$password = $password;
    return static::$instance;
  }
  
  /**
   * @param string $adapter configured type of port
   *
   * @return int port number for adapter in range 0 to 65536 (inclusive)
   * @throws \OutOfBoundsException when port not configured for given adapter
   */
  public function getPort(string $adapter): int {
    if (array_key_exists($adapter,static::$ports))
      throw new \OutOfBoundsException(
        'Unknown port type `'.print_r($adapter, true).'`.');
    
    return static::$ports[$adapter];
  }
  
  /**
   * @param array $ports indexed by adaptor name for each port
   *
   * @return TeamSpeak3 singleton instance for fluency
   * @throws Exception when trying to use an empty set of ports
   */
  protected function setPorts(array $ports) : TeamSpeak3 {
    if (empty($ports))
      throw new Exception('Received empty port parameter, expected (string)');
    static::$ports = $ports;
    return static::$instance;
  }
}
