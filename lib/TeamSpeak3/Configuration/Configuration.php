<?php

namespace TeamSpeak3\Configuration;

use Symfony\Component\Yaml\Yaml;

abstract class Configuration
{
  const AUTHTYPE_BASIC = 0x01;
  const AUTHTYPE_TOKEN = 0x02;
  
  const PROTOCOL_TCP = 0x01;
  const PROTOCOL_UDP = 0x02;
  
  const CONFIG_DIR = '.ts3config';
  
  /**
   * @var static Singleton
   */
  protected static $instance;
  
  /**
   * @var array raw configuration loaded directly from YAML config file
   */
  protected static $config;
  
  /**
   * Unique application which extending configuration class is used for.
   *
   * @var string
   */
  protected static $commonName;
  
  /**
   * @var string filepath of configuration file. Default:
   *      <CONFIG_DIR>/<commonName>.yml
   */
  protected static $path;
  
  /**
   * @var string target for connection
   */
  protected static $host;
  
  /**
   * @var int bitmask for allowed protocols. @see static::PROTOCOL_*
   */
  protected static $protocol;
  
  /**
   * @var int bitmask of available authorization types. @see static::AUTHTYPE_*
   */
  protected static $authtype;
  
  /**
   * Loads configuration from default filepath, parameter filepath or array.
   * Assumes loaded file is YAML config file. @see Yaml::parse()
   * Assumes (array) parameter is assoc with same format as YAML file.
   * 
   * @param null|string|array $config
   *
   * @throws \TeamSpeak3\Configuration\Exception
   * @return static
   */
  public function load($config = null) {
    // Allow setting config filepath at runtime
    if (is_string($config))
      $config = @file_get_contents($config);
    
    // Load config from default YAML filepath
    if ($config === null)
      $config = @file_get_contents(static::$path);
  
    if ($config === false) 
      throw new Exception('Failed loading config `' . static::$path . '`.');
    
    if (empty($config)) {
      throw new Exception('Loaded empty config:' . PHP_EOL
        . print_r($config, true));
    }
    
    // Allow setting config at runtime
    static::$config = is_array($config) ? $config : Yaml::parse($config);
    
    assert(is_array(static::$config), 
      'Expected static::$config to be (array), instead got `'
      .gettype(static::$config).'`.');
    assert(!empty(static::$config),
      'Expected static::$config to not be empty, instead got `'
      . print_r(static::$config, true) . '`.');
    
    // Validate and set configuration properties. Most extensions will override.
    static::$instance->initConfig();
    
    return static::$instance;
  }
  
  /**
   * @return array parsed configuration following YAML config format
   */
  public function getConfig(): array {
    return static::$config;
  }
  
  /**
   * @return int bitmask of available authorizations for this configuration.
   */
  public function getAuthType() : int {
    return static::$authtype;
  }
  
  /**
   * @return string target host for connection
   */
  public function getHost() : string {
    return static::$host;
  }
  
  /**
   * @param string $host
   *
   * @return static
   * @throws Exception when host parameter is empty.
   */
  protected function setHost(string $host) {
    if (empty($host))
      throw new Exception('Received empty host parameter, expected (string)');
    static::$host = $host;
    return static::$instance;
  }
  
  /**
   * Validate and set configuration properties
   */
  protected function initConfig() {
    static::$instance->setHost(static::$config['server']['host']);
  }
  
  /**
   * On first time:
   *   - Enforce every subclass sets package name. @see $commonName
   *   - Set default config filepath from CONFIG_DIR and $commonName. @see $path
   * Set singleton instance and return it for fluency.
   *
   * @return static
   * @throws \TeamSpeak3\Configuration\Exception
   */
  final public static function getInstance() {
    if (static::$instance === null) {
      // Enforce subclass requirements
      if (is_subclass_of(static::$instance, self::class)) {
        // Subclasses should declare common name used for defaults, etc
        if (static::$commonName === null) {
          throw new Exception('Implementation Error - Subclass `' . static::class . '` must set unique `' . static::class . '::commonName` string.');
        }
        if (!is_string(static::$commonName)) {
          throw new Exception('Implementation Error - `' . static::class . '::commonName` must be of type `string`.');
        }
        if (static::$path === null) {
          static::$path = rtrim(static::CONFIG_DIR, " \t\n\r\0\x0B/")
            . '/' . static::$commonName . '.yml';
        }
      }
      static::$instance = new static();
    }
    return static::$instance;
  }
}
