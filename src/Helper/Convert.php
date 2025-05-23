<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper;

use DateTime;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class Convert
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper
 * @class Convert
 * @brief Helper class for data conversion.
 */
class Convert
{
    /**
     * Converts bytes to a human-readable value.
     *
     * @param integer $bytes
     * @return string
     */
    public static function bytes(int $bytes, int $precision = 10): string
    {
        // Identify if its a negative or positive number
        $negative = (str_starts_with($bytes, '-')) ? true : false;

        // force calculation with positive numbers only
        $bytes = floatval(abs($bytes));

        $unit_conversions = [
            0 => ["UNIT" => "B", "VALUE" => pow(1024, 0)],
            1 => ["UNIT" => "KiB", "VALUE" => pow(1024, 1)],
            2 => ["UNIT" => "MiB", "VALUE" => pow(1024, 2)],
            3 => ["UNIT" => "GiB", "VALUE" => pow(1024, 3)],
            4 => ["UNIT" => "TiB", "VALUE" => pow(1024, 4)],
            5 => ["UNIT" => "PiB", "VALUE" => pow(1024, 5)],
            6 => ["UNIT" => "EiB", "VALUE" => pow(1024, 6)],
            7 => ["UNIT" => "ZiB", "VALUE" => pow(1024, 7)],
            8 => ["UNIT" => "YiB", "VALUE" => pow(1024, 8)],
        ];

        // Sort from the biggest defined unit to smallest to get the best human readable format.
        krsort($unit_conversions);

        foreach ($unit_conversions as $conversion) {
            if ($bytes >= $conversion["VALUE"]) {
                $result = $bytes / $conversion["VALUE"];
                $result = strval(round($result, $precision)) . " " . $conversion["UNIT"];
                return ($negative) ? '-' . $result : $result;
            }
        }

        return ($negative) ? '-' . $bytes . " B" : $bytes . " B";
    }

    /**
     * Converts seconds/milliseconds to a human-readable value.
     *
     * Note: Assumes non-negative integer, but no validation
     * @param integer $seconds
     * @param boolean $is_ms
     * @param string $format
     * @return string
     * @todo: Handle negative integer $seconds, or invalidate
     *
     */
    public static function seconds(int $seconds, bool $is_ms = false, string $format = "%aD %H:%I:%S"): string
    {
        if ($is_ms) {
            $seconds = $seconds / 1000;
        }

        $current_datetime = new DateTime("@0");
        $seconds_datetime = new DateTime("@$seconds");

        return $current_datetime->diff($seconds_datetime)->format($format);
    }

    /**
     * Converts a given codec ID to a human-readable name.
     *
     * @param integer $codec
     * @return string
     */
    public static function codec(int $codec): string
    {
        if ($codec == TeamSpeak3::CODEC_SPEEX_NARROWBAND) {
            return "Speex Narrowband";
        }
        if ($codec == TeamSpeak3::CODEC_SPEEX_WIDEBAND) {
            return "Speex Wideband";
        }
        if ($codec == TeamSpeak3::CODEC_SPEEX_ULTRAWIDEBAND) {
            return "Speex Ultra-Wideband";
        }
        if ($codec == TeamSpeak3::CODEC_CELT_MONO) {
            return "CELT Mono";
        }
        if ($codec == TeamSpeak3::CODEC_OPUS_VOICE) {
            return "Opus Voice";
        }
        if ($codec == TeamSpeak3::CODEC_OPUS_MUSIC) {
            return "Opus Music";
        }

        return "Unknown";
    }

    /**
     * Converts a given group type ID to a human-readable name.
     *
     * @param integer $type
     * @return string
     */
    public static function groupType(int $type): string
    {
        if ($type == TeamSpeak3::GROUP_DBTYPE_TEMPLATE) {
            return "Template";
        }
        if ($type == TeamSpeak3::GROUP_DBTYPE_REGULAR) {
            return "Regular";
        }
        if ($type == TeamSpeak3::GROUP_DBTYPE_SERVERQUERY) {
            return "ServerQuery";
        }

        return "Unknown";
    }

