<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Transport;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\MockServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\TCP;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;

class TCPTest extends TestCase
{
    public function testReadLineWaitsForRemainingNonBlockingChunk(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345, 'blocking' => 0]) extends TCP {
            private array $chunks = ['cldbid=42 client_nickname=Some', false, "|body\\sname=Other\n"];

            public function setStreamForTest(): void
            {
                $this->stream = fopen('php://temp', 'r+');
            }

            protected function waitForReadyRead(int $time = 0): void
            {
            }

            protected function readLineChunk(): string|false
            {
                return array_shift($this->chunks);
            }
        };

        $transport->setStreamForTest();

        $this->assertSame(
            'cldbid=42 client_nickname=Some|body\\sname=Other',
            $transport->readLine()->toString()
        );
    }

    public function testReadLineThrowsWhenConnectionIsClosed(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345]) extends TCP {
            public function setStreamForTest(): void
            {
                $this->stream = fopen('php://temp', 'r');
            }
        };

        $transport->setStreamForTest();

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("connection to server 'test:12345' lost");
        $transport->readLine();
    }

    /**
     * @throws TransportException
     */
    public function testConstructorNoException()
    {
        $adapter = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertInstanceOf(TCP::class, $adapter);

        $this->assertArrayHasKey('host', $adapter->getConfig());
        $this->assertEquals('test', $adapter->getConfig('host'));

        $this->assertArrayHasKey('port', $adapter->getConfig());
        $this->assertEquals(12345, $adapter->getConfig('port'));

        $this->assertArrayHasKey('timeout', $adapter->getConfig());
        $this->assertIsInt($adapter->getConfig('timeout'));

        $this->assertArrayHasKey('tls_verify', $adapter->getConfig());
        $this->assertSame(0, $adapter->getConfig('tls_verify'));

        $this->assertArrayHasKey('blocking', $adapter->getConfig());
        $this->assertIsInt($adapter->getConfig('blocking'));
    }

    public function testConstructorExceptionNoHost()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'host'");

        new TCP(['port' => 12345]);
    }

    public function testConstructorExceptionNoPort()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'port'");

        new TCP(['host' => 'test']);
    }

    /**
     * @throws TransportException
     */
    public function testGetConfig()
    {
        $adapter = new TCP(
            ['host' => 'test', 'port' => 12345]
        );

        $this->assertIsArray($adapter->getConfig());
        $this->assertCount(5, $adapter->getConfig());
        $this->assertArrayHasKey('host', $adapter->getConfig());
        $this->assertEquals('test', $adapter->getConfig()['host']);
        $this->assertEquals('test', $adapter->getConfig('host'));
    }

    /**
     * @throws TransportException
     */
    public function testSetGetAdapter()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        // Mocking adaptor since `stream_socket_client()` depends on running server
        $adaptor = $this->createMockServerQuery();
        $transport->setAdapter($adaptor);

        $this->assertSame($adaptor, $transport->getAdapter());
    }

    /**
     * @throws TransportException
     */
    public function testGetStream()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->getStream());
    }

    /**
     * @throws AdapterException
     */
    protected function createMockServerQuery(): MockServerQuery
    {
        return new MockServerQuery(['host' => '0.0.0.0', 'port' => 9987]);
    }

    /**
     * Tests if the connection status gets properly returned.
     */
    public function testConnectionStatus()
    {
        $mockServerQuery = $this->createMockServerQuery();
        $this->assertTrue($mockServerQuery->getTransport()->isConnected());
        $mockServerQuery->getTransport()->disconnect();
        $this->assertFalse($mockServerQuery->getTransport()->isConnected());
    }

    /**
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function testConnectBadHost()
    {
        $host = 'test';
        $transport = new TCP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->connect();
    }

    /**
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function testConnectHostRefuseConnection()
    {
        $transport = new class (['host' => '127.0.0.1', 'port' => 12345]) extends TCP {
            protected function openSocket(string $address, int &$errno, string &$errstr, int $timeout, array $options): mixed
            {
                $errno = 111;
                $errstr = 'Connection refused';

                return false;
            }
        };
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Connection refused');
        $transport->connect();
    }

    /**
     * @throws TransportException
     */
    public function testDisconnect()
    {
        $transport = new TCP(
            ['host' => '127.0.0.1', 'port' => 12345]
        );
        $transport->disconnect();
        $this->assertNull($transport->getStream());
    }

    /**
     * @throws TransportException
     */
    public function testDisconnectNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->getStream());
        $transport->disconnect();
    }

    /**
     * @throws ServerQueryException
     * @throws TransportException
     */
    public function testReadNoConnection()
    {
        $host = 'test';
        $transport = new TCP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->read();
    }

    /**
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function testReadLineNoConnection()
    {
        $host = 'test';
        $transport = new TCP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->readLine();
    }

    /**
     * @throws TransportException
     * @throws ServerQueryException
     */
    public function testSendNoConnection()
    {
        $host = 'test';
        $transport = new TCP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->send('testsend');
    }

    public function testSendRetriesPartialWritesAndRejectsFailedWrites(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345]) extends TCP {
            public array $writes = [];
            private array $results = [2, 3];
            public function connect(): void
            {
                $this->stream = true;
            }
            protected function write(string $data): int|false
            {
                $this->writes[] = $data;
                return array_shift($this->results);
            }
        };
        $transport->send('hello');
        $this->assertSame(['hello', 'llo'], $transport->writes);
    }

    /**
     * @throws ServerQueryException
     * @throws TransportException
     */
    public function testSendLineNoConnection()
    {
        $host = 'abc';
        $transport = new TCP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->sendLine('test.sendLine');
    }

    public function testNonBlockingReadTimesOut(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345, 'blocking' => 0, 'timeout' => 0]) extends TCP {
            private $peer;

            public function connectForTest(): void
            {
                [$this->stream, $this->peer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            }

            public function waitForReadForTest(): void
            {
                $this->waitForReadyRead();
            }
        };

        $transport->connectForTest();

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("timed out waiting for data from server 'test:12345'");
        $transport->waitForReadForTest();
    }

    public function testTlsVerificationIsDisabledByDefault(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345, 'tls' => 1]) extends TCP {
            public array $contextOptions;

            protected function openSocket(string $address, int &$errno, string &$errstr, int $timeout, array $options): mixed
            {
                $this->contextOptions = $options;
                return fopen('php://temp', 'r+');
            }

            protected function enableCrypto(): bool
            {
                return true;
            }
        };

        $transport->connect();

        $this->assertSame(['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false], $transport->contextOptions['ssl']);
    }

    public function testTlsVerificationCanBeEnabled(): void
    {
        $transport = new class (['host' => 'test', 'port' => 12345, 'tls' => 1, 'tls_verify' => 1]) extends TCP {
            public array $contextOptions;

            protected function openSocket(string $address, int &$errno, string &$errstr, int $timeout, array $options): mixed
            {
                $this->contextOptions = $options;
                return fopen('php://temp', 'r+');
            }

            protected function enableCrypto(): bool
            {
                return true;
            }
        };

        $transport->connect();

        $this->assertSame(['allow_self_signed' => false, 'verify_peer' => true, 'verify_peer_name' => true], $transport->contextOptions['ssl']);
    }
}
