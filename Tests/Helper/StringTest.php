<?php
use PHPUnit\Framework\TestCase;

require_once('libraries/TeamSpeak3/Helper/String.php');

class StringTest extends TestCase
{
    public function testReplace()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $string->replace("world", "word");

        $this->assertEquals("Hello word!", (string)$string);
    }

    public function testStartsWith()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertTrue($string->startsWith("Hello"));
        $this->assertFalse($string->startsWith("world"));
    }

    public function testEndsWith()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertTrue($string->endsWith("!"));
        $this->assertFalse($string->endsWith("."));
    }

    public function testFindFirst()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(2, $string->findFirst("l"));
    }

    public function testFindLast()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(2, $string->findFirst("l"));
    }

    public function testToLower()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals("hello world!", $string->toLower());
    }

    public function testToUpper()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals("HELLO WORLD!", $string->toUpper());
    }

    public function testContains()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertTrue($string->contains("world"));
        $this->assertFalse($string->contains("word"));
    }

    public function testSubstr()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals("ello", $string->substr(1, 4));
        $this->assertEquals("world", $string->substr(-6, 5));
    }
}
