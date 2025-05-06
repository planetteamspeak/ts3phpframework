<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler;

/**
 * Class Timer
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler
 * @class Timer
 * @brief Helper class providing profiler timers.
 */
class Timer
{
    /**
     * Indicates wether the timer is running or not.
     *
     * @var boolean
     */
    protected bool $running = false;

    /**
     * Stores the timestamp in microseconds when the timer was last started.
     *
     * @var float
     */
    protected float $started = 0;

    /**
     * Stores the timer name.
     *
     * @var string
     */
    protected string $name;

    /**
     * Stores various information about the server environment.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Timer constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $this->data["runtime"] = 0;
        $this->data["realmem"] = 0;
        $this->data["emalloc"] = 0;

        $this->start();
    }

    /**
     * Starts the timer.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->data["realmem_start"] = memory_get_usage(true);
        $this->data["emalloc_start"] = memory_get_usage();

        $this->started = microtime(true);
        $this->running = true;
    }

    /**
     * Stops the timer.
     *
     * @return void
     */
    public function stop(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->data["runtime"] += microtime(true) - $this->started;
        $this->data["realmem"] += memory_get_usage(true) - $this->data["realmem_start"];
        $this->data["emalloc"] += memory_get_usage() - $this->data["emalloc_start"];

        $this->started = 0;
        $this->running = false;
    }

    /**
     * Return the timer runtime in microseconds.
     *
     * @return float
     */
    public function getRuntime(): float
    {
        if ($this->isRunning()) {
            $this->stop();
            $this->start();
        }

        return $this->data["runtime"];
    }

    /**
     * Returns the amount of memory allocated to PHP in bytes.
     *
     * @param boolean $realmem
     * @return integer
     */
    public function getMemUsage(bool $realmem = false): int
    {
        if ($this->isRunning()) {
            $this->stop();
            $this->start();
        }

        return ($realmem !== false) ? $this->data["realmem"] : $this->data["emalloc"];
    }

    /**
     * Returns TRUE if the timer is running.
     *
     * @return boolean
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
}
