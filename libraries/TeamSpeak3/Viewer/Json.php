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

/**
 * @class TeamSpeak3_Viewer_Json
 * @brief Generates a JSON struct used in JS-based TeamSpeak 3 viewers.
 */
class TeamSpeak3_Viewer_Json implements TeamSpeak3_Viewer_Interface
{
  /**
   * Stores an array of data parsed from TeamSpeak3_Node_Abstract objects.
   *
   * @var array
   */
  protected $data = null;

  /**
   * The TeamSpeak3_Node_Abstract object which is currently processed.
   *
   * @var TeamSpeak3_Node_Abstract
   */
  protected $currObj = null;

  /**
   * An array filled with siblings for the TeamSpeak3_Node_Abstract object which is currently
   * processed.
   *
   * @var array
   */
  protected $currSib = null;

  /**
   * An internal counter indicating the depth of the TeamSpeak3_Node_Abstract object previously
   * processed.
   *
   * @var integer
   */
  protected $lastLvl = 0;

  /**
   * The TeamSpeak3_Viewer_Json constructor.
   *
   * @param  array $data
   * @return TeamSpeak3_Viewer_Json
   */
  public function __construct(array &$data = array())
  {
    $this->data = &$data;
  }

  /**
   * Assembles an stdClass object for the current element.
   *
   * @param  TeamSpeak3_Node_Abstract $node
   * @param  array $siblings
   * @return void
   */
  public function fetchObject(TeamSpeak3_Node_Abstract $node, array $siblings = array())
  {
    $this->currObj = $node;
    $this->currSib = $siblings;

    $obj = new stdClass();

    $obj->ident    = $this->getId();
    $obj->parent   = $this->getParent();
    $obj->children = $node->count();
    $obj->level    = $this->getLevel();
    $obj->first    = (bool) ($obj->level != $this->lastLvl);
    $obj->last     = (bool) array_pop($siblings);
    $obj->siblings = array_map("boolval", $siblings);
    $obj->class    = $this->getType();
    $obj->name     = $this->getName();
    $obj->image    = $this->getImage();
    $obj->props    = $this->getProps();

    $this->data[]  = $obj;
    $this->lastLvl = $obj->level;
  }

  /**
   * Returns the ID of the current element.
   *
   * @return mixed
   */
  protected function getId()
  {
    if($this->currObj instanceof TeamSpeak3_Node_Server)
    {
      return "ts3_s" . $this->currObj->virtualserver_id;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Channel)
    {
      return "ts3_c" . $this->currObj->cid;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      return "ts3_u" . $this->currObj->clid;
    }

    return FALSE;
  }

  /**
   * Returns the parent ID of the current element.
   *
   * @return mixed
   */
  protected function getParent()
  {
    if($this->currObj instanceof TeamSpeak3_Node_Channel)
    {
      return $this->currObj->pid ? "ts3_c" . $this->currObj->pid : "ts3_s" . $this->currObj->getParent()->getId();
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      return $this->currObj->cid ? "ts3_c" . $this->currObj->cid : "ts3_s" . $this->currObj->getParent()->getId();
    }

    return "ts3";
  }

  /**
   * Returns the level of the current element.
   *
   * @return integer
   */
  protected function getLevel()
  {
    if($this->currObj instanceof TeamSpeak3_Node_Channel)
    {
      return $this->currObj->getLevel()+2;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      return $this->currObj->channelGetById($this->currObj->cid)->getLevel()+3;
    }

    return 1;
  }

  /**
   * Returns a single type identifier for the current element.
   *
   * @return string
   */
  protected function getType()
  {
    if($this->currObj instanceof TeamSpeak3_Node_Server)
    {
      return "server";
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Channel)
    {
      return "channel";
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      return "client";
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Servergroup || $this->currObj instanceof TeamSpeak3_Node_Channelgroup)
    {
      return "group";
    }

    return "host";
  }

