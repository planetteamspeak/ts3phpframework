<?php
/**
 * ts3phpframework
 * Created by PhpStorm.
 * File: StringTest.php
 * User: thhan
 * Date: 07.11.17
 * Time: 10:56
 */

use PHPUnit\Framework\TestCase;

require_once('libraries/TeamSpeak3/Helper/String.php');

class StringTest extends TestCase
{
    public function testReplace()
    {
        $string = new \TeamSpeak3_Helper_String("Hello World!");
        $string->replace("World", "Word");

        $this->assertEquals("Hello Word!", (string)$string);
    }

    public function testStartsWith()
    {
        $string = new \TeamSpeak3_Helper_String("Hello World!");
        $this->assertTrue($string->startsWith("Hello"));
    }

    public function testEndsWith()
    {
        $string = new \TeamSpeak3_Helper_String("Hello World!");
        $this->assertTrue($string->endsWith("!"));
    }

    public function testFindFirst(){
        $string = new \TeamSpeak3_Helper_String("Hello World!");
        $this->assertEquals(3,$string->findFirst("l"));

    }
}