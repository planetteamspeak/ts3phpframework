<?php

use PHPUnit\Framework\TestCase;

require_once('libraries/TeamSpeak3/Helper/String.php');

class StringTest extends TestCase
{
    public function testReplace()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $string->replace("world", "word");

        $this->assertEquals("Hello word!", (string) $string);


        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $string->replace("hello", "Hey", false);

        $this->assertEquals("Hey world!", (string) $string);
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
        $this->assertEquals(9, $string->findLast("l"));
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
        $this->assertTrue($string->contains(""));
        $this->assertTrue($string->contains("[a-z]{5}", true));
        $this->assertTrue($string->contains("world"));
        $this->assertFalse($string->contains("word"));
    }

    public function testSubstr()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals("ello", $string->substr(1, 4));
        $this->assertEquals("world", $string->substr(-6, 5));
    }

    public function testSplit()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $array  = $string->split('l', 3);
        $this->assertCount(3, $array);
        $this->assertEquals('He', $array[0]);
        $this->assertEmpty($array[1]);
    }

    public function testIsInt()
    {
        $tests = [
            "1"             => true,
            "+1"            => false,
            "-1"            => false,
            "hello"         => false,
            "1.0"           => false,
            ".1"            => false,

            // From https://goo.gl/C5v9wT
            "0x539"         => false,
            "0b10100111001" => false,
            "1337e0"        => false,
            "9.1"           => false,
        ];

        foreach ($tests as $test => $expected) {
            $string = new \TeamSpeak3_Helper_String($test);
            $this->assertSame($string->isInt(), $expected);
        }
    }

    public function testFactory()
    {
        $string = \TeamSpeak3_Helper_String::factory("hello world");

        $this->assertEquals("hello world", $string->toString());
    }

    public function testArg()
    {
        $string = new \TeamSpeak3_Helper_String("%h %w");

        $string->arg(["w" => "world", "h" => "Hello"]);

        $this->assertEquals(
            "Hello world",
            $string->toString()
        );
    }

    public function testAppend()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world");
        $string->append('!');
        $this->assertEquals("Hello world!", $string->toString());
    }

    public function testPrepend()
    {
        $string = new \TeamSpeak3_Helper_String("world!");
        $string->prepend("Hello ");
        $this->assertEquals("Hello world!", $string->toString());
    }

    public function testSection()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");

        $section = $string->section(' ');
        $this->assertEquals("Hello", $section->toString());

        $section = $string->section(' ', 1, 1);
        $this->assertEquals("world!", $section->toString());

        $section = $string->section(' ', 0, 1);
        $this->assertEquals("Hello world!", $section->toString());

        $section = $string->section(' ', 3, 3);
        $this->assertNull($section);
    }

    public function testToCrc32()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(crc32("Hello world!"), $string->toCrc32());
    }

    public function testToMd5()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(md5("Hello world!"), $string->toMd5());
    }

    public function testToSha1()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(sha1("Hello world!"), $string->toSha1());
    }

    public function testIsUtf8()
    {
        $string = new \TeamSpeak3_Helper_String(utf8_encode("Äpfel"));
        $this->assertTrue($string->isUtf8());

        $string = new \TeamSpeak3_Helper_String(utf8_decode("Äpfel"));
        $this->assertNotTrue($string->isUtf8());
    }

    public function testToUft8()
    {
        $notUtf8 = utf8_decode("Äpfel");
        $string  = new \TeamSpeak3_Helper_String($notUtf8);
        $this->assertEquals(utf8_encode($notUtf8), $string->toUtf8());
    }

    public function testToBase64()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(base64_encode("Hello world!"), $string->toBase64());
    }

    public function testFromBase64(){
        $string = \TeamSpeak3_Helper_String::fromBase64(base64_encode("Hello world!"));
        $this->assertEquals("Hello world!", $string->toString());
    }

    public function testToHex(){
        \TeamSpeak3::init();
        $string = new \TeamSpeak3_Helper_String("Hello");
        $this->assertEquals("48656C6C6F", $string->toHex());
    }

    public function testFromHex(){
        $string = \TeamSpeak3_Helper_String::fromHex("48656C6C6F");
        $this->assertEquals("Hello", $string->toString());
    }
}
