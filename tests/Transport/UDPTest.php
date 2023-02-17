<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Transport;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\UDP;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;

class UDPTest extends TestCase
{
    /**
     * @throws TransportException
     */
    public function testConstructorNoException()
    {
        $adapter = new UDP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertInstanceOf(UDP::class, $adapter);

        $this->assertArrayHasKey('host', $adapter->getConfig());
        $this->assertEquals('test', $adapter->getConfig('host'));

        $this->assertArrayHasKey('port', $adapter->getConfig());
        $this->assertEquals(12345, $adapter->getConfig('port'));

        $this->assertArrayHasKey('timeout', $adapter->getConfig());
        $this->assertIsInt($adapter->getConfig('timeout'));

        $this->assertArrayHasKey('blocking', $adapter->getConfig());
        $this->assertIsInt($adapter->getConfig('blocking'));
    }

    public function testConstructorExceptionNoHost()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'host'");

        new UDP(['port' => 12345]);
    }

    public function testConstructorExceptionNoPort()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'port'");

        new UDP(['host' => 'test']);
    }

    /**
     * @throws TransportException
     */
    public function testGetConfig()
    {
        $adapter = new UDP(
            ['host' => 'test', 'port' => 12345]
        );

        $this->assertIsArray($adapter->getConfig());
        $this->assertCount(4, $adapter->getConfig());
        $this->assertArrayHasKey('host', $adapter->getConfig());
        $this->assertEquals('test', $adapter->getConfig()['host']);
        $this->assertEquals('test', $adapter->getConfig('host'));
    }

    /**
     * @throws TransportException
     */
    public function testSetGetAdapter()
    {
        $transport = new UDP(
            ['host' => 'test', 'port' => 12345]
        );
        // Mocking adaptor since `stream_socket_client()` depends on running server
        $adaptor = $this->createMock(ServerQuery::class);
        $transport->setAdapter($adaptor);

        $this->assertSame($adaptor, $transport->getAdapter());
    }

    /**
     * @throws TransportException
     */
    public function testGetStream()
    {
        $transport = new UDP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->getStream());
    }

    /**
     * @throws TransportException
     */
    public function testConnect()
    {
        $transport = new UDP(
            ['host' => '127.0.0.1', 'port' => 12345]
        );
        $transport->connect();
        $this->assertIsResource($transport->getStream());
    }

    /**
     * @throws TransportException
     */
    public function testConnectBadHost()
    {
        $host = 'test';
        $transport = new UDP(
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
     */
    public function testDisconnect()
    {
        $transport = new UDP(
            ['host' => '127.0.0.1', 'port' => 12345]
        );
        $transport->connect();
        $this->assertIsResource($transport->getStream());
    }

    /**
     * @throws TransportException
     */
    public function testDisconnectNoConnection()
    {
        $transport = new UDP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->getStream());
        $transport->disconnect();
    }

    /**
     * @throws TransportException
     */
    public function testReadNoConnection()
    {
        $host = 'test';
        $transport = new UDP(
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
     */
    public function testSendNoConnection()
    {
        $host = 'test';
        $transport = new UDP(
            ['host' => $host, 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        if (PHP_VERSION_ID < 80100) {
            $this->expectExceptionMessage("getaddrinfo failed");
        } else {
            $this->expectExceptionMessage("getaddrinfo for $host failed");
        }
        $transport->send('test.send');
    }
}
