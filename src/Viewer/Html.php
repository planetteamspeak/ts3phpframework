<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Viewer;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\ChannelGroup;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Client;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\ServerGroup;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Class Html
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Viewer
 * @class Html
 * @brief Renders nodes used in HTML-based TeamSpeak 3 viewers.
 */
class Html implements ViewerInterface
{
    /**
     * A pre-defined pattern used to display a node in a TeamSpeak 3 viewer.
     *
     * @var string
     */
    protected string $pattern = "<table id='%0' class='%1' summary='%2'><tr class='%3'><td class='%4'>%5</td><td class='%6' title='%7'>%8 %9</td><td class='%10'>%11%12</td></tr></table>\n";

    /**
     * The PlanetTeamSpeak\TeamSpeak3Framework\Node\Node object which is currently processed.
     *
     * @var Node|null
     */
    protected ?Node $currObj = null;

    /**
     * An array filled with siblings for the PlanetTeamSpeak\TeamSpeak3Framework\Node\Node object which is currently
     * processed.
     *
     * @var array|null
     */
    protected ?array $currSib = null;

    /**
     * An internal counter indicating the number of fetched PlanetTeamSpeak\TeamSpeak3Framework\Node\Node objects.
     *
     * @var integer
     */
    protected int $currNum = 0;

    /**
     * The relative URI path where the images used by the viewer can be found.
     *
     * @var string
     */
    protected string $iconpath;

    /**
     * The relative URI path where the country flag icons used by the viewer can be found.
     *
     * @var string|null
     */
    protected ?string $flagpath;

    /**
     * The relative path of the file transter client script on the server.
     *
     * @var string|null
     */
    protected ?string $ftclient;

    /**
     * Stores an array of local icon IDs.
     *
     * @var array
     */
    protected array $cachedIcons = [100, 200, 300, 400, 500, 600];

    /**
     * Stores an array of remote icon IDs.
     *
     * @var array
     */
    protected array $remoteIcons = [];

    /**
     * Html constructor.
     *
     * @param string $iconpath
     * @param string|null $flagpath
     * @param string|null $ftclient
     * @param string|null $pattern
     * @return void
     */
    public function __construct(string $iconpath = "images/viewer/", string $flagpath = null, string $ftclient = null, string $pattern = null)
    {
        $this->iconpath = $iconpath;
        $this->flagpath = $flagpath;
        $this->ftclient = $ftclient;

        if ($pattern) {
            $this->pattern = $pattern;
        }
    }

    /**
     * Returns the code needed to display a node in a TeamSpeak 3 viewer.
     *
     * @param Node $node
     * @param array $siblings
     * @return string
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    public function fetchObject(Node $node, array $siblings = []): string
    {
        $this->currObj = $node;
        $this->currSib = $siblings;

        $args = [
            $this->getContainerIdent(),
            $this->getContainerClass(),
            $this->getContainerSummary(),
            $this->getRowClass(),
            $this->getPrefixClass(),
            $this->getPrefix(),
            $this->getCorpusClass(),
            $this->getCorpusTitle(),
            $this->getCorpusIcon(),
            $this->getCorpusName(),
            $this->getSuffixClass(),
            $this->getSuffixIcon(),
            $this->getSuffixFlag(),
        ];

        return StringHelper::factory($this->pattern)->arg($args);
    }

    /**
     * Returns a unique identifier for the current node which can be used as an HTML id
     * property.
     *
     * @return string
     */
    protected function getContainerIdent(): string
    {
        return $this->currObj->getUniqueId();
    }

    /**
     * Returns a dynamic string for the current container element which can be used as
     * an HTML class property.
     *
     * @return string
     */
    protected function getContainerClass(): string
    {
        return "ts3_viewer " . $this->currObj->getClass(null);
    }

    /**
     * Returns the ID of the current node which will be used as a summary element for
     * the container element.
     *
     * @return integer
     */
    protected function getContainerSummary(): int
    {
        return $this->currObj->getId();
    }

    /**
     * Returns a dynamic string for the current row element which can be used as an HTML
     * class property.
     *
     * @return string
     */
    protected function getRowClass(): string
    {
        return ++$this->currNum % 2 ? "row1" : "row2";
    }

    /**
     * Returns a string for the current prefix element which can be used as an HTML class
     * property.
     *
     * @return string
     */
    protected function getPrefixClass(): string
    {
        return "prefix " . $this->currObj->getClass(null);
    }

    /**
     * Returns the HTML img tags to display the prefix of the current node.
     *
     * @return string
     */
    protected function getPrefix(): string
    {
        $prefix = "";

        if (count($this->currSib)) {
            $last = array_pop($this->currSib);

            foreach ($this->currSib as $sibling) {
                $prefix .= ($sibling) ? $this->getImage("tree_line.gif") : $this->getImage("tree_blank.png");
            }

            $prefix .= ($last) ? $this->getImage("tree_end.gif") : $this->getImage("tree_mid.gif");
        }

        return $prefix;
    }

    /**
     * Returns a string for the current corpus element which can be used as an HTML class
     * property. If the current node is a channel spacer the class string will contain
     * additional class names to allow further customization of the content via CSS.
     *
     * @return string
     */
    protected function getCorpusClass(): string
    {
        $extras = "";

        if ($this->currObj instanceof Channel && $this->currObj->isSpacer()) {
            switch ($this->currObj->spacerGetType()) {
                case (string)TeamSpeak3::SPACER_SOLIDLINE:
                    $extras .= " solidline";
                    break;

                case (string)TeamSpeak3::SPACER_DASHLINE:
                    $extras .= " dashline";
                    break;

                case (string)TeamSpeak3::SPACER_DASHDOTLINE:
                    $extras .= " dashdotline";
                    break;

                case (string)TeamSpeak3::SPACER_DASHDOTDOTLINE:
                    $extras .= " dashdotdotline";
                    break;

                case (string)TeamSpeak3::SPACER_DOTLINE:
                    $extras .= " dotline";
                    break;
            }

            switch ($this->currObj->spacerGetAlign()) {
                case TeamSpeak3::SPACER_ALIGN_CENTER:
                    $extras .= " center";
                    break;

                case TeamSpeak3::SPACER_ALIGN_RIGHT:
                    $extras .= " right";
                    break;

                case TeamSpeak3::SPACER_ALIGN_LEFT:
                    $extras .= " left";
                    break;
            }
        } elseif ($this->currObj instanceof Client && $this->currObj->client_is_recording) {
            $extras .= " recording";
        }

        return "corpus " . $this->currObj->getClass(null) . $extras;
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Abstract object.
     *
     * @return string|null
     */
    protected function getCorpusTitle(): ?string
    {
        if ($this->currObj instanceof Server) {
            return "ID: " . $this->currObj->getId() . " | Clients: " . $this->currObj->clientCount() . "/" . $this->currObj["virtualserver_maxclients"] . " | Uptime: " . Convert::seconds($this->currObj["virtualserver_uptime"]);
        } elseif ($this->currObj instanceof Channel && !$this->currObj->isSpacer()) {
            return "ID: " . $this->currObj->getId() . " | Codec: " . Convert::codec($this->currObj["channel_codec"]) . " | Quality: " . $this->currObj["channel_codec_quality"];
        } elseif ($this->currObj instanceof Client) {
            return "ID: " . $this->currObj->getId() . " | Version: " . Convert::versionShort($this->currObj["client_version"]) . " | Platform: " . $this->currObj["client_platform"];
        } elseif ($this->currObj instanceof ServerGroup || $this->currObj instanceof ChannelGroup) {
            return "ID: " . $this->currObj->getId() . " | Type: " . Convert::groupType($this->currObj["type"]) . " (" . ($this->currObj["savedb"] ? "Permanent" : "Temporary") . ")";
        }
        return null;
    }

    /**
     * Returns an HTML img tag which can be used to display the status icon for a
     * TeamSpeak_Node_Abstract object.
     *
     * @return string
     */
    protected function getCorpusIcon(): string
    {
        if ($this->currObj instanceof Channel && $this->currObj->isSpacer()) {
            return "";
        }

        return $this->getImage($this->currObj->getIcon() . ".png");
    }

    /**
     * Returns a string for the current corpus element which contains the display name
     * for the current TeamSpeak_Node_Abstract object.
     *
     * @return string
     */
    protected function getCorpusName(): string
    {
        if ($this->currObj instanceof Channel && $this->currObj->isSpacer()) {
            if ($this->currObj->spacerGetType() != TeamSpeak3::SPACER_CUSTOM) {
                return "";
            }

            $string = $this->currObj["channel_name"]->section("]", 1, 99);

            if ($this->currObj->spacerGetAlign() == TeamSpeak3::SPACER_ALIGN_REPEAT) {
                $string->resize(30, $string);
            }

            return htmlspecialchars($string);
        }

        if ($this->currObj instanceof Client) {
            $before = [];
            $behind = [];

            if (!$this->currObj->client_is_recording) {
                foreach ($this->currObj->memberOf() as $group) {
                    if ($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEFORE) {
                        $before[] = "[" . htmlspecialchars($group["name"]) . "]";
                    } elseif ($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEHIND) {
                        $behind[] = "[" . htmlspecialchars($group["name"]) . "]";
                    }
                }
            } else {
                $before[] = "***";
                $behind[] = "*** [RECORDING]";
            }

            return implode("", $before) . " " . htmlspecialchars($this->currObj) . " " . implode("", $behind);
        }

        return htmlspecialchars($this->currObj);
    }

    /**
     * Returns a string for the current suffix element which can be used as an HTML
     * class property.
     *
     * @return string
     */
    protected function getSuffixClass(): string
    {
        return "suffix " . $this->currObj->getClass(null);
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Abstract object.
     *
     * @return string
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    protected function getSuffixIcon(): string
    {
        if ($this->currObj instanceof Server) {
            return $this->getSuffixIconServer();
        } elseif ($this->currObj instanceof Channel) {
            return $this->getSuffixIconChannel();
        } elseif ($this->currObj instanceof Client) {
            return $this->getSuffixIconClient();
        }
        return "";
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Server object.
     *
     * @return string
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    protected function getSuffixIconServer(): string
    {
        $html = "";

        if ($this->currObj["virtualserver_icon_id"]) {
            if (!$this->currObj->iconIsLocal("virtualserver_icon_id") && $this->ftclient) {
                if (!isset($this->cacheIcon[$this->currObj["virtualserver_icon_id"]])) {
                    $download = $this->currObj->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("virtualserver_icon_id"));

                    if ($this->ftclient == "data:image") {
                        $download = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["virtualserver_icon_id"]] = $download;
                } else {
                    $download = $this->cacheIcon[$this->currObj["virtualserver_icon_id"]];
                }

                if ($this->ftclient == "data:image") {
                    $html .= $this->getImage("data:" . Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Server Icon", null, false);
                } else {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Server Icon", null, false);
                }
            } elseif (in_array($this->currObj["virtualserver_icon_id"], $this->cachedIcons)) {
                $html .= $this->getImage("group_icon_" . $this->currObj["virtualserver_icon_id"] . ".png", "Server Icon");
            }
        }

        return $html;
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Channel object.
     *
     * @return string
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    protected function getSuffixIconChannel(): string
    {
        if ($this->currObj instanceof Channel && $this->currObj->isSpacer()) {
            return "";
        }

        $html = "";

        if ($this->currObj["channel_flag_default"]) {
            $html .= $this->getImage("channel_flag_default.png", "Default Channel");
        }

        if ($this->currObj["channel_flag_password"]) {
            $html .= $this->getImage("channel_flag_password.png", "Password-protected");
        }

        if ($this->currObj["channel_codec"] == TeamSpeak3::CODEC_CELT_MONO || $this->currObj["channel_codec"] == TeamSpeak3::CODEC_OPUS_MUSIC) {
            $html .= $this->getImage("channel_flag_music.png", "Music Codec");
        }

        if ($this->currObj["channel_needed_talk_power"]) {
            $html .= $this->getImage("channel_flag_moderated.png", "Moderated");
        }

        if ($this->currObj["channel_icon_id"]) {
            if (!$this->currObj->iconIsLocal("channel_icon_id") && $this->ftclient) {
                if (!isset($this->cacheIcon[$this->currObj["channel_icon_id"]])) {
                    $download = $this->currObj->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("channel_icon_id"));

                    if ($this->ftclient == "data:image") {
                        $download = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["channel_icon_id"]] = $download;
                } else {
                    $download = $this->cacheIcon[$this->currObj["channel_icon_id"]];
                }

                if ($this->ftclient == "data:image") {
                    $html .= $this->getImage("data:" . Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Channel Icon", null, false);
                } else {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Channel Icon", null, false);
                }
            } elseif (in_array($this->currObj["channel_icon_id"], $this->cachedIcons)) {
                $html .= $this->getImage("group_icon_" . $this->currObj["channel_icon_id"] . ".png", "Channel Icon");
            }
        }

        return $html;
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Client object.
     *
     * @return string
     * @throws AdapterException
     * @throws HelperException
     * @throws ServerQueryException
     */
    protected function getSuffixIconClient(): string
    {
        $html = "";

        if ($this->currObj["client_is_priority_speaker"]) {
            $html .= $this->getImage("client_priority.png", "Priority Speaker");
        }

        if ($this->currObj["client_is_channel_commander"]) {
            $html .= $this->getImage("client_cc.png", "Channel Commander");
        }

        if ($this->currObj["client_is_talker"]) {
            $html .= $this->getImage("client_talker.png", "Talk Power granted");
        } elseif ($cntp = $this->currObj->getParent()->channelGetById($this->currObj["cid"])->channel_needed_talk_power) {
            if ($cntp > $this->currObj["client_talk_power"]) {
                $html .= $this->getImage("client_mic_muted.png", "Insufficient Talk Power");
            }
        }

        foreach ($this->currObj->memberOf() as $group) {
            if (!$group["iconid"]) {
                continue;
            }

            $type = ($group instanceof ServerGroup) ? "Server Group" : "Channel Group";

            if (!$group->iconIsLocal("iconid") && $this->ftclient) {
                if (!isset($this->cacheIcon[$group["iconid"]])) {
                    $download = $group->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $group->iconGetName("iconid"));

                    if ($this->ftclient == "data:image") {
                        $download = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$group["iconid"]] = $download;
                } else {
                    $download = $this->cacheIcon[$group["iconid"]];
                }

                if ($this->ftclient == "data:image") {
                    $html .= $this->getImage("data:" . Convert::imageMimeType($download) . ";base64," . base64_encode($download), $group . " [" . $type . "]", null, false);
                } else {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), $group . " [" . $type . "]", null, false);
                }
            } elseif (in_array($group["iconid"], $this->cachedIcons)) {
                $html .= $this->getImage("group_icon_" . $group["iconid"] . ".png", $group . " [" . $type . "]");
            }
        }

        if ($this->currObj["client_icon_id"]) {
            if (!$this->currObj->iconIsLocal("client_icon_id") && $this->ftclient) {
                if (!isset($this->cacheIcon[$this->currObj["client_icon_id"]])) {
                    $download = $this->currObj->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("client_icon_id"));

                    if ($this->ftclient == "data:image") {
                        $download = TeamSpeak3::factory("filetransfer://" . (str_contains($download["host"], ":") ? "[" . $download["host"] . "]" : $download["host"]) . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["client_icon_id"]] = $download;
                } else {
                    $download = $this->cacheIcon[$this->currObj["client_icon_id"]];
                }

                if ($this->ftclient == "data:image") {
                    $html .= $this->getImage("data:" . Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Client Icon", null, false);
                } else {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Client Icon", null, false);
                }
            } elseif (in_array($this->currObj["client_icon_id"], $this->cachedIcons)) {
                $html .= $this->getImage("group_icon_" . $this->currObj["client_icon_id"] . ".png", "Client Icon");
            }
        }

        return $html;
    }

    /**
     * Returns an HTML img tag which can be used to display the country flag for a
     * TeamSpeak_Node_Client object.
     *
     * @return string
     */
    protected function getSuffixFlag(): string
    {
        if (!$this->currObj instanceof Client) {
            return "";
        }

        if ($this->flagpath && $this->currObj["client_country"]) {
            return $this->getImage($this->currObj["client_country"]->toLower() . ".png", $this->currObj["client_country"], null, false, true);
        }

        return "";
    }

    /**
     * Returns the code to display a custom HTML img tag.
     *
     * @param string $name
     * @param string $text
     * @param string|null $class
     * @param boolean $iconpath
     * @param boolean $flagpath
     * @return string
     */
    protected function getImage(string $name, string $text = "", string $class = null, bool $iconpath = true, bool $flagpath = false): string
    {
        $src = "";

        if ($iconpath) {
            $src = $this->iconpath;
        }

        if ($flagpath) {
            $src = $this->flagpath;
        }

        return "<img src='" . $src . $name . "' title='" . $text . "' alt='' align='top' />";
    }
}
