<?php

use PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Uri;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;

class UriTest extends TestCase
{
    // URI format: <adapter>://<user>:<pass>@<host>:<port>/<options>#<flags>
    protected $mock = [
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
        $uri = new Uri('');
    }

    public function testConstructInvalidScheme()
    {
        $this->expectException(HelperException::class);
        $this->expectExceptionMessage('invalid URI scheme');

        // Uri should throw exception on non-alphanumeric in <scheme> of URI
        $uri = new Uri(str_replace(
            'serverquery',
            'server&&&&query', // non-alphanumeric
      $this->mock['test_uri'][0]
        ));
    }

    public function testParseURI()
    {
        // @todo: No reachable path results in error. Implement if found.
    }

    public function testCheckUser()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $ASCIIValid = [
      48,57,65,90,97,122,45,95,46,33,126,42,39,40,41,91,93,59,58,38,61,43,36,44
    ];
        $ASCIIInvalid = [
      34,35,37,47,60,62,63,64,92,94,96,123,124,125,127
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

        // Unicode character should fail
    $this->assertFalse($uri->checkUser("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    public function testCheckPass()
    {
        $uri = new Uri($this->mock['test_uri'][0]);

        $ASCIIValid = [
      48,57,65,90,97,122,45,95,46,33,126,42,39,40,41,91,93,59,58,38,61,43,36,44
    ];
        $ASCIIInvalid = [
      34,35,37,47,60,62,63,64,92,94,96,123,124,125,127
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

        // Unicode character should fail
    $this->assertFalse($uri->checkPass("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    public function testCheckHost()
    {
        // @todo: Implement after checkHost() validation implemented
    }

    public function testCheckPort()
    {
        // @todo: Implement after checkPort() validation implemented
    }

    public function testCheckPath()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/' and url escaped hex '%XX'
        $ASCIIValid = [48,57,65,90,97,122,45,95,46,33,126,42,39,40,41,91,93,58,64,
                   38,61,43,36,44,59,47];
        $ASCIIInvalid = [34,35,37,60,62,63,92,94,96,123,124,125,127];

        $this->assertTrue($uri->checkPath(''));
        $this->assertTrue($uri->checkPath('/'));
        $this->assertTrue($uri->checkPath('//'));
        $this->assertTrue($uri->checkPath('///'));

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkPath('/'.chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            //echo "GOT(" . $dec . "): " . chr($dec) . "\n";
            //var_dump($uri->checkPath('/'.chr($dec)));
            $this->assertFalse($uri->checkPath(chr($dec)));
            $this->assertFalse($uri->checkPath('/'.chr($dec)));
        }

        // Unicode character should fail
    $this->assertFalse($uri->checkPath("\xC2\xA2")); // "\u{00A2}" '¢'
    $this->assertFalse($uri->checkPath("/\xC2\xA2")); // "/\u{00A2}" '/¢'
    }

    public function testCheckQuery()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/#' and url escaped hex '%XX'
        $ASCIIValid = [48,57,65,90,97,122,45,95,46,33,126,42,39,40,41,91,93,58,64,
                   38,61,43,36,44,59,47,63];
        $ASCIIInvalid = [34,35,37,60,62,92,94,96,123,124,125,127];

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkQuery(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkQuery(chr($dec)));
        }

        // Unicode character should fail
    $this->assertFalse($uri->checkQuery("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    public function testCheckFragment()
    {
        $uri = new Uri($this->mock['test_uri'][1]);

        // NOTE: Similar, but different valid characters than previous tests.
        // '0-9A-Za-z-_.!~*'()[]:@&=+$,;/#' and url escaped hex '%XX'
        $ASCIIValid = [48,57,65,90,97,122,45,95,46,33,126,42,39,40,41,91,93,58,64,
                   38,61,43,36,44,59,47,63];
        $ASCIIInvalid = [34,35,37,60,62,92,94,96,123,124,125,127];

        // Test valid ASCII characters
        foreach ($ASCIIValid as $dec) {
            $this->assertTrue($uri->checkFragment(chr($dec)));
        }

        // Test invalid ASCII characters
        foreach ($ASCIIInvalid as $dec) {
            $this->assertFalse($uri->checkFragment(chr($dec)));
        }

        // Unicode character should fail
    $this->assertFalse($uri->checkFragment("\xC2\xA2")); // "\u{00A2}" '¢'
    }

    public function testIsValid()
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
        $this->assertInternalType(
            PHPUnit_IsType::TYPE_INT,
            $uri->getPort()
        );
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
        $this->assertInternalType(
            PHPUnit_IsType::TYPE_ARRAY,
            $uri->getQuery()
        );
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
