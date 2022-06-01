<?php

namespace Tests\Transport;

use \PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\TCP;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;

class TCPTest extends TestCase
{
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

        $this->assertArrayHasKey('blocking', $adapter->getConfig());
        $this->assertIsInt($adapter->getConfig('blocking'));
    }

    public function testConstructorExceptionNoHost()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'host'");

        $adapter = new TCP(['port' => 12345]);
    }

    public function testConstructorExceptionNoPort()
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage("config must have a key for 'port'");

        $adapter = new TCP(['host' => 'test']);
    }

    public function testGetConfig()
    {
        $adapter = new TCP(
            ['host' => 'test', 'port' => 12345]
        );

        $this->assertIsArray($adapter->getConfig());
        $this->assertCount(4, $adapter->getConfig());
        $this->assertArrayHasKey('host', $adapter->getConfig());
        $this->assertEquals('test', $adapter->getConfig()['host']);
        $this->assertEquals('test', $adapter->getConfig('host'));
    }

    public function testSetGetAdapter()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        // Mocking adaptor since `stream_socket_client()` depends on running server
        $adaptor = $this->createMock(ServerQuery::class);
        $transport->setAdapter($adaptor);

        $this->assertSame($adaptor, $transport->getAdapter());
    }

    public function testGetStream()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->getStream());
    }

    public function testConnectBadHost()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getaddrinfo for test failed');
        $transport->connect();
    }

    public function testConnectHostRefuseConnection()
    {
        $transport = new TCP(
            ['host' => '127.0.0.1', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Connection refused');
        $transport->connect();
    }

    public function testDisconnectNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->assertNull($transport->disconnect());
    }

    public function testReadNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getaddrinfo for test failed');
        $transport->read();
    }

    public function testReadLineNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getaddrinfo for test failed');
        $transport->readLine();
    }

    public function testSendNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getaddrinfo for test failed');
        $transport->send('testsend');
    }

    public function testSendLineNoConnection()
    {
        $transport = new TCP(
            ['host' => 'test', 'port' => 12345]
        );
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getaddrinfo for test failed');
        $transport->sendLine('test.sendLine');
    }
}
