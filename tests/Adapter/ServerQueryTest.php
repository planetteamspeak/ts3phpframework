<?php


namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Adapter;


use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\MockServerQuery;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;

class ServerQueryTest extends TestCase
{
    const S_ERROR_OK = 'error id=0 msg=ok';

    /**
     * @throws AdapterException
     */
    protected function createMockServerQuery(): MockServerQuery
    {
        return new MockServerQuery(['host' => '0.0.0.0', 'port' => 9987]);
    }

    /**
     * @throws ServerQueryException
     */
    public function testRequestIllegalCharakterException() {

        $query = "Hello\nWorld\r";

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage(sprintf("illegal characters in command '%s'", $query));
        $serverQuery = $this->createMockServerQuery();
        $serverQuery->request($query);
    }

    /**
     * @throws AdapterException
     * @throws ServerQueryException
     */
    public function testLogin() {
        $query = "login serveradmin secret";
        $serverQuery = $this->createMockServerQuery();
        $reply = $serverQuery->request($query);
        $this->assertEquals("ok", $reply->getErrorProperty('msg')->toString());

        $query = "login client_login_name=serveradmin client_login_password=secret";
        $serverQuery = $this->createMockServerQuery();
        $reply = $serverQuery->request($query);
        $this->assertEquals("ok", $reply->getErrorProperty('msg')->toString());
    }
}
