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
