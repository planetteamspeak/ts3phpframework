<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Exception;

/**
 * Class ServerQueryException
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Exception
 * @class ServerQueryException
 * @brief Enhanced exception class for PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery objects.
 */
class ServerQueryException extends AdapterException
{
    /**
     * Stores the optional return code for ServerQuery errors.
     *
     * @var string|null
     */
    protected ?string $return_code;

    /**
     * The PlanetTeamSpeak\TeamSpeak3Framework\ServerQuery\Exception constructor.
     *
     * @param string $mesg
     * @param integer $code
     * @param string $return_code
     */
    public function __construct(string $mesg, int $code = 0x00, $return_code = null)
    {
        parent::__construct($mesg, $code);

        $this->return_code = $return_code;
    }

    /**
     * Returns TRUE if the exception provides a return code for ServerQuery errors.
     *
     * @return boolean
     */
    public function hasReturnCode(): bool
    {
        return $this->return_code !== null;
    }

    /**
     * Returns the optional return code for ServerQuery errors.
     *
     * @return string|null
     */
    public function getReturnCode(): ?string
    {
        return $this->return_code;
    }
}