    /**
     * Converts a given permission type ID to a human-readable name.
     *
     * @param integer $type
     * @return string
     */
    public static function permissionType(int $type): string
    {
        if ($type == TeamSpeak3::PERM_TYPE_SERVERGROUP) {
            return "Server Group";
        }
        if ($type == TeamSpeak3::PERM_TYPE_CLIENT) {
            return "Client";
        }
        if ($type == TeamSpeak3::PERM_TYPE_CHANNEL) {
            return "Channel";
        }
        if ($type == TeamSpeak3::PERM_TYPE_CHANNELGROUP) {
            return "Channel Group";
        }
        if ($type == TeamSpeak3::PERM_TYPE_CHANNELCLIENT) {
            return "Channel Client";
        }

        return "Unknown";
    }

    /**
     * Converts a given permission category value to a human-readable name.
     *
     * @param integer $pcat
     * @return string
     */
    public static function permissionCategory(int $pcat): string
    {
        if ($pcat == TeamSpeak3::PERM_CAT_GLOBAL) {
            return "Global";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GLOBAL_INFORMATION) {
            return "Global / Information";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GLOBAL_SERVER_MGMT) {
            return "Global / Virtual Server Management";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GLOBAL_ADM_ACTIONS) {
            return "Global / Administration";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GLOBAL_SETTINGS) {
            return "Global / Settings";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_SERVER) {
            return "Virtual Server";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_SERVER_INFORMATION) {
            return "Virtual Server / Information";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_SERVER_ADM_ACTIONS) {
            return "Virtual Server / Administration";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_SERVER_SETTINGS) {
            return "Virtual Server / Settings";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL) {
            return "Channel";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL_INFORMATION) {
            return "Channel / Information";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL_CREATE) {
            return "Channel / Create";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL_MODIFY) {
            return "Channel / Modify";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL_DELETE) {
            return "Channel / Delete";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CHANNEL_ACCESS) {
            return "Channel / Access";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GROUP) {
            return "Group";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GROUP_INFORMATION) {
            return "Group / Information";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GROUP_CREATE) {
            return "Group / Create";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GROUP_MODIFY) {
            return "Group / Modify";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_GROUP_DELETE) {
            return "Group / Delete";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CLIENT) {
            return "Client";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CLIENT_INFORMATION) {
            return "Client / Information";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CLIENT_ADM_ACTIONS) {
            return "Client / Admin";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CLIENT_BASICS) {
            return "Client / Basics";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_CLIENT_MODIFY) {
            return "Client / Modify";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_FILETRANSFER) {
            return "File Transfer";
        }
        if ($pcat == TeamSpeak3::PERM_CAT_NEEDED_MODIFY_POWER) {
            return "Grant";
        }

        return "Unknown";
    }

    /**
     * Converts a given log level ID to a human readable name and vice versa.
     *
     * @param mixed $level
     * @return string
     */
    public static function logLevel(mixed $level): string
    {
        if (is_numeric($level)) {
            if ($level == TeamSpeak3::LOGLEVEL_CRITICAL) {
                return "CRITICAL";
            }
            if ($level == TeamSpeak3::LOGLEVEL_ERROR) {
                return "ERROR";
            }
            if ($level == TeamSpeak3::LOGLEVEL_DEBUG) {
                return "DEBUG";
            }
            if ($level == TeamSpeak3::LOGLEVEL_WARNING) {
                return "WARNING";
            }
            if ($level == TeamSpeak3::LOGLEVEL_INFO) {
                return "INFO";
            }

            return "DEVELOP";
        } else {
            if (strtoupper($level) == "CRITICAL") {
                return TeamSpeak3::LOGLEVEL_CRITICAL;
            }
            if (strtoupper($level) == "ERROR") {
                return TeamSpeak3::LOGLEVEL_ERROR;
            }
            if (strtoupper($level) == "DEBUG") {
                return TeamSpeak3::LOGLEVEL_DEBUG;
            }
            if (strtoupper($level) == "WARNING") {
                return TeamSpeak3::LOGLEVEL_WARNING;
            }
            if (strtoupper($level) == "INFO") {
                return TeamSpeak3::LOGLEVEL_INFO;
            }

            return TeamSpeak3::LOGLEVEL_DEVEL;
        }
    }

