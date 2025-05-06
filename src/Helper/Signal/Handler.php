<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\SignalException;

/**
 * Class Handler
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal
 * @class Handler
 * @brief Helper class providing handler functions for signals.
 */
class Handler
{
    /**
     * Stores the name of the subscribed signal.
     *
     * @var string
     */
    protected string $signal;

    /**
     * Stores the callback function for the subscribed signal.
     *
     * @var mixed
     */
    protected mixed $callback;

    /**
     * Handler constructor.
     *
     * @param string $signal
     * @param mixed $callback
     * @throws SignalException
     */
    public function __construct(string $signal, mixed $callback)
    {
        $this->signal = $signal;

        if (!is_callable($callback)) {
            throw new SignalException("invalid callback specified for signal '" . $signal . "'");
        }

        $this->callback = $callback;
    }

    /**
     * Invoke the signal handler.
     *
     * @param array $args
     * @return mixed
     */
    public function call(array $args = []): mixed
    {
        return call_user_func_array($this->callback, $args);
    }
}
