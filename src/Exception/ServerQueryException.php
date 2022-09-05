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