    /**
     * Converts a specified log entry string into an array containing the data.
     *
     * @param string $entry
     * @return array
     */
    public static function logEntry(string $entry): array
    {
        $parts = explode("|", $entry, 5);
        $array = [];

        if (count($parts) != 5) {
            $array["timestamp"] = 0;
            $array["level"] = TeamSpeak3::LOGLEVEL_ERROR;
            $array["channel"] = "ParamParser";
            $array["server_id"] = "";
            $array["msg"] = StringHelper::factory("convert error (" . trim($entry) . ")");
            $array["msg_plain"] = $entry;
            $array["malformed"] = true;
        } else {
            $array["timestamp"] = strtotime(trim($parts[0]) . " UTC");
            $array["level"] = self::logLevel(trim($parts[1]));
            $array["channel"] = trim($parts[2]);
            $array["server_id"] = trim($parts[3]);
            $array["msg"] = StringHelper::factory(trim($parts[4]));
            $array["msg_plain"] = $entry;
            $array["malformed"] = false;
        }

        return $array;
    }

    /**
     * Converts a specified 32-bit unsigned integer value to a signed 32-bit integer value.
     *
     * @param integer $unsigned
     * @return integer
     */
    public static function iconId(int $unsigned): int
    {
        $signed = $unsigned;

        if (PHP_INT_SIZE > 4) { // 64-bit
            if ($signed & 0x80000000) {
                return $signed - 0x100000000;
            }
        }

        return $signed;
    }

    /**
     * Converts a given string to a ServerQuery password hash.
     *
     * @param string $plain
     * @return string
     */
    public static function password(string $plain): string
    {
        return base64_encode(sha1($plain, true));
    }

    /**
     * Returns a client-like formatted version of the TeamSpeak 3 version string.
     *
     * @param string $version
     * @param string $format
     * @return string|StringHelper
     */
    public static function version(string $version, string $format = "Y-m-d h:i:s"): string|StringHelper
    {
        if (!$version instanceof StringHelper) {
            $version = new StringHelper($version);
        }

        $buildno = $version->section("[", 1)->filterDigits()->toInt();

        return ($buildno <= 15001) ? $version : $version->section("[")->append("(" . date($format, $buildno) . ")");
    }

    /**
     * Returns a client-like short-formatted version of the TeamSpeak 3 version string.
     *
     * @param string $version
     * @return StringHelper
     */
    public static function versionShort(string $version): StringHelper
    {
        if (!$version instanceof StringHelper) {
            $version = new StringHelper($version);
        }

        return $version->section(" ");
    }

    /**
     * Tries to detect the type of image by a given string and returns it.
     *
     * @param string $binary
     * @return string
     */
    public static function imageMimeType(string $binary): string
    {
        if (!preg_match('/\A(?:(\xff\xd8\xff)|(GIF8[79]a)|(\x89PNG\x0d\x0a)|(BM)|(\x49\x49(\\x2a\x00|\x00\x4a))|(FORM.{4}ILBM))/', $binary, $matches)) {
            return "image/svg+xml";
        }

        $type = [
            1 => "image/jpeg",
            2 => "image/gif",
            3 => "image/png",
            4 => "image/x-windows-bmp",
            5 => "image/tiff",
            6 => "image/x-ilbm",
        ];

        return $type[count($matches) - 1];
    }
}
