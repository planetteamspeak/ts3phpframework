<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;

require_once('lib/TeamSpeak3/Helper/Crypt.php');

class CryptTest extends TestCase
{
  public function testEncrypt() {
    $crypto = new \TeamSpeak3_Helper_Crypt('My Secret Key');
    $this->assertEquals('b45xr3dIAI4=', $crypto->encrypt('password'));
    $this->assertEquals('password', $crypto->decrypt('b45xr3dIAI4='));
  }
}