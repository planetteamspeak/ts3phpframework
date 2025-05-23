<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Viewer;

use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Channel;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\ChannelGroup;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Client;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\ServerGroup;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use stdClass;

/**
 * Class Json
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Viewer
 * @class PlanetTeamSpeak\TeamSpeak3Framework\Viewer\Json
 * @brief Generates a JSON struct used in JS-based TeamSpeak 3 viewers.
 */
class Json implements ViewerInterface
{
    /**
     * Stores an array of data parsed from PlanetTeamSpeak\TeamSpeak3Framework\Node\Node objects.
     *
     * @var array
     */
    protected array $data;

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
     * An internal counter indicating the depth of the PlanetTeamSpeak\TeamSpeak3Framework\Node\Node object previously
     * processed.
     *
     * @var integer
     */
    protected int $lastLvl = 0;

    /**
     * The Json constructor.
     *
     * @param array $data
     * @return Json
     */
    public function __construct(array &$data = [])
    {
        $this->data = &$data;
        return $this;
    }

    /**
     * Assembles an stdClass object for the current element.
     *
     * @param Node $node
     * @param array $siblings
     * @return string
     */
    public function fetchObject(Node $node, array $siblings = []): string
    {
        $this->currObj = $node;
        $this->currSib = $siblings;

        $obj = new stdClass();

        $obj->ident = $this->getId();
        $obj->parent = $this->getParent();
        $obj->children = $node->count();
        $obj->level = $this->getLevel();
        $obj->first = $obj->level != $this->lastLvl;
        $obj->last = (bool)array_pop($siblings);
        $obj->siblings = array_map("boolval", $siblings);
        $obj->class = $this->getType();
        $obj->name = $this->getName();
        $obj->image = $this->getImage();
        $obj->props = $this->getProps();

        $this->data[] = $obj;
        $this->lastLvl = $obj->level;

        return "";
    }

    /**
     * Returns the ID of the current element.
     *
     * @return false|string
     */
    protected function getId(): bool|string
    {
        if ($this->currObj instanceof Server) {
            return "ts3_s" . $this->currObj->virtualserver_id;
        } elseif ($this->currObj instanceof Channel) {
            return "ts3_c" . $this->currObj->cid;
        } elseif ($this->currObj instanceof Client) {
            return "ts3_u" . $this->currObj->clid;
        }

        return false;
    }

    /**
     * Returns the parent ID of the current element.
     *
     * @return string
     */
    protected function getParent(): string
    {
        if ($this->currObj instanceof Channel) {
            return $this->currObj->pid ? "ts3_c" . $this->currObj->pid : "ts3_s" . $this->currObj->getParent()->getId();
        } elseif ($this->currObj instanceof Client) {
            return $this->currObj->cid ? "ts3_c" . $this->currObj->cid : "ts3_s" . $this->currObj->getParent()->getId();
        }

        return "ts3";
    }

    /**
     * Returns the level of the current element.
     *
     * @return integer
     */
    protected function getLevel(): int
    {
        if ($this->currObj instanceof Channel) {
            return $this->currObj->getLevel() + 2;
        } elseif ($this->currObj instanceof Client) {
            return $this->currObj->channelGetById($this->currObj->cid)->getLevel() + 3;
        }

        return 1;
    }

    /**
     * Returns a single type identifier for the current element.
     *
     * @return string
     */
    protected function getType(): string
    {
        if ($this->currObj instanceof Server) {
            return "server";
        } elseif ($this->currObj instanceof Channel) {
            return "channel";
        } elseif ($this->currObj instanceof Client) {
            return "client";
        } elseif ($this->currObj instanceof ServerGroup || $this->currObj instanceof ChannelGroup) {
            return "group";
        }

        return "host";
    }

