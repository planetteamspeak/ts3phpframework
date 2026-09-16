<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Adapter;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\FileTransferException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport;

/**
 * Class FileTransfer
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Adapter
 * @class FileTransfer
 * @brief Provides low-level methods for file transfer communication with a TeamSpeak 3 Server.
 */
class FileTransfer extends Adapter
{
    /**
     * Connects the PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport object and performs initial actions on the remote server.
     */
    public function syn(): void
    {
        $this->initTransport($this->options);
        $this->transport->setAdapter($this);

        Profiler::init(spl_object_hash($this));

        Signal::getInstance()->emit("filetransferConnected", $this);
    }

    /**
     * FileTransfer destructor.
     */
    public function __destruct()
    {
        if ($this->getTransport() instanceof Transport && $this->getTransport()->isConnected()) {
            $this->getTransport()->disconnect();
        }

        Profiler::remove(spl_object_hash($this));
    }

    /**
     * Sends a valid file transfer key to the server to initialize the file transfer.
     *
     * @param string $ftkey
     * @return void
     * @throws FileTransferException
     */
    protected function init(string $ftkey): void
    {
        if (strlen($ftkey) != 32 && strlen($ftkey) != 16) {
            throw new FileTransferException("invalid file transfer key format");
        }

        $this->getProfiler()->start();
        $this->getTransport()->send($ftkey);

        Signal::getInstance()->emit("filetransferHandshake", $this);
    }

    /**
     * Sends the content of a file to the server.
     *
     * @param string $ftkey
     * @param integer $seek
     * @param string $data
     * @return void
     * @throws FileTransferException
     */
    public function upload(string $ftkey, int $seek, string $data): void
    {
        $this->init($ftkey);

        $size = strlen($data);
        $pack = 4096;

        Signal::getInstance()->emit("filetransferUploadStarted", $ftkey, $seek, $size);

        for (; $seek < $size;) {
            $rest = $size - $seek;
            $pack = min($rest, $pack);
            $buff = substr($data, $seek, $pack);
            $seek = $seek + $pack;

            $this->getTransport()->send($buff);

            Signal::getInstance()->emit("filetransferUploadProgress", $ftkey, $seek, $size);
        }

        $this->getProfiler()->stop();

        Signal::getInstance()->emit("filetransferUploadFinished", $ftkey, $seek, $size);
    }

    /**
     * Returns the content of a downloaded file as a PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper object.
     *
     * @param string $ftkey
     * @param integer $size
     * @param boolean $passthru
     * @return StringHelper|void
     * @throws FileTransferException
     * @throws TransportException
     */
    public function download(string $ftkey, int $size, bool $passthru = false)
    {
        if ($passthru) {
            $this->downloadTo($ftkey, $size, static function (StringHelper $data): void {
                echo $data;
            });
            return;
        }

        $buff = new StringHelper("");
        $this->downloadTo($ftkey, $size, static function (StringHelper $data) use ($buff): void {
            $buff->append($data);
        });

        return $buff;
    }

    /**
     * Downloads a file and passes each received chunk to the given consumer.
     *
     * @param string $ftkey
     * @param integer $size
     * @param callable $consumer
     * @return void
     * @throws FileTransferException
     */
    public function downloadTo(string $ftkey, int $size, callable $consumer): void
    {
        $this->init($ftkey);
        $pack = 4096;
        $seek = 0;

        Signal::getInstance()->emit("filetransferDownloadStarted", $ftkey, $seek, $size);

        try {
            while ($seek < $size) {
                $data = $this->getTransport()->read(min($size - $seek, $pack));

                if (count($data) === 0) {
                    throw new FileTransferException("incomplete file download (" . $seek . " of " . $size . " bytes)");
                }

                $consumer($data);
                $seek += count($data);

                Signal::getInstance()->emit("filetransferDownloadProgress", $ftkey, $seek, $size);
            }
        } finally {
            $this->getProfiler()->stop();
        }

        Signal::getInstance()->emit("filetransferDownloadFinished", $ftkey, $seek, $size);
    }
}
