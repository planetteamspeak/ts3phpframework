<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class StringHelper
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper
 * @class StringHelper
 * @brief Helper class for string handling.
 */
class StringHelper implements ArrayAccess, Iterator, Countable, JsonSerializable
{
    /**
     * Stores the original string.
     *
     * @var string
     */
    protected string $string;

    /**
     * @ignore
     * @var integer
     */
    protected int $position = 0;

    /**
     * The StringHelper constructor.
     *
     * @param string $string
     */
    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * Returns a StringHelper object for the given string.
     *
     * @param string $string
     * @return self
     */
    public static function factory(string $string): StringHelper
    {
        return new self($string);
    }

    /**
     * Replaces every occurrence of the string or array $search with the string or array $replace.
     *
     * @param array|string $search
     * @param array|string $replace
     * @param boolean $caseSensitivity
     * @return self
     */
    public function replace(array|string $search, array|string $replace, bool $caseSensitivity = true): static
    {
        if ($caseSensitivity) {
            $this->string = str_replace($search, $replace, $this->string);
        } else {
            $this->string = str_ireplace($search, $replace, $this->string);
        }

        return $this;
    }

    /**
     * This function replaces indexed or associative signs with given values.
     *
     * @param array $args
     * @param string $char
     * @return self
     */
    public function arg(array $args, string $char = "%"): static
    {
        $args = array_reverse($args, true);

        foreach ($args as $key => $val) {
            $args[$char . $key] = $val;
            unset($args[$key]);
        }

        $this->string = strtr($this->string, $args);

        return $this;
    }

    /**
     * Returns true if the string starts with $pattern.
     *
     * @param string $pattern
     * @return boolean
     */
    public function startsWith(string $pattern): bool
    {
        return str_starts_with($this->string, $pattern);
    }

    /**
     * Returns true if the string ends with $pattern.
     *
     * @param string $pattern
     * @return boolean
     */
    public function endsWith(string $pattern): bool
    {
        return str_ends_with($this->string, $pattern);
    }

    /**
     * Returns the position of the first occurrence of a char in a string.
     *
     * @param string $needle
     * @return integer
     */
    public function findFirst(string $needle): int
    {
        return strpos($this->string, $needle);
    }

    /**
     * Returns the position of the last occurrence of a char in a string.
     *
     * @param string $needle
     * @return integer
     */
    public function findLast(string $needle): int
    {
        return strrpos($this->string, $needle);
    }

    /**
     * Returns the lowercased string.
     *
     * @return self
     */
    public function toLower(): StringHelper
    {
        return new self(strtolower($this->string));
    }

    /**
     * Returns the uppercased string.
     *
     * @return self
     */
    public function toUpper(): StringHelper
    {
        return new self(strtoupper($this->string));
    }

    /**
     * Returns true if the string contains $pattern.
     *
     * @param string $pattern
     * @param boolean $regexp
     * @return boolean
     */
    public function contains(string $pattern, bool $regexp = false): bool
    {
        if (empty($pattern)) {
            return true;
        }

        if ($regexp) {
            return boolval(preg_match(sprintf("/%s/i", $pattern), $this->string));
        } else {
            return stristr($this->string, $pattern) !== false;
        }
    }

    /**
     * Returns part of a string.
     *
     * @param integer $start
     * @param integer|null $length
     * @return self
     */
    public function substr(int $start, int $length = null): StringHelper
    {
        $string = ($length !== null) ? substr($this->string, $start, $length) : substr($this->string, $start);

        return new self($string);
    }

    /**
     * Splits the string into substrings wherever $separator occurs.
     *
     * @param string $separator
     * @param integer $limit
     * @return array
     */
    public function split(string $separator, int $limit = 0): array
    {
        $parts = explode($separator, $this->string, ($limit) ?: $this->count());

        foreach ($parts as $key => $val) {
            $parts[$key] = new self($val);
        }

        return $parts;
    }

    /**
     * Appends $part to the string.
     *
     * @param string $part
     * @return self
     */
    public function append(string $part): static
    {
        $this->string = $this->string . $part;

        return $this;
    }

    /**
     * Prepends $part to the string.
     *
     * @param string $part
     * @return self
     */
    public function prepend(string $part): static
    {
        $this->string = $part . $this->string;

        return $this;
    }

    /**
     * Returns a section of the string.
     *
     * @param string $separator
     * @param integer $first
     * @param integer $last
     * @return StringHelper|null
     */
    public function section(string $separator, int $first = 0, int $last = 0): ?StringHelper
    {
        $sections = explode($separator, $this->string);

        $total = count($sections);

        if ($first > $total) {
            return null;
        }
        if ($first > $last) {
            $last = $first;
        }

        for ($i = 0; $i < $total; $i++) {
            if ($i < $first || $i > $last) {
                unset($sections[$i]);
            }
        }

        $string = implode($separator, $sections);

        return new self($string);
    }

