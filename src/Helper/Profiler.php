<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper;

use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler\Timer;

/**
 * Class Profiler
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper
 * @class Profiler
 * @brief Helper class for profiler handling.
 */
class Profiler
{
    /**
     * Stores various timers for code profiling.
     *
     * @var array
     */
    protected static array $timers = [];

    /**
     * Inits a timer.
     *
     * @param string $name
     * @return void
     */
    public static function init(string $name = "default"): void
    {
        self::$timers[$name] = new Timer($name);
    }

    /**
     * Starts a timer.
     *
     * @param string $name
     * @return void
     */
    public static function start(string $name = "default"): void
    {
        if (array_key_exists($name, self::$timers)) {
            self::$timers[$name]->start();
        } else {
            self::$timers[$name] = new Timer($name);
        }
    }

    /**
     * Stops a timer.
     *
     * @param string $name
     * @return void
     */
    public static function stop(string $name = "default"): void
    {
        if (!array_key_exists($name, self::$timers)) {
            self::init($name);
        }

        self::$timers[$name]->stop();
    }

    /**
     * Returns a timer.
     *
     * @param string $name
     * @return Timer
     */
    public static function get(string $name = "default"): Timer
    {
        if (!array_key_exists($name, self::$timers)) {
            self::init($name);
        }

        return self::$timers[$name];
    }
}