    /**
     * Returns a string for the current corpus element which can be used as an HTML class
     * property. If the current node is a channel spacer the class string will contain
     * additional class names to allow further customization of the content via CSS.
     *
     * @return string
     */
    protected function getClass(): string
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
                case TeamSpeak3::SPACER_ALIGN_REPEAT:
                    $extras .= " repeat";
                    break;

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
        }

        return $this->currObj->getClass(null) . $extras;
    }

    /**
     * Returns an individual type for a spacer.
     *
     * @return string
     */
    protected function getSpacerType(): string
    {
        $type = "";

        if (!$this->currObj instanceof Channel || !$this->currObj->isSpacer()) {
            return "none";
        }

        $type .= match ($this->currObj->spacerGetType()) {
            (string)TeamSpeak3::SPACER_SOLIDLINE => "solidline",
            (string)TeamSpeak3::SPACER_DASHLINE => "dashline",
            (string)TeamSpeak3::SPACER_DASHDOTLINE => "dashdotline",
            (string)TeamSpeak3::SPACER_DASHDOTDOTLINE => "dashdotdotline",
            (string)TeamSpeak3::SPACER_DOTLINE => "dotline",
            default => "custom",
        };

        if ($type == "custom") {
            $type .= match ($this->currObj->spacerGetAlign()) {
                TeamSpeak3::SPACER_ALIGN_REPEAT => "repeat",
                TeamSpeak3::SPACER_ALIGN_CENTER => "center",
                TeamSpeak3::SPACER_ALIGN_RIGHT => "right",
                default => "left",
            };
        }

        return $type;
    }

    /**
     * Returns a string for the current corpus element which contains the display name
     * for the current TeamSpeak_Node_Abstract object.
     *
     * @return string
     */
    protected function getName(): string
    {
        if ($this->currObj instanceof Channel && $this->currObj->isSpacer()) {
            return $this->currObj["channel_name"]->section("]", 1, 99)->toString();
        } elseif ($this->currObj instanceof Client) {
            $before = [];
            $behind = [];

            foreach ($this->currObj->memberOf() as $group) {
                if ($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEFORE) {
                    $before[] = "[" . $group["name"] . "]";
                } elseif ($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEHIND) {
                    $behind[] = "[" . $group["name"] . "]";
                }
            }

            return trim(implode("", $before) . " " . $this->currObj . " " . implode("", $behind));
        }

        return $this->currObj->toString();
    }

    /**
     * Returns the parent ID of the current element.
     *
     * @return stdClass
     */
    protected function getProps(): stdClass
    {
        $props = new stdClass();

        if (is_a($this->currObj, Node::class)) {
            $this->id = 0;
            $this->icon = 0;
            $props->version = $this->currObj->version("version")->toString();
            $props->platform = $this->currObj->version("platform")->toString();
            $props->users = $this->currObj->virtualservers_total_clients_online;
            $props->slots = $this->currObj->virtualservers_total_maxclients;
            $props->flags = 0;
        } elseif (is_a($this->currObj, Server::class)) {
            $props->id = $this->currObj->getId();
            $props->icon = ($this->currObj->virtualserver_icon_id < 0) ? pow(2, 32) - ($this->currObj->virtualserver_icon_id * -1) : $this->currObj->virtualserver_icon_id;
            $props->welcmsg = strlen($this->currObj->virtualserver_welcomemessage) ? trim($this->currObj->virtualserver_welcomemessage) : null;
            $props->hostmsg = strlen($this->currObj->virtualserver_hostmessage) ? trim($this->currObj->virtualserver_hostmessage) : null;
            $props->version = Convert::versionShort($this->currObj->virtualserver_version)->toString();
            $props->platform = $this->currObj->virtualserver_platform->toString();
            $props->country = null;
            $props->users = $this->currObj->clientCount();
            $props->slots = $this->currObj->virtualserver_maxclients;
            $props->flags = 0;

            $props->flags += $this->currObj->virtualserver_status === "online" ? 1 : 0;
            $props->flags += $this->currObj->virtualserver_flag_password ? 2 : 0;
            $props->flags += $this->currObj->virtualserver_autostart ? 4 : 0;
            $props->flags += $this->currObj->virtualserver_weblist_enabled ? 8 : 0;
            $props->flags += $this->currObj->virtualserver_ask_for_privilegekey ? 16 : 0;
        } elseif (is_a($this->currObj, Channel::class)) {
            $props->id = $this->currObj->getId();
            $props->icon = 0;
            if (!$this->currObj->isSpacer()) {
                $props->icon = $this->currObj->channel_icon_id < 0 ? (2 ** 32) - ($this->currObj->channel_icon_id * -1) : $this->currObj->channel_icon_id;
            }

            $props->path = trim($this->currObj->getPathway());
            $props->topic = strlen($this->currObj->channel_topic) ? trim($this->currObj->channel_topic) : null;
            $props->codec = $this->currObj->channel_codec;
            $props->users = $this->currObj->total_clients == -1 ? 0 : $this->currObj->total_clients;
            $props->slots = $this->currObj->channel_maxclients == -1 ? $this->currObj->getParent()->virtualserver_maxclients : $this->currObj->channel_maxclients;
            $props->famusers = $this->currObj->total_clients_family == -1 ? 0 : $this->currObj->total_clients_family;
            $props->famslots = $this->currObj->channel_maxfamilyclients == -1 ? $this->currObj->getParent()->virtualserver_maxclients : $this->currObj->channel_maxfamilyclients;
            $props->spacer = $this->getSpacerType();
            $props->flags = 0;

            $props->flags += $this->currObj->channel_flag_default ? 1 : 0;
            $props->flags += $this->currObj->channel_flag_password ? 2 : 0;
            $props->flags += $this->currObj->channel_flag_permanent ? 4 : 0;
            $props->flags += $this->currObj->channel_flag_semi_permanent ? 8 : 0;
            $props->flags += ($props->codec == 3 || $props->codec == 5) ? 16 : 0;
            $props->flags += $this->currObj->channel_needed_talk_power != 0 ? 32 : 0;
            $props->flags += $this->currObj->total_clients != -1 ? 64 : 0;
            $props->flags += $this->currObj->isSpacer() ? 128 : 0;
        } elseif (is_a($this->currObj, Client::class)) {
            $props->id = $this->currObj->getId();
            $props->icon = $this->currObj->client_icon_id < 0 ? pow(2, 32) - ($this->currObj->client_icon_id * -1) : $this->currObj->client_icon_id;
            $props->version = Convert::versionShort($this->currObj->client_version)->toString();
            $props->platform = $this->currObj->client_platform->toString();
            $props->country = strlen($this->currObj->client_country) ? trim($this->currObj->client_country) : null;
            $props->awaymesg = strlen($this->currObj->client_away_message) ? trim($this->currObj->client_away_message) : null;
            $props->memberof = [];
            $props->badges = $this->currObj->getBadges();
            $props->flags = 0;

            foreach ($this->currObj->memberOf() as $num => $group) {
                $props->memberof[$num] = new stdClass();

                $props->memberof[$num]->name = trim($group->name);
                $props->memberof[$num]->icon = $group->iconid < 0 ? pow(2, 32) - ($group->iconid * -1) : $group->iconid;
                $props->memberof[$num]->order = $group->sortid;
                $props->memberof[$num]->flags = 0;

                $props->memberof[$num]->flags += $group->namemode;
                $props->memberof[$num]->flags += $group->type == 2 ? 4 : 0;
                $props->memberof[$num]->flags += $group->type == 0 ? 8 : 0;
                $props->memberof[$num]->flags += $group->savedb ? 16 : 0;
                $props->memberof[$num]->flags += $group instanceof ServerGroup ? 32 : 0;
            }

            $props->flags += $this->currObj->client_away ? 1 : 0;
            $props->flags += $this->currObj->client_is_recording ? 2 : 0;
            $props->flags += $this->currObj->client_is_channel_commander ? 4 : 0;
            $props->flags += $this->currObj->client_is_priority_speaker ? 8 : 0;
            $props->flags += $this->currObj->client_is_talker ? 16 : 0;
            $props->flags += $this->currObj->channelGetById($this->currObj->cid)->channel_needed_talk_power > $this->currObj->client_talk_power && !$this->currObj->client_is_talker ? 32 : 0;
            $props->flags += $this->currObj->client_input_muted || !$this->currObj->client_input_hardware ? 64 : 0;
            $props->flags += $this->currObj->client_output_muted || !$this->currObj->client_output_hardware ? 128 : 0;
        } elseif (is_a($this->currObj, ServerGroup::class) || is_a($this->currObj, ChannelGroup::class)) {
            $props->id = $this->currObj->getId();
            $props->icon = $this->currObj->iconid < 0 ? pow(2, 32) - ($this->currObj->iconid * -1) : $this->currObj->iconid;
            $props->order = $this->currObj->sortid;
            $props->n_map = $this->currObj->n_member_addp;
            $props->n_mrp = $this->currObj->n_member_removep;
            $props->flags = 0;

            $props->flags += $this->currObj->namemode;
            $props->flags += $this->currObj->type == 2 ? 4 : 0;
            $props->flags += $this->currObj->type == 0 ? 8 : 0;
            $props->flags += $this->currObj->savedb ? 16 : 0;
            $props->flags += $this->currObj instanceof ServerGroup ? 32 : 0;
        }

        return $props;
    }

    /**
     * Returns the status icon URL of the current element.
     *
     * @return string
     */
    protected function getImage(): string
    {
        return str_replace("_", "-", $this->currObj->getIcon());
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Returns a string representation of this node.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}