    /**
     * Sets the size of the string to $size characters.
     *
     * @param integer $size
     * @param string $char
     * @return self
     */
    public function resize(int $size, string $char = "\0"): static
    {
        $chars = ($size - $this->count());

        if ($chars < 0) {
            $this->string = substr($this->string, 0, $chars);
        } elseif ($chars > 0) {
            $this->string = str_pad($this->string, $size, $char);
        }

        return $this;
    }

    /**
     * Strips whitespaces (or other characters) from the beginning and end of the string.
     *
     * @return self
     */
    public function trim(): static
    {
        $this->string = trim($this->string);

        return $this;
    }

    /**
     * Escapes a string using the TeamSpeak 3 escape patterns.
     *
     * @return self
     */
    public function escape(): static
    {
        foreach (TeamSpeak3::getEscapePatterns() as $search => $replace) {
            $this->string = str_replace($search, $replace, $this->string);
        }

        return $this;
    }

    /**
     * Unescapes a string using the TeamSpeak 3 escape patterns.
     *
     * @return self
     */
    public function unescape(): static
    {
        $this->string = strtr($this->string, array_flip(TeamSpeak3::getEscapePatterns()));

        return $this;
    }

    /**
     * Removes any non-alphanumeric characters from the string.
     *
     * @return self
     */
    public function filterAlnum(): static
    {
        $this->string = preg_replace("/[^[:alnum:]]/", "", $this->string);

        return $this;
    }

    /**
     * Removes any non-alphabetic characters from the string.
     *
     * @return self
     */
    public function filterAlpha(): static
    {
        $this->string = preg_replace("/[^[:alpha:]]/", "", $this->string);

        return $this;
    }

    /**
     * Removes any non-numeric characters from the string.
     *
     * @return self
     */
    public function filterDigits(): static
    {
        $this->string = preg_replace("/[^[:digit:]]/", "", $this->string);

        return $this;
    }

    /**
     * Returns TRUE if the string is a numeric value.
     *
     * @return boolean
     */
    public function isInt(): bool
    {
        return is_numeric($this->string) &&
            !$this->contains(".") &&
            !$this->contains("x") &&
            !$this->contains("e") &&
            !$this->contains("+") &&
            !$this->contains("-");
    }

    /**
     * Returns the integer value of the string.
     *
     * @return int
     */
    public function toInt(): int
    {
        if ($this->string == pow(2, 63) || $this->string == pow(2, 64) || $this->string > pow(2, 31)) {
            return -1;
        }

        return intval($this->string);
    }

    /**
     * Calculates and returns the crc32 polynomial of the string.
     *
     * @return int
     */
    public function toCrc32(): int
    {
        return crc32($this->string);
    }

    /**
     * Calculates and returns the md5 checksum of the string.
     *
     * @return string
     */
    public function toMd5(): string
    {
        return md5($this->string);
    }

    /**
     * Calculates and returns the sha1 checksum of the string.
     *
     * @return string
     */
    public function toSha1(): string
    {
        return sha1($this->string);
    }

    /**
     * Returns TRUE if the string is UTF-8 encoded. This method searches for non-ascii multibyte
     * sequences in the UTF-8 range.
     *
     * @return boolean
     */
    public function isUtf8(): bool
    {
        $pattern = [];

        $pattern[] = "[\xC2-\xDF][\x80-\xBF]";            // non-overlong 2-byte
        $pattern[] = "\xE0[\xA0-\xBF][\x80-\xBF]";        // excluding overlongs
        $pattern[] = "[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}"; // straight 3-byte
        $pattern[] = "\xED[\x80-\x9F][\x80-\xBF]";        // excluding surrogates
        $pattern[] = "\xF0[\x90-\xBF][\x80-\xBF]{2}";     // planes 1-3
        $pattern[] = "[\xF1-\xF3][\x80-\xBF]{3}";         // planes 4-15
        $pattern[] = "\xF4[\x80-\x8F][\x80-\xBF]{2}";     // plane 16

        return (bool)preg_match("%(?:" . implode("|", $pattern) . ")+%xs", $this->string);
    }

    /**
     * Converts the string to UTF-8.
     *
     * @return self
     */
    public function toUtf8(): static
    {
        if (!$this->isUtf8() && !$this->isInt()) {
            $this->string = mb_convert_encoding($this->string, 'UTF-8', mb_list_encodings());
        }

        return $this;
    }

    /**
     * Encodes the string with MIME base64 and returns the result.
     *
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->string);
    }

    /**
     * Decodes the string with MIME base64 and returns the result as an PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper
     *
     * @param string $base64
     * @return self
     */
    public static function fromBase64(string $base64): StringHelper
    {
        return new self(base64_decode($base64));
    }

