<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Uri;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

class UriTest extends TestCase
{
    // URI format: <adapter>://<user>:<pass>@<host>:<port>/<options>#<flags>
    protected array $mock = [
        'adapters' => [
            'serverquery',
            'filetransfer'
        ],
        'options' => [
            'timeout',
            'blocking',
            'nickname',
            'no_query_clients',
            'use_offline_as_virtual',
            'clients_before_channels',
            'server' => [
                'server_id',
                'server_uid',
                'server_port',
                'server_name',
                'server_tsdns'
            ],
            'channel' => [
                'channel_id',
                'channel_name'
            ],
            'client' => [
                'client_id',
                'client_uid',
                'client_name'
            ]
        ],
        'flags' => [
            'no_query_clients',
            'use_offline_as_virtual',
            'clients_before_channels'
        ],
        'test_uri' => [
            'serverquery://username:password@127.0.0.1:10011',
            'serverquery://username:password@127.0.0.1:10011/?server_port=9987',
            'serverquery://username:password@127.0.0.1:10011/?server_port=9987&blocking=0',
            'serverquery://username:password@127.0.0.1:10011/?server_port=9987&blocking=0#no_query_clients'
        ]
    ];

    public function testConstructEmptyURI()
    {
        $this->expectException(HelperException::class);
        $this->expectExceptionMessage('invalid URI scheme');

        // Uri should throw exception on non-alphanumeric in <scheme> of URI
        new Uri('');
    }

    public function testConstructInvalidScheme()
    {
        $this->expectException(HelperException::class);
        $this->expectExceptionMessage('invalid URI scheme');

        // Uri should throw exception on non-alphanumeric in <scheme> of URI
        new Uri(str_replace(
            'serverquery',
            'server&&&&query', // non-alphanumeric
            $this->mock['test_uri'][0]
        ));
    }

    public function testParseURI()
    {
        // @todo: No reachable path results in error. Implement if found.
    }

    /**
     * @throws HelperException
     */
    public function testCheckUser()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $ASCIIValid = [
            48, 57, 65, 90, 97, 122, 45, 95, 46, 33, 126, 42, 39, 40, 41, 91, 93, 59, 58, 38, 61, 43, 36, 44
        ];
        $ASCIIInvalid = [
            34, 35, 37, 47, 60, 62, 63, 64, 92, 94, 96, 123, 124, 125, 127
        ];

