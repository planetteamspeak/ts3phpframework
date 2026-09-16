<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\FileTransfer;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\Transport;

class FileTransferTest extends TestCase
{
    public function testDownloadToStreamsAllChunksToConsumer(): void
    {
        $transfer = new class (['host' => 'test', 'port' => 12345]) extends FileTransfer {
            public function syn(): void
            {
                $this->transport = new class (['host' => 'test', 'port' => 12345]) extends Transport {
                    private array $chunks = ['ab', 'cde'];

                    public function connect(): void
                    {
                    }

                    public function disconnect(): void
                    {
                    }

                    public function read(int $length = 4096): StringHelper
                    {
                        return new StringHelper(array_shift($this->chunks));
                    }

                    public function send(string $data): void
                    {
                    }
                };
            }

            protected function init(string $ftkey): void
            {
            }
        };
        $chunks = [];

        $transfer->downloadTo('1234567890123456', 5, static function (StringHelper $data) use (&$chunks): void {
            $chunks[] = $data->toString();
        });

        $this->assertSame(['ab', 'cde'], $chunks);
    }

    public function testDownloadCollectsPartialReadsWithoutSkippingData(): void
    {
        $transfer = new class (['host' => 'test', 'port' => 12345]) extends FileTransfer {
            public function syn(): void
            {
                $this->transport = new class (['host' => 'test', 'port' => 12345]) extends Transport {
                    private array $chunks = ['ab', 'cde'];
                    public function connect(): void
                    {
                    }
                    public function disconnect(): void
                    {
                    }
                    public function read(int $length = 4096): StringHelper
                    {
                        return new StringHelper(array_shift($this->chunks));
                    }
                    public function send(string $data): void
                    {
                    }
                };
            }
            protected function init(string $ftkey): void
            {
            }
        };

        $this->assertSame('abcde', $transfer->download('1234567890123456', 5)->toString());
    }
}