    /**
     * Returns the hexadecimal value of the string.
     *
     * @return string
     */
    public function toHex(): string
    {
        $hex = "";

        foreach ($this as $char) {
            $hex .= $char->toHex();
        }

        return $hex;
    }

    /**
     * Returns the PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper based on a given hex value.
     *
     * @param string $hex
     * @return self
     * @throws HelperException
     */
    public static function fromHex(string $hex): StringHelper
    {
        $string = "";

        if (strlen($hex) % 2 == 1) {
            throw new HelperException("given parameter '" . $hex . "' is not a valid hexadecimal number");
        }

        foreach (str_split($hex, 2) as $chunk) {
            $string .= chr(hexdec($chunk));
        }

        return new self($string);
    }

    /**
     * Returns the string transliterated from UTF-8 to Latin.
     *
     * @return self
     */
    public function transliterate(): StringHelper
    {
        $utf8_accents = [
            "à" => "a",
            "ô" => "o",
            "ď" => "d",
            "ḟ" => "f",
            "ë" => "e",
            "š" => "s",
            "ơ" => "o",
            "ß" => "ss",
            "ă" => "a",
            "ř" => "r",
            "ț" => "t",
            "ň" => "n",
            "ā" => "a",
            "ķ" => "k",
            "ŝ" => "s",
            "ỳ" => "y",
            "ņ" => "n",
            "ĺ" => "l",
            "ħ" => "h",
            "ṗ" => "p",
            "ó" => "o",
            "ú" => "u",
            "ě" => "e",
            "é" => "e",
            "ç" => "c",
            "ẁ" => "w",
            "ċ" => "c",
            "õ" => "o",
            "ṡ" => "s",
            "ø" => "o",
            "ģ" => "g",
            "ŧ" => "t",
            "ș" => "s",
            "ė" => "e",
            "ĉ" => "c",
            "ś" => "s",
            "î" => "i",
            "ű" => "u",
            "ć" => "c",
            "ę" => "e",
            "ŵ" => "w",
            "ṫ" => "t",
            "ū" => "u",
            "č" => "c",
            "ö" => "oe",
            "è" => "e",
            "ŷ" => "y",
            "ą" => "a",
            "ł" => "l",
            "ų" => "u",
            "ů" => "u",
            "ş" => "s",
            "ğ" => "g",
            "ļ" => "l",
            "ƒ" => "f",
            "ž" => "z",
            "ẃ" => "w",
            "ḃ" => "b",
            "å" => "a",
            "ì" => "i",
            "ï" => "i",
            "ḋ" => "d",
            "ť" => "t",
            "ŗ" => "r",
            "ä" => "ae",
            "í" => "i",
            "ŕ" => "r",
            "ê" => "e",
            "ü" => "ue",
            "ò" => "o",
            "ē" => "e",
            "ñ" => "n",
            "ń" => "n",
            "ĥ" => "h",
            "ĝ" => "g",
            "đ" => "d",
            "ĵ" => "j",
            "ÿ" => "y",
            "ũ" => "u",
            "ŭ" => "u",
            "ư" => "u",
            "ţ" => "t",
            "ý" => "y",
            "ő" => "o",
            "â" => "a",
            "ľ" => "l",
            "ẅ" => "w",
            "ż" => "z",
            "ī" => "i",
            "ã" => "a",
            "ġ" => "g",
            "ṁ" => "m",
            "ō" => "o",
            "ĩ" => "i",
            "ù" => "u",
            "į" => "i",
            "ź" => "z",
            "á" => "a",
            "û" => "u",
            "þ" => "th",
            "ð" => "dh",
            "æ" => "ae",
            "µ" => "u",
            "ĕ" => "e",
            "œ" => "oe",
            "À" => "A",
            "Ô" => "O",
            "Ď" => "D",
            "Ḟ" => "F",
            "Ë" => "E",
            "Š" => "S",
            "Ơ" => "O",
            "Ă" => "A",
            "Ř" => "R",
            "Ț" => "T",
            "Ň" => "N",
            "Ā" => "A",
            "Ķ" => "K",
            "Ŝ" => "S",
            "Ỳ" => "Y",
            "Ņ" => "N",
            "Ĺ" => "L",
            "Ħ" => "H",
            "Ṗ" => "P",
            "Ó" => "O",
            "Ú" => "U",
            "Ě" => "E",
            "É" => "E",
            "Ç" => "C",
            "Ẁ" => "W",
            "Ċ" => "C",
            "Õ" => "O",
            "Ṡ" => "S",
            "Ø" => "O",
            "Ģ" => "G",
            "Ŧ" => "T",
            "Ș" => "S",
            "Ė" => "E",
            "Ĉ" => "C",
            "Ś" => "S",
            "Î" => "I",
            "Ű" => "U",
            "Ć" => "C",
            "Ę" => "E",
            "Ŵ" => "W",
            "Ṫ" => "T",
            "Ū" => "U",
            "Č" => "C",
            "Ö" => "Oe",
            "È" => "E",
            "Ŷ" => "Y",
            "Ą" => "A",
            "Ł" => "L",
            "Ų" => "U",
            "Ů" => "U",
            "Ş" => "S",
            "Ğ" => "G",
            "Ļ" => "L",
            "Ƒ" => "F",
            "Ž" => "Z",
            "Ẃ" => "W",
            "Ḃ" => "B",
            "Å" => "A",
            "Ì" => "I",
            "Ï" => "I",
            "Ḋ" => "D",
            "Ť" => "T",
            "Ŗ" => "R",
            "Ä" => "Ae",
            "Í" => "I",
            "Ŕ" => "R",
            "Ê" => "E",
            "Ü" => "Ue",
            "Ò" => "O",
            "Ē" => "E",
            "Ñ" => "N",
            "Ń" => "N",
            "Ĥ" => "H",
            "Ĝ" => "G",
            "Đ" => "D",
            "Ĵ" => "J",
            "Ÿ" => "Y",
            "Ũ" => "U",
            "Ŭ" => "U",
            "Ư" => "U",
            "Ţ" => "T",
            "Ý" => "Y",
            "Ő" => "O",
            "Â" => "A",
            "Ľ" => "L",
            "Ẅ" => "W",
            "Ż" => "Z",
            "Ī" => "I",
            "Ã" => "A",
            "Ġ" => "G",
            "Ṁ" => "M",
            "Ō" => "O",
            "Ĩ" => "I",
            "Ù" => "U",
            "Į" => "I",
            "Ź" => "Z",
            "Á" => "A",
            "Û" => "U",
            "Þ" => "Th",
            "Ð" => "Dh",
            "Æ" => "Ae",
            "Ĕ" => "E",
            "Œ" => "Oe",
        ];

        return new self($this->toUtf8()->replace(array_keys($utf8_accents), array_values($utf8_accents)));
    }

