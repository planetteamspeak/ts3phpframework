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

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\SignalException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal\Handler;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;

/**
 * Class Signal
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper
 * @class Signal
 * @brief Helper class for signal slots.
 */
class Signal
{
    /**
     * Stores the PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal object.
     *
     * @var Signal
     */
    protected static $instance = null;

    /**
     * Stores subscribed signals and their slots.
     *
     * @var array
     */
    protected $sigslots = [];

    /**
     * Emits a signal with a given set of parameters.
     *
     * @param string $signal
     * @param mixed $params
     * @return mixed|void
     * @todo: Confirm / fix $return is set to last $slot->call() return value.
     *      It appears all previous calls before last are lost / ignored.
     *
     */
    public function emit($signal, $params = null)
    {
        if (!$this->hasHandlers($signal)) {
            return;
        }

        if (!is_array($params)) {
            $params = func_get_args();
            $params = array_slice($params, 1);
        }

        foreach ($this->sigslots[$signal] as $slot) {
            $return = $slot->call($params);
        }

        return $return;
    }

    /**
     * Generates a MD5 hash based on a given callback.
     *
     * @param mixed $callback
     * @param string
     * @return string
     * @throws HelperException
     */
    public function getCallbackHash($callback)
    {
        if (!is_callable($callback, true, $callable_name)) {
            throw new SignalException("invalid callback specified");
        }

        return md5($callable_name);
    }

    /**
     * Subscribes to a signal and returns the signal handler.
     *
     * @param string $signal
     * @param mixed $callback
     * @return Signal
     * @throws HelperException
     */
    public function subscribe($signal, $callback)
    {
        if (empty($this->sigslots[$signal])) {
            $this->sigslots[$signal] = [];
        }

        $index = $this->getCallbackHash($callback);

        if (!array_key_exists($index, $this->sigslots[$signal])) {
            $this->sigslots[$signal][$index] = new Handler($signal, $callback);
        }

        return $this->sigslots[$signal][$index];
    }

    /**
     * Unsubscribes from a signal.
     *
     * @param string $signal
     * @param mixed $callback
     * @return void
     * @throws HelperException
     */
    public function unsubscribe($signal, $callback = null)
    {
        if (!$this->hasHandlers($signal)) {
            return;
        }

        if ($callback !== null) {
            $index = $this->getCallbackHash($callback);

            if (!array_key_exists($index, $this->sigslots[$signal])) {
                return;
            }

            unset($this->sigslots[$signal][$index]);
        } else {
            unset($this->sigslots[$signal]);
        }
    }

    /**
     * Returns all registered signals.
     *
     * @return array
     */
    public function getSignals()
    {
        return array_keys($this->sigslots);
    }

    /**
     * Returns TRUE there are slots subscribed for a specified signal.
     *
     * @param string $signal
     * @return boolean
     */
    public function hasHandlers($signal)
    {
        return empty($this->sigslots[$signal]) ? false : true;
    }

    /**
     * Returns all slots for a specified signal.
     *
     * @param string $signal
     * @return array
     */
    public function getHandlers($signal)
    {
        if ($this->hasHandlers($signal)) {
            return $this->sigslots[$signal];
        }

        return [];
    }

    /**
     * Clears all slots for a specified signal.
     *
     * @param string $signal
     * @return void
     */
    public function clearHandlers($signal)
    {
        if ($this->hasHandlers($signal)) {
            unset($this->sigslots[$signal]);
        }
    }

    /**
     * Returns a singleton instance of PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal.
     *
     * @return Signal
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
