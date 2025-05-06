<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Exception;

use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

/**
 * Class TeamSpeak3Exception
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Exception
 * @class TeamSpeak3Exception
 * @brief Enhanced exception class for TeamSpeak3 objects.
 */
class TeamSpeak3Exception extends Exception
{
    /**
     * Stores the original error code.
     *
     * @var integer
     */
    protected int $raw_code = 0x00;

    /**
     * Stores the original error message.
     *
     * @var string|StringHelper|null
     */
    protected StringHelper|string|null $raw_mesg = null;

    /**
     * Stores custom error messages.
     *
     * @var array
     */
    protected static array $messages = [];

    /**
     * The TeamSpeak3Exception constructor.
     *
     * @param string $mesg
     * @param integer $code
     */
    public function __construct(string $mesg, int $code = 0x00)
    {
        parent::__construct($mesg, $code);

        $this->raw_code = $code;
        $this->raw_mesg = $mesg;

        if (array_key_exists($code, self::$messages)) {
            $this->message = $this->prepareCustomMessage(self::$messages[$code]);
        }

        Signal::getInstance()->emit("errorException", $this);
    }

    /**
     * Prepares a custom error message by replacing pre-defined signs with given values.
     *
     * @param StringHelper $mesg
     * @return string
     */
    protected function prepareCustomMessage(StringHelper $mesg): string
    {
        $args = [
            "code" => $this->getCode(),
            "mesg" => $this->getMessage(),
            "line" => $this->getLine(),
            "file" => $this->getFile(),
        ];

        return $mesg->arg($args)->toString();
    }

    /**
     * Registers a custom error message to $code.
     *
     * @param integer $code
     * @param string $mesg
     * @return void
     * @throws TeamSpeak3Exception
     */
    public static function registerCustomMessage(int $code, string $mesg): void
    {
        if (array_key_exists($code, self::$messages)) {
            throw new self("custom message for code 0x" . strtoupper(dechex($code)) . " is already registered");
        }

        self::$messages[$code] = new StringHelper($mesg);
    }

    /**
     * Unregisters a custom error message from $code.
     *
     * @param integer $code
     * @return void
     * @throws TeamSpeak3Exception
     */
    public static function unregisterCustomMessage(int $code): void
    {
        if (!array_key_exists($code, self::$messages)) {
            throw new self("custom message for code 0x" . strtoupper(dechex($code)) . " is not registered");
        }

        unset(self::$messages[$code]);
    }

    /**
     * Returns the original error code.
     *
     * @return integer
     */
    public function getRawCode(): int
    {
        return $this->raw_code;
    }

    /**
     * Returns the original error message.
     *
     * @return string|StringHelper|null
     */
    public function getRawMessage(): string|StringHelper|null
    {
        return $this->raw_mesg;
    }

    /**
     * Returns the class from which the exception was thrown.
     *
     * @return string
     */
    public function getSender(): string
    {
        $trace = $this->getTrace();

        return (isset($trace[0]["class"])) ? $trace[0]["class"] : "{main}";
    }
}