    /**
     * Processes the string and replaces all accented UTF-8 characters by unaccented ASCII-7 "equivalents",
     * whitespaces are replaced by a pre-defined spacer and the string is lowercase.
     *
     * @param string $spacer
     * @return self
     */
    public function uriSafe(string $spacer = "-"): StringHelper
    {
        $this->string = str_replace($spacer, " ", $this->string);
        $this->string = $this->transliterate();
        $this->string = preg_replace("/(\s|[^A-Za-z0-9\-])+/", $spacer, trim(strtolower($this->string)));
        $this->string = trim($this->string, $spacer);

        return new self($this->string);
    }

    /**
     * Replaces space characters with percent encoded strings.
     *
     * @return string
     */
    public function spaceToPercent(): string
    {
        return str_replace(" ", "%20", $this->string);
    }

    /**
     * Returns the string as a standard string
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->string;
    }

    /**
     * Magical function that allows you to call PHP's built-in string functions on the PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper object.
     *
     * @param string $function
     * @param array $args
     * @return self
     * @throws HelperException
     */
    public function __call(string $function, array $args)
    {
        if (!function_exists($function)) {
            throw new HelperException("cannot call undefined function '" . $function . "' on this object");
        }

        if (count($args)) {
            if (($key = array_search($this, $args, true)) !== false) {
                $args[$key] = $this->string;
            } else {
                throw new HelperException("cannot call undefined function '" . $function . "' without the " . __CLASS__ . " object parameter");
            }

            $return = call_user_func_array($function, $args);
        } else {
            $return = call_user_func($function, $this->string);
        }

        if (is_string($return)) {
            $this->string = $return;
        } else {
            return $return;
        }

        return $this;
    }

    /**
     * Returns the character as a standard string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string;
    }

    /**
     *  Return UTF-8 encoded string to for serializing to JSON.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->toUtf8()->string;
    }

    /**
     * @ignore
     */
    public function count(): int
    {
        return strlen($this->string);
    }

    /**
     * @ignore
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @ignore
     */
    public function valid(): bool
    {
        return $this->position < $this->count();
    }

    /**
     * @ignore
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @throws HelperException
     * @ignore
     */
    public function current(): Char
    {
        return new Char($this->string[$this->position]);
    }

    /**
     * @ignore
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @ignore
     */
    public function offsetExists($offset): bool
    {
        return $offset < strlen($this->string);
    }

    /**
     * @throws HelperException
     * @ignore
     */
    public function offsetGet($offset): ?Char
    {
        return ($this->offsetExists($offset)) ? new Char($this->string[$offset]) : null;
    }

    /**
     * @ignore
     */
    public function offsetSet($offset, $value): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        $this->string[$offset] = strval($value);
    }

    /**
     * @ignore
     */
    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        $this->string = substr_replace($this->string, "", $offset, 1);
    }
}