        $this->assertTrue($uri->checkUser(''));

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkUser(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkUser(chr($dec)));
        }

        // Unicode's character should fail
        $this->assertFalse($uri->checkUser("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    /**
     * @throws HelperException
     */
    public function testCheckPass()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $ASCIIValid = [
            48, 57, 65, 90, 97, 122, 45, 95, 46, 33, 126, 42, 39, 40, 41, 91, 93, 59, 58, 38, 61, 43, 36, 44
        ];
        $ASCIIInvalid = [
            34, 35, 37, 47, 60, 62, 63, 64, 92, 94, 96, 123, 124, 125, 127
        ];

        $this->assertTrue($uri->checkPass(''));

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkPass(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkPass(chr($dec)));
        }

        // Unicode's character should fail
        $this->assertFalse($uri->checkPass("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    public function testCheckHost()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $validHosts = [
            // Private IPv4 addresses
            "127.0.0.1", "127.1.2.3", "192.168.178.37", "192.168.2.13", "10.0.0.10", "10.10.10.10", "172.16.5.250",
            // Private IPv6 addresses
            "0:0:0:0:0:0:0:1", "::1", "2003:cf:273b:9b00:7a8d:8622:fc3b:e684",
            // Private DNS hostnames
            "localhost", "localhost.localdomain", "web-03.host.example.com",
            // Current hostname of the local machine
            gethostname(),

            // Public IPv4 addresses
            "8.8.8.8", "8.8.4.4", "1.1.1.1",
            // Public IPv6 addresses
            "2001:4860:4860:0:0:0:0:8888", "2001:4860:4860::8888", "2001:4860:4860::8844",
            // Public DNS hostnames
            "github.com", "google.com", "wikipedia.org", "some-site1337.com",
        ];
        foreach ($validHosts as $host) {
            $this->assertTrue($uri->checkHost($host));
        }

        $invalidHosts = [
            // Private IPv4 addresses (invalid address)
            "127.0.0.256", "192.168.178.260", "10.256.0.1",
            // Private IPv6 addresses (invalid address)
            "0:0:0:0:0:0:0:YZ", "::YZ",
            // Private IPv6 addresses (invalid format)
            "fe80:::", ":::", "0:::0", "2003:cf:::fc3b::",
            // Private DNS hostnames (not RFC 1123 conform)
            ".localhost.local", "-localhost", "localhost-",

            // Public IPv4 addresses (invalid address)
            "256.1.2.3", "83.256.1.37",
            // Public IPv6 addresses (invalid format)
            "2001:4860:::0:0:0:0::1", "2001:::8888", ":::8844",
            // Public DNS hostnames (not RFC 1123 conform)
            ".github.com", "-github.wtf", "github-.com",
        ];
        foreach ($invalidHosts as $host) {
            $this->assertFalse($uri->checkHost($host));
        }
    }

    public function testCheckPort()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $validPorts = [
            80, 443, 8080, 9200, 9987, 10011, 10022,
        ];
        foreach ($validPorts as $port) {
            $this->assertTrue($uri->checkPort($port));
        }

        $invalidPorts = [
            -443, -1.5, -1, 0, 0.5, 65536,
        ];
        foreach ($invalidPorts as $port) {
            $this->assertFalse($uri->checkPort($port));
        }
    }

    /**
     * @throws HelperException
     */
    public function testCheckPath()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/' and url escaped hex '%XX'
        $ASCIIValid = [48, 57, 65, 90, 97, 122, 45, 95, 46, 33, 126, 42, 39, 40, 41, 91, 93, 58, 64,
            38, 61, 43, 36, 44, 59, 47];
        $ASCIIInvalid = [34, 35, 37, 60, 62, 63, 92, 94, 96, 123, 124, 125, 127];

        $this->assertTrue($uri->checkPath(''));
        $this->assertTrue($uri->checkPath('/'));
        $this->assertTrue($uri->checkPath('//'));
        $this->assertTrue($uri->checkPath('///'));

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkPath('/' . chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            //echo "GOT(" . $dec . "): " . chr($dec) . "\n";
            //var_dump($uri->checkPath('/'.chr($dec)));
            $this->assertFalse($uri->checkPath(chr($dec)));
            $this->assertFalse($uri->checkPath('/' . chr($dec)));
        }

        // Unicode's character should fail
        $this->assertFalse($uri->checkPath("\xC2\xA2")); // "\u{00A2}" '¢'
        $this->assertFalse($uri->checkPath("/\xC2\xA2")); // "/\u{00A2}" '/¢'
    }

    /**
     * @throws HelperException
     */
    public function testCheckQuery()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/#' and url escaped hex '%XX'
        $ASCIIValid = [48, 57, 65, 90, 97, 122, 45, 95, 46, 33, 126, 42, 39, 40, 41, 91, 93, 58, 64,
            38, 61, 43, 36, 44, 59, 47, 63];
        $ASCIIInvalid = [34, 35, 37, 60, 62, 92, 94, 96, 123, 124, 125, 127];

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkQuery(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkQuery(chr($dec)));
        }

        // Unicode's character should fail
        $this->assertFalse($uri->checkQuery("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    /**
     * @throws HelperException
     */
    public function testCheckFragment()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/#' and url escaped hex '%XX'
        $ASCIIValid = [48, 57, 65, 90, 97, 122, 45, 95, 46, 33, 126, 42, 39, 40, 41, 91, 93, 58, 64,
            38, 61, 43, 36, 44, 59, 47, 63];
        $ASCIIInvalid = [34, 35, 37, 60, 62, 92, 94, 96, 123, 124, 125, 127];

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkFragment(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkFragment(chr($dec)));
        }

        // Unicode's character should fail
        $this->assertFalse($uri->checkFragment("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    /**
     * @throws HelperException
     */
    public function testIsValid(): Uri
    {
        $uri = new Uri($this->mock['test_uri'][3]);

        $this->assertTrue($uri->isValid());

        return $uri;
    }

    /**
     * @param Uri $uri
     * @depends testIsValid
     */
    public function testGetScheme(Uri $uri)
    {
        $this->assertEquals('serverquery', $uri->getScheme());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getScheme()
        );
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetUser(Uri $uri)
    {
        $this->assertEquals('username', $uri->getUser());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getUser()
        );
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetPass(Uri $uri)
    {
        $this->assertEquals('password', $uri->getPass());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getPass()
        );
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetHost(Uri $uri)
    {
        $this->assertEquals('127.0.0.1', $uri->getHost());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getHost()
        );
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetPort(Uri $uri)
    {
        $this->assertEquals(10011, $uri->getPort());
        $this->assertIsInt($uri->getPort());
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetPath(Uri $uri)
    {
        // NOTE: getPath() is never used in framework, add tests for consistency.
        $this->assertEquals('/', $uri->getPath());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getPath()
        );
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetQuery(Uri $uri)
    {
        // NOTE: getPath() is never used in framework, add tests for consistency.
        $this->assertEquals(
            ['server_port' => '9987', 'blocking' => '0'],
            $uri->getQuery()
        );
        $this->assertIsArray($uri->getQuery());
    }

    /**
     * @param Uri $uri
     *
     * @depends testIsValid
     */
    public function testGetFragment(Uri $uri)
    {
        $this->assertEquals('no_query_clients', $uri->getFragment());
        $this->assertInstanceOf(
            StringHelper::class,
            $uri->getFragment()
        );
    }

    // @todo: Implement remaining get* tests
    // Deferring for now, since mostly web related.
}
