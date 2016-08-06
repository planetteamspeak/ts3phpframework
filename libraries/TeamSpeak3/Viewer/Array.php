<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework
 *
 * $Id: Text.php 06/06/2016 22:27:13 scp@Svens-iMac $
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
 * @version   1.1.24
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/**
 * @class TeamSpeak3_Viewer_Json
 * @brief Returns nodes as array.
 */
class TeamSpeak3_Viewer_Array implements \TeamSpeak3_Viewer_Interface
{
    /**
     * @var string
     */
    protected $iconpath = null;

    /**
     * @var string
     */
    protected $flagpath = null;

    /**
     * @var string
     */
    protected $ftclient = null;

    /**
     * @var array
     */
    protected $cachedIcons = [100, 200, 300, 400, 500, 600];

    /**
     * @var array
     */
    protected $remoteIcons = [];

    /**
     * The \TeamSpeak3_Viewer_Array constructor.
     *
     * @param  string $iconpath
     * @param  string $flagpath
     * @param  string $ftclient
     */
    public function __construct(string $iconpath = 'images/viewer/', string $flagpath = null, string $ftclient = null)
    {
        $this->iconpath = $iconpath;
        $this->flagpath = $flagpath;
        $this->ftclient = $ftclient;
    }

    /**
     * @param  \TeamSpeak3_Node_Abstract $node
     * @param  array $siblings
     *
     * @return array
     */
    public function fetchObject(\TeamSpeak3_Node_Abstract $node, array $siblings = []) : array
    {
        if ($node instanceof \TeamSpeak3_Node_Channel && $node->isSpacer()) {
            return null;
        }

        $data = [
            'id' => $node->getId(),
            'node' => strtolower(str_replace('\TeamSpeak3_Node_', '', get_class($node))),
            'name' => (string) $node,
            'icon' => $this->getImage($node->getIcon() . '.png'),
        ];

        if ($node instanceof \TeamSpeak3_Node_Server) {
            $data['maxclients'] = $node->virtualserver_maxclients;
            $data['uptime'] = $node->virtualserver_uptime;
            $data['sfx_icon'] = $this->getSuffixIconServer($node);
        } elseif ($node instanceof \TeamSpeak3_Node_Channel) {
            $data['codec'] = \TeamSpeak3_Helper_Convert::codec($node->channel_codec);
            $data['quality'] = $node->channel_codec_quality;
            $data['sfx_icon'] = $this->getSuffixIconChannel($node);
        } elseif ($node instanceof \TeamSpeak3_Node_Client) {
            $data['version'] = (string) \TeamSpeak3_Helper_Convert::versionShort($node->client_version);
            $data['platform'] = (string) $node->client_platform;
            $data['sfx_icon'] = $this->getSuffixIconClient($node);
        } elseif ($node instanceof \TeamSpeak3_Node_Servergroup || $node instanceof \TeamSpeak3_Node_Channelgroup) {
            $data['type'] = \TeamSpeak3_Helper_Convert::groupType($node->type);
            $data['permanent'] = $node->savedb ? 'Permanent' : 'Temporary';
        }
        
        if ($node instanceof \TeamSpeak3_Node_Client && $this->flagpath && $node->client_country) {
            $data ['flag'] = $this->getImage($node->client_country->toLower() . '.png', $node->client_country, false, true);
        }
        
        return $data;
    }

    /**
     * @param $node
     *
     * @return array
     */
    protected function getSuffixIconServer($node) : array
    {
        $icons = [];

        if ($node['virtualserver_icon_id']) {
            if (!$node->iconIsLocal('virtualserver_icon_id') && $this->ftclient) {
                if (!isset($this->cachedIcons[$node['virtualserver_icon_id']])) {
                    $download = $node->transferInitDownload(rand(0x0000, 0xFFFF), 0, $node->iconGetName('virtualserver_icon_id'));

                    if ($this->ftclient == 'data:image') {
                        $download = \TeamSpeak3::factory('filetransfer://' . (strstr($download['host'], ':') !== false ? '[' . $download['host'] . ']' : $download['host']) . ':' . $download['port'])->download($download['ftkey'], $download['size']);
                    }

                    $this->cachedIcons[$node['virtualserver_icon_id']] = $download;
                } else {
                    $download = $this->cachedIcons[$node['virtualserver_icon_id']];
                }

                if ($this->ftclient == 'data:image') {
                    $icons[] = $this->getImage('data:' . \TeamSpeak3_Helper_Convert::imageMimeType($download) . ';base64,' . base64_encode($download), 'Server Icon', false);
                } else {
                    $icons[] = $this->getImage($this->ftclient . '?ftdata=' . base64_encode(serialize($download)), 'Server Icon', false);
                }
            } elseif (in_array($node['virtualserver_icon_id'], $this->cachedIcons)) {
                $icons[] = $this->getImage('group_icon_' . $node['virtualserver_icon_id'] . '.png', 'Server Icon');
            }
        }

        return $icons;
    }

    /**
     * @param $node
     *
     * @return array
     */
    protected function getSuffixIconChannel($node) : array
    {
        if ($node instanceof \TeamSpeak3_Node_Channel && $node->isSpacer()) return [];

        $icons = [];

        if ($node['channel_flag_default']) {
            $icons[] = $this->getImage('channel_flag_default.png', 'Default Channel');
        }

        if ($node['channel_flag_password']) {
            $icons[] = $this->getImage('channel_flag_password.png', 'Password-protected');
        }

        if ($node['channel_codec'] == \TeamSpeak3::CODEC_CELT_MONO || $node['channel_codec'] == \TeamSpeak3::CODEC_OPUS_MUSIC) {
            $icons[] = $this->getImage('channel_flag_music.png', 'Music Codec');
        }

        if ($node['channel_needed_talk_power']) {
            $icons[] = $this->getImage('channel_flag_moderated.png', 'Moderated');
        }

        if ($node['channel_icon_id']) {
            if (!$node->iconIsLocal('channel_icon_id') && $this->ftclient) {
                if (!isset($this->cachedIcons[$node['channel_icon_id']])) {
                    $download = $node->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $node->iconGetName('channel_icon_id'));

                    if ($this->ftclient == 'data:image') {
                        $download = \TeamSpeak3::factory('filetransfer://' . (strstr($download['host'], ':') !== false ? '[' . $download['host'] . ']' : $download['host']) . ':' . $download['port'])->download($download['ftkey'], $download['size']);
                    }

                    $this->cachedIcons[$node['channel_icon_id']] = $download;
                } else {
                    $download = $this->cachedIcons[$node['channel_icon_id']];
                }

                if ($this->ftclient == 'data:image') {
                    $icons[] = $this->getImage('data:' . \TeamSpeak3_Helper_Convert::imageMimeType($download) . ';base64,' . base64_encode($download), 'Channel Icon', false);
                } else {
                    $icons[] = $this->getImage($this->ftclient . '?ftdata=' . base64_encode(serialize($download)), 'Channel Icon', false);
                }
            } elseif (in_array($node['channel_icon_id'], $this->cachedIcons)) {
                $icons[] = $this->getImage('group_icon_' . $node['channel_icon_id'] . '.png', 'Channel Icon');
            }
        }

        return $icons;
    }

    /**
     * @param $node
     *
     * @return array
     */
    protected function getSuffixIconClient($node) : array
    {
        $icons = [];

        if ($node['client_is_priority_speaker']) {
            $icons[] = $this->getImage('client_priority.png', 'Priority Speaker');
        }

        if ($node['client_is_channel_commander']) {
            $icons[] = $this->getImage('client_cc.png', 'Channel Commander');
        }

        if ($node['client_is_talker']) {
            $icons[] = $this->getImage('client_talker.png', 'Talk Power granted');
        } elseif ($cntp = $node->getParent()->channelGetById($node['cid'])->channel_needed_talk_power) {
            if ($cntp > $node['client_talk_power']) {
                $icons[] = $this->getImage('client_mic_muted.png', 'Insufficient Talk Power');
            }
        }

        foreach ($node->memberOf() as $group) {
            if (!$group['iconid']) continue;

            $type = ($group instanceof \TeamSpeak3_Node_Servergroup) ? 'Server Group' : 'Channel Group';

            if (!$group->iconIsLocal('iconid') && $this->ftclient) {
                if (!isset($this->cachedIcons[$group['iconid']])) {
                    $download = $group->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $group->iconGetName('iconid'));

                    if ($this->ftclient == 'data:image') {
                        $download = \TeamSpeak3::factory('filetransfer://' . (strstr($download['host'], ':') !== false ? '[' . $download['host'] . ']' : $download['host']) . ':' . $download['port'])->download($download['ftkey'], $download['size']);
                    }

                    $this->cachedIcons[$group['iconid']] = $download;
                } else {
                    $download = $this->cachedIcons[$group['iconid']];
                }

                if ($this->ftclient == 'data:image') {
                    $icons[] = $this->getImage('data:' . \TeamSpeak3_Helper_Convert::imageMimeType($download) . ';base64,' . base64_encode($download), $group . ' [' . $type . ']', false);
                } else {
                    $icons[] = $this->getImage($this->ftclient . '?ftdata=' . base64_encode(serialize($download)), $group . ' [' . $type . ']', false);
                }
            } elseif (in_array($group['iconid'], $this->cachedIcons)) {
                $icons[] = $this->getImage('group_icon_' . $group['iconid'] . '.png', $group . ' [' . $type . ']');
            }
        }

        if ($node['client_icon_id']) {
            if (!$node->iconIsLocal('client_icon_id') && $this->ftclient) {
                if (!isset($this->cachedIcons[$node['client_icon_id']])) {
                    $download = $node->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $node->iconGetName('client_icon_id'));

                    if ($this->ftclient == 'data:image') {
                        $download = \TeamSpeak3::factory('filetransfer://' . (strstr($download['host'], ':') !== false ? '[' . $download['host'] . ']' : $download['host']) . ':' . $download['port'])->download($download['ftkey'], $download['size']);
                    }

                    $this->cachedIcons[$node['client_icon_id']] = $download;
                } else {
                    $download = $this->cachedIcons[$node['client_icon_id']];
                }

                if ($this->ftclient == 'data:image') {
                    $icons[] = $this->getImage('data:' . \TeamSpeak3_Helper_Convert::imageMimeType($download) . ';base64,' . base64_encode($download), 'Client Icon', false);
                } else {
                    $icons[] = $this->getImage($this->ftclient . '?ftdata=' . base64_encode(serialize($download)), 'Client Icon', false);
                }
            } elseif (in_array($node['client_icon_id'], $this->cachedIcons)) {
                $icons[] = $this->getImage('group_icon_' . $node['client_icon_id'] . '.png', 'Client Icon');
            }
        }

        return $icons;
    }

    /**
     * @param string $name
     * @param string $text
     * @param bool $iconpath
     * @param bool $flagpath
     * 
     * @return array
     */
    protected function getImage(
        string $name,
        string $text = '',
        bool $iconpath = true,
        bool $flagpath = false
    ) : array
    {
        $src = '';

        if ($iconpath) {
            $src = $this->iconpath;
        }

        if ($flagpath) {
            $src = $this->flagpath;
        }

        return [
            'src' => $src . $name,
            'text' => $text,
        ];
    }
}
