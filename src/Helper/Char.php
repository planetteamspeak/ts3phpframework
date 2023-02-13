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

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;

/**
 * Class Char
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper
 * @class Char
 * @brief Helper class for char handling.
 */
class Char
{
    /**
     * Stores the original character.
     *
     * @var string
     */
    protected string $char;

    /**
     * Char constructor.
     *
     * @param string $char
     * @throws HelperException
     */
    public function __construct(string $char)
    {
        if (strlen($char) != 1) {
            throw new HelperException("char parameter may not contain more or less than one character");
        }

        $this->char = $char;
    }

    /**
     * Returns true if the character is a letter.
     *
     * @return boolean
     */
    public function isLetter(): bool
    {
        return ctype_alpha($this->char);
    }

    /**
     * Returns true if the character is a decimal digit.
     *
     * @return boolean
     */
    public function isDigit(): bool
    {
        return ctype_digit($this->char);
    }

    /**
     * Returns true if the character is a space.
     *
     * @return boolean
     */
    public function isSpace(): bool
    {
        return ctype_space($this->char);
    }

    /**
     * Returns true if the character is a mark.
     *
     * @return boolean
     */
    public function isMark(): bool
    {
        return ctype_punct($this->char);
    }

    /**
     * Returns true if the character is a control character (i.e. "\t").
     *
     * @return boolean
     */
    public function isControl(): bool
    {
        return ctype_cntrl($this->char);
    }

    /**
     * Returns true if the character is a printable character.
     *
     * @return boolean
     */
    public function isPrintable(): bool
    {
        return ctype_print($this->char);
    }

    /**
     * Returns true if the character is the Unicode character 0x0000 ("\0").
     *
     * @return boolean
     */
    public function isNull(): bool
    {
        return $this->char === "\0";
    }

    /**
     * Returns true if the character is an uppercase letter.
     *
     * @return boolean
     */
    public function isUpper(): bool
    {
        return $this->char === strtoupper($this->char);
    }

    /**
     * Returns true if the character is a lowercase letter.
     *
     * @return boolean
     */
    public function isLower(): bool
    {
        return $this->char === strtolower($this->char);
    }

    /**
     * Returns the uppercase equivalent if the character is lowercase.
     *
     * @return Char
     * @throws HelperException
     */
    public function toUpper(): Char
    {
        return ($this->isUpper()) ? $this : new self(strtoupper($this));
    }

    /**
     * Returns the lowercase equivalent if the character is uppercase.
     *
     * @return Char
     * @throws HelperException
     */
    public function toLower(): Char
    {
        return ($this->isLower()) ? $this : new self(strtolower($this));
    }

    /**
     * Returns the ascii value of the character.
     *
     * @return integer
     */
    public function toAscii(): int
    {
        return ord($this->char);
    }

    /**
     * Returns the Unicode value of the character.
     *
     * @return integer
     */
    public function toUnicode(): int
    {
        $h = ord($this->char[0]);

        if ($h <= 0x7F) {
            return $h;
        } elseif ($h < 0xC2) {
            return false;
        } elseif ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (ord($this->char[1]) & 0x3F);
        } elseif ($h <= 0xEF) {
            return ($h & 0x0F) << 12 | (ord($this->char[1]) & 0x3F) << 6 | (ord($this->char[2]) & 0x3F);
        } elseif ($h <= 0xF4) {
            return ($h & 0x0F) << 18 | (ord($this->char[1]) & 0x3F) << 12 | (ord($this->char[2]) & 0x3F) << 6 | (ord($this->char[3]) & 0x3F);
        } else {
            return -1;
        }
    }

    /**
     * Returns the hexadecimal value of the char.
     *
     * @return string
     */
    public function toHex(): string
    {
        return strtoupper(dechex($this->toAscii()));
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Helper\Char based on a given hex value.
     *
     * @param string $hex
     * @return Char
     * @throws HelperException
     */
    public static function fromHex(string $hex): Char
    {
        if (strlen($hex) != 2) {
            throw new HelperException("given parameter '" . $hex . "' is not a valid hexadecimal number");
        }

        return new self(chr(hexdec($hex)));
    }

    /**
     * Returns the character as a standard string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->char;
    }

    /**
     * Returns the integer value of the character.
     *
     * @return integer
     */
    public function toInt(): int
    {
        return intval($this->char);
    }

    /**
     * Returns the character as a standard string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->char;
    }
}
