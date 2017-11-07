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

require_once('../../libraries/TeamSpeak3/Helper/String.php');

class StringTest extends TestCase
{
    public function testReplace()
    {
        $string = new \TeamSpeak3_Helper_String("Hello World!");
        $string->replace("World", "Word");

        $this->assertEquals("Hello Word!", (string) $string);
    }
}