<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;

use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\Adapter;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\FileTransfer;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Event;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\SignalException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

/**
 * Interface SignalInterface
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal
 * @class SignalInterface
 * @brief Interface class describing the layout for PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal callbacks.
 */
interface SignalInterface
{
    /**
     * Possible callback for '<adapter>Connected' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryConnected", array($object, "onConnect"));
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferConnected", array($object, "onConnect"));
     *
     * @param Adapter $adapter
     * @return void
     */
    public function onConnect(Adapter $adapter): void;

    /**
     * Possible callback for '<adapter>Disconnected' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryDisconnected", array($object, "onDisconnect"));
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDisconnected", array($object, "onDisconnect"));
     *
     * @return void
     */
    public function onDisconnect(): void;

    /**
     * Possible callback for 'serverqueryCommandStarted' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryCommandStarted", array($object, "onCommandStarted"));
     *
     * @param string $cmd
     * @return void
     */
    public function onCommandStarted(string $cmd): void;

    /**
     * Possible callback for 'serverqueryCommandFinished' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryCommandFinished", array($object, "onCommandFinished"));
     *
     * @param string $cmd
     * @param Reply $reply
     * @return void
     */
    public function onCommandFinished(string $cmd, Reply $reply): void;

    /**
     * Possible callback for 'notifyEvent' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyEvent", array($object, "onEvent"));
     *
     * @param Event $event
     * @param Host $host
     * @return void
     */
    public function onEvent(Event $event, Host $host): void;

    /**
     * Possible callback for 'notifyError' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyError", array($object, "onError"));
     *
     * @param Reply $reply
     * @return void
     */
    public function onError(Reply $reply): void;

    /**
     * Possible callback for 'notifyServerselected' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServerselected", array($object, "onServerselected"));
     *
     * @param Host $host
     * @return void
     */
    public function onServerselected(Host $host): void;

    /**
     * Possible callback for 'notifyServercreated' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServercreated", array($object, "onServercreated"));
     *
     * @param Host $host
     * @param integer $sid
     * @return void
     */
    public function onServercreated(Host $host, int $sid): void;

    /**
     * Possible callback for 'notifyServerdeleted' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServerdeleted", array($object, "onServerdeleted"));
     *
     * @param Host $host
     * @param integer $sid
     * @return void
     */
    public function onServerdeleted(Host $host, int $sid): void;

    /**
     * Possible callback for 'notifyServerstarted' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServerstarted", array($object, "onServerstarted"));
     *
     * @param Host $host
     * @param integer $sid
     * @return void
     */
    public function onServerstarted(Host $host, int $sid): void;

    /**
     * Possible callback for 'notifyServerstopped' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServerstopped", array($object, "onServerstopped"));
     *
     * @param Host $host
     * @param integer $sid
     * @return void
     */
    public function onServerstopped(Host $host, int $sid): void;

    /**
     * Possible callback for 'notifyServershutdown' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyServershutdown", array($object, "onServershutdown"));
     *
     * @param Host $host
     * @return void
     */
    public function onServershutdown(Host $host): void;

    /**
     * Possible callback for 'notifyLogin' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyLogin", array($object, "onLogin"));
     *
     * @param Host $host
     * @return void
     */
    public function onLogin(Host $host): void;

    /**
     * Possible callback for 'notifyLogout' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyLogout", array($object, "onLogout"));
     *
     * @param Host $host
     * @return void
     */
    public function onLogout(Host $host): void;

    /**
     * Possible callback for 'notifyTokencreated' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("notifyTokencreated", array($object, "onTokencreated"));
     *
     * @param Server $server
     * @param string $token
     * @return void
     */
    public function onTokencreated(Server $server, string $token): void;

    /**
     * Possible callback for 'filetransferHandshake' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferHandshake", array($object, "onFtHandshake"));
     *
     * @param FileTransfer $adapter
     * @return void
     */
    public function onFtHandshake(FileTransfer $adapter): void;

    /**
     * Possible callback for 'filetransferUploadStarted' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferUploadStarted", array($object, "onFtUploadStarted"));
     *
     * @param string $ftkey
     * @param integer $seek
     * @param integer $size
     * @return void
     */
    public function onFtUploadStarted(string $ftkey, int $seek, int $size): void;

    /**
     * Possible callback for 'filetransferUploadProgress' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferUploadProgress", array($object, "onFtUploadProgress"));
     *
     * @param string $ftkey
     * @param integer $seek
     * @param integer $size
     * @return void
     */
    public function onFtUploadProgress(string $ftkey, int $seek, int $size): void;

    /**
     * Possible callback for 'filetransferUploadFinished' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferUploadFinished", array($object, "onFtUploadFinished"));
     *
     * @param string $ftkey
     * @param integer $seek
     * @param integer $size
     * @return void
     */
    public function onFtUploadFinished(string $ftkey, int $seek, int $size): void;

    /**
     * Possible callback for 'filetransferDownloadStarted' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDownloadStarted", array($object, "onFtDownloadStarted"));
     *
     * @param string $ftkey
     * @param integer $buff
     * @param integer $size
     * @return void
     */
    public function onFtDownloadStarted(string $ftkey, int $buff, int $size): void;

    /**
     * Possible callback for 'filetransferDownloadProgress' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDownloadProgress", array($object, "onFtDownloadProgress"));
     *
     * @param string $ftkey
     * @param integer $buff
     * @param integer $size
     * @return void
     */
    public function onFtDownloadProgress(string $ftkey, int $buff, int $size): void;

    /**
     * Possible callback for 'filetransferDownloadFinished' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDownloadFinished", array($object, "onFtDownloadFinished"));
     *
     * @param string $ftkey
     * @param integer $buff
     * @param integer $size
     * @return void
     */
    public function onFtDownloadFinished(string $ftkey, int $buff, int $size): void;

    /**
     * Possible callback for '<adapter>DataRead' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryDataRead", array($object, "onDebugDataRead"));
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDataRead", array($object, "onDebugDataRead"));
     *
     * @param string $data
     * @return void
     */
    public function onDebugDataRead(string $data): void;

    /**
     * Possible callback for '<adapter>DataSend' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryDataSend", array($object, "onDebugDataSend"));
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferDataSend", array($object, "onDebugDataSend"));
     *
     * @param string $data
     * @return void
     */
    public function onDebugDataSend(string $data): void;

    /**
     * Possible callback for '<adapter>WaitTimeout' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("serverqueryWaitTimeout", array($object, "onWaitTimeout"));
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("filetransferWaitTimeout", array($object, "onWaitTimeout"));
     *
     * @param integer $time
     * @param Adapter $adapter
     * @return void
     */
    public function onWaitTimeout(int $time, Adapter $adapter): void;

    /**
     * Possible callback for 'errorException' signals.
     *
     * === Examples ===
     *   - PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal::getInstance()->subscribe("errorException", array($object, "onException"));
     *
     * @param SignalException $e
     * @return void
     */
    public function onException(SignalException $e): void;
}