  /**
   * Returns a string for the current corpus element which can be used as a HTML class
   * property. If the current node is a channel spacer the class string will contain
   * additional class names to allow further customization of the content via CSS.
   *
   * @return string
   */
  protected function getClass()
  {
    $extras = "";

    if($this->currObj instanceof TeamSpeak3_Node_Channel && $this->currObj->isSpacer())
    {
      switch($this->currObj->spacerGetType())
      {
        case (string) TeamSpeak3::SPACER_SOLIDLINE:
          $extras .= " solidline";
          break;

        case (string) TeamSpeak3::SPACER_DASHLINE:
          $extras .= " dashline";
          break;

        case (string) TeamSpeak3::SPACER_DASHDOTLINE:
          $extras .= " dashdotline";
          break;

        case (string) TeamSpeak3::SPACER_DASHDOTDOTLINE:
          $extras .= " dashdotdotline";
          break;

        case (string) TeamSpeak3::SPACER_DOTLINE:
          $extras .= " dotline";
          break;
      }

      switch($this->currObj->spacerGetAlign())
      {
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
  protected function getSpacerType()
  {
    $type = "";

    if(!$this->currObj instanceof TeamSpeak3_Node_Channel || !$this->currObj->isSpacer())
    {
      return "none";
    }

    switch($this->currObj->spacerGetType())
    {
      case (string) TeamSpeak3::SPACER_SOLIDLINE:
        $type .= "solidline";
        break;

      case (string) TeamSpeak3::SPACER_DASHLINE:
        $type .= "dashline";
        break;

      case (string) TeamSpeak3::SPACER_DASHDOTLINE:
        $type .= "dashdotline";
        break;

      case (string) TeamSpeak3::SPACER_DASHDOTDOTLINE:
        $type .= "dashdotdotline";
        break;

      case (string) TeamSpeak3::SPACER_DOTLINE:
        $type .= "dotline";
        break;

      default:
        $type .= "custom";
    }

    if($type == "custom")
    {
      switch($this->currObj->spacerGetAlign())
      {
        case TeamSpeak3::SPACER_ALIGN_REPEAT:
          $type .= "repeat";
          break;

        case TeamSpeak3::SPACER_ALIGN_CENTER:
          $type .= "center";
          break;

        case TeamSpeak3::SPACER_ALIGN_RIGHT:
          $type .= "right";
          break;

        default:
          $type .= "left";
      }
    }

    return $type;
  }

  /**
   * Returns a string for the current corpus element which contains the display name
   * for the current TeamSpeak_Node_Abstract object.
   *
   * @return string
   */
  protected function getName()
  {
    if($this->currObj instanceof TeamSpeak3_Node_Channel && $this->currObj->isSpacer())
    {
      return $this->currObj["channel_name"]->section("]", 1, 99)->toString();
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      $before = array();
      $behind = array();

      foreach($this->currObj->memberOf() as $group)
      {
        if($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEFORE)
        {
          $before[] = "[" . $group["name"] . "]";
        }
        elseif($group->getProperty("namemode") == TeamSpeak3::GROUP_NAMEMODE_BEHIND)
        {
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
  protected function getProps()
  {
    $props = new stdClass();

    if($this->currObj instanceof TeamSpeak3_Node_Host)
    {
      $this->id        = 0;
      $this->icon      = 0;
      $props->version  = $this->currObj->version("version")->toString();
      $props->platform = $this->currObj->version("platform")->toString();
      $props->users    = $this->currObj->virtualservers_total_clients_online;
      $props->slots    = $this->currObj->virtualservers_total_maxclients;
      $props->flags    = 0;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Server)
    {
      $props->id       = $this->currObj->getId();
      $props->icon     = $this->currObj->virtualserver_icon_id < 0 ? pow(2, 32)-($this->currObj->virtualserver_icon_id*-1) : $this->currObj->virtualserver_icon_id;
      $props->welcmsg  = strlen($this->currObj->virtualserver_welcomemessage) ? trim($this->currObj->virtualserver_welcomemessage) : null;
      $props->hostmsg  = strlen($this->currObj->virtualserver_hostmessage) ? trim($this->currObj->virtualserver_hostmessage) : null;
      $props->version  = TeamSpeak3_Helper_Convert::versionShort($this->currObj->virtualserver_version)->toString();
      $props->platform = $this->currObj->virtualserver_platform->toString();
      $props->country  = null;
      $props->users    = $this->currObj->clientCount();
      $props->slots    = $this->currObj->virtualserver_maxclients;
      $props->flags    = 0;

      $props->flags += $this->currObj->virtualserver_status == "online"   ? 1  : 0;
      $props->flags += $this->currObj->virtualserver_flag_password        ? 2  : 0;
      $props->flags += $this->currObj->virtualserver_autostart            ? 4  : 0;
      $props->flags += $this->currObj->virtualserver_weblist_enabled      ? 8  : 0;
      $props->flags += $this->currObj->virtualserver_ask_for_privilegekey ? 16 : 0;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Channel)
    {
      $props->id       = $this->currObj->getId();
      $props->icon     = $this->currObj->isSpacer() ? 0 : $this->currObj->channel_icon_id < 0 ? pow(2, 32)-($this->currObj->channel_icon_id*-1) : $this->currObj->channel_icon_id;
      $props->path     = trim($this->currObj->getPathway());
      $props->topic    = strlen($this->currObj->channel_topic) ? trim($this->currObj->channel_topic) : null;
      $props->codec    = $this->currObj->channel_codec;
      $props->users    = $this->currObj->total_clients == -1 ? 0 : $this->currObj->total_clients;
      $props->slots    = $this->currObj->channel_maxclients == -1 ? $this->currObj->getParent()->virtualserver_maxclients : $this->currObj->channel_maxclients;
      $props->famusers = $this->currObj->total_clients_family == -1 ? 0 : $this->currObj->total_clients_family;
      $props->famslots = $this->currObj->channel_maxfamilyclients == -1 ? $this->currObj->getParent()->virtualserver_maxclients : $this->currObj->channel_maxfamilyclients;
      $props->spacer   = $this->getSpacerType();
      $props->flags    = 0;

      $props->flags += $this->currObj->channel_flag_default           ? 1   : 0;
      $props->flags += $this->currObj->channel_flag_password          ? 2   : 0;
      $props->flags += $this->currObj->channel_flag_permanent         ? 4   : 0;
      $props->flags += $this->currObj->channel_flag_semi_permanent    ? 8   : 0;
      $props->flags += ($props->codec == 3 || $props->codec == 5)     ? 16  : 0;
      $props->flags += $this->currObj->channel_needed_talk_power != 0 ? 32  : 0;
      $props->flags += $this->currObj->total_clients != -1            ? 64  : 0;
      $props->flags += $this->currObj->isSpacer()                     ? 128 : 0;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Client)
    {
      $props->id       = $this->currObj->getId();
      $props->icon     = $this->currObj->client_icon_id < 0 ? pow(2, 32)-($this->currObj->client_icon_id*-1) : $this->currObj->client_icon_id;
      $props->version  = TeamSpeak3_Helper_Convert::versionShort($this->currObj->client_version)->toString();
      $props->platform = $this->currObj->client_platform->toString();
      $props->country  = strlen($this->currObj->client_country) ? trim($this->currObj->client_country) : null;
      $props->awaymesg = strlen($this->currObj->client_away_message) ? trim($this->currObj->client_away_message) : null;
      $props->memberof = array();
      $props->badges   = $this->currObj->getBadges();
      $props->flags    = 0;

      foreach($this->currObj->memberOf() as $num => $group)
      {
        $props->memberof[$num] = new stdClass();

        $props->memberof[$num]->name  = trim($group->name);
        $props->memberof[$num]->icon  = $group->iconid < 0 ? pow(2, 32)-($group->iconid*-1) : $group->iconid;
        $props->memberof[$num]->order = $group->sortid;
        $props->memberof[$num]->flags = 0;

        $props->memberof[$num]->flags += $group->namemode;
        $props->memberof[$num]->flags += $group->type == 2                             ? 4  : 0;
        $props->memberof[$num]->flags += $group->type == 0                             ? 8  : 0;
        $props->memberof[$num]->flags += $group->savedb                                ? 16 : 0;
        $props->memberof[$num]->flags += $group instanceof TeamSpeak3_Node_Servergroup ? 32 : 0;
      }

      $props->flags += $this->currObj->client_away                                                                                                                             ? 1   : 0;
      $props->flags += $this->currObj->client_is_recording                                                                                                                     ? 2   : 0;
      $props->flags += $this->currObj->client_is_channel_commander                                                                                                             ? 4   : 0;
      $props->flags += $this->currObj->client_is_priority_speaker                                                                                                              ? 8   : 0;
      $props->flags += $this->currObj->client_is_talker                                                                                                                        ? 16  : 0;
      $props->flags += $this->currObj->channelGetById($this->currObj->cid)->channel_needed_talk_power > $this->currObj->client_talk_power && !$this->currObj->client_is_talker ? 32  : 0;
      $props->flags += $this->currObj->client_input_muted || !$this->currObj->client_input_hardware                                                                            ? 64  : 0;
      $props->flags += $this->currObj->client_output_muted || !$this->currObj->client_output_hardware                                                                          ? 128 : 0;
    }
    elseif($this->currObj instanceof TeamSpeak3_Node_Servergroup || $this->currObj instanceof TeamSpeak3_Node_Channelgroup)
    {
      $props->id     = $this->currObj->getId();
      $props->icon   = $this->currObj->iconid < 0 ? pow(2, 32)-($this->currObj->iconid*-1) : $this->currObj->iconid;
      $props->order  = $this->currObj->sortid;
      $props->n_map  = $this->currObj->n_member_addp;
      $props->n_mrp  = $this->currObj->n_member_removep;
      $props->flags  = 0;

      $props->flags += $this->currObj->namemode;
      $props->flags += $this->currObj->type == 2                             ? 4  : 0;
      $props->flags += $this->currObj->type == 0                             ? 8  : 0;
      $props->flags += $this->currObj->savedb                                ? 16 : 0;
      $props->flags += $this->currObj instanceof TeamSpeak3_Node_Servergroup ? 32 : 0;
    }

    return $props;
  }

  /**
   * Returns the status icon URL of the current element.
   *
   * @return string
   */
  protected function getImage()
  {
    return str_replace("_", "-", $this->currObj->getIcon());
  }

  /**
   * Returns a string representation of this node.
   *
   * @return string
   */
  public function toString()
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
