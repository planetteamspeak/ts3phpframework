<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Crypt;

class CryptTest extends TestCase
{
    public function testSetSecretKeyWithTooFewArguments()
    {
        $this->expectException(ArgumentCountError::class);
        new Crypt();
    }

    public function testSetSecretKeyWithTooFewCharacters()
    {
        $this->expectException(HelperException::class);
        new Crypt("");
    }

    public function testSetSecretKeyWithTooManyCharacters()
    {
        $this->expectException(HelperException::class);
        new Crypt("Lorem ipsum dolor sit amet consetetur sadipscing elitr se");
    }

    public function testEncrypt()
    {
        $crypto = new Crypt('My Secret Key');
        $this->assertEquals('b45xr3dIAI4=', $crypto->encrypt('password'));
    }

    public function testDecrypt()
    {
        $crypto = new Crypt('My Secret Key');
        $this->assertEquals('password', $crypto->decrypt('b45xr3dIAI4='));
    }

    public function testDecryptWithDifferentSecret()
    {
        $crypto = new Crypt('My Secret Key');
        $this->assertEquals('b45xr3dIAI4=', $crypto->encrypt('password'));
        $this->assertSame('password', $crypto->decrypt('b45xr3dIAI4='));

        $crypto = new Crypt('The Secret Changed');
        $this->assertNotSame('password', $crypto->decrypt('b45xr3dIAI4='));
    }
}
