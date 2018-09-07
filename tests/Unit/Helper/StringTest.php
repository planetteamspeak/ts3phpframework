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

        // Well-formed UTF-8 Byte Sequences
        // Ref: Unicode (v11.0.0) - §3.9 Unicode Encoding Forms (126, table 3-7)
        // https://www.unicode.org/versions/Unicode11.0.0/ch03.pdf#page=55
        $unicodeBoundaries = [
            // Ignoring first set (ASCII) as isUtf8() does not use it.
            //[[0x00],[0x7F]],                                // U+0000..U+007F
            [[0xC2,0x80], [0xDF,0xBF]],                     // U+0080..U+07FF
            [[0xE0,0xA0,0x80], [0xE0,0xBF,0xBF]],           // U+0800..U+0FFF
            [[0xE1,0x80,0x80], [0xEC,0xBF,0xBF]],           // U+1000..U+CFFF
            [[0xED,0x80,0x80], [0xED,0x9F,0xBF]],           // U+D000..U+D7FF
            [[0xEE,0x80,0x80], [0xEF,0xBF,0xBF]],           // U+E000..U+FFFF
            [[0xF0,0x90,0x80,0x80], [0xF0,0xEF,0xBF,0xBF]], // U+10000..U+3FFFF
            [[0xF1,0x80,0x80,0x80], [0xF3,0xBF,0xBF,0xBF]], // U+40000..U+FFFFF
            [[0xF4,0x80,0x80,0x80], [0xF4,0x8F,0xBF,0xBF]]  // U+100000..U+10FFFF
        ];

        // Lower character precedes lower unicode boundary. 
        // Upper character follows upper unicode boundary.
        $unicodeBoundariesMalformed = [
            [[0x80],[0xFF]],
            [[0xC2,0x7F], [0xDF,0xC0]],
            [[0xE0,0xA0,0x7F], [0xE0,0xBF,0xC0]],
            [[0xE1,0x80,0x7F], [0xEC,0xBF,0xC0]],
            [[0xED,0x80,0x7F], [0xED,0x9F,0xC0]],
            [[0xEE,0x80,0x7F], [0xEF,0xBF,0xC0]],
            [[0xF0,0x90,0x80,0x7F], [0xF0,0xEF,0xBF,0xC0]],
            [[0xF1,0x80,0x80,0x7F], [0xF3,0xBF,0xBF,0xC0]],
            [[0xF4,0x80,0x80,0x7F], [0xF4,0x8F,0xBF,0xC0]]
        ];

        foreach($unicodeBoundaries as $boundary) {
            $lowerUtf8MultibyteChar = new \TeamSpeak3_Helper_String(array_reduce(
                $boundary[0],
                function($mb_string, $item) {
                  $mb_string .= chr($item);
                  return $mb_string;
                }
            ));
            $this->assertTrue($lowerUtf8MultibyteChar->isUtf8());
            $upperUtf8MultibyteChar = new \TeamSpeak3_Helper_String(array_reduce(
                $boundary[1],
                function($mb_string, $item) {
                  //var_dump($item);
                  $mb_string .= chr($item);
                  return $mb_string;
                }
            ));
            $this->assertTrue($upperUtf8MultibyteChar->isUtf8());
        }

        foreach($unicodeBoundariesMalformed as $boundary) {
            $lowerUtf8MultibyteChar = new \TeamSpeak3_Helper_String(array_reduce(
                $boundary[0],
                function($mb_string, $item) {
                  $mb_string .= chr($item);
                  return $mb_string;
                }
            ));
            $this->assertNotTrue($lowerUtf8MultibyteChar->isUtf8());
            $upperUtf8MultibyteChar = new \TeamSpeak3_Helper_String(array_reduce(
                $boundary[1],
                function($mb_string, $item) {
                  //var_dump($item);
                  $mb_string .= chr($item);
                  return $mb_string;
                }
            ));
            $this->assertNotTrue($upperUtf8MultibyteChar->isUtf8());
        }
    }

    public function testToUft8()
    {
        $notUtf8 = utf8_decode("Äpfel");
        $stringNotUtf8  = new \TeamSpeak3_Helper_String($notUtf8);
        $this->assertEquals(utf8_encode($notUtf8), $stringNotUtf8->toUtf8()->toString());

        $notUtf8 = utf8_decode("¶");
        $stringNotUtf8  = new \TeamSpeak3_Helper_String($notUtf8);
        $this->assertEquals(utf8_encode($notUtf8), $stringNotUtf8->toUtf8()->toString());
    }

    public function testToBase64()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals(base64_encode("Hello world!"), $string->toBase64());
    }

    public function testFromBase64()
    {
        $string = \TeamSpeak3_Helper_String::fromBase64(base64_encode("Hello world!"));
        $this->assertEquals("Hello world!", $string->toString());
    }

    public function testToHex()
    {
        \TeamSpeak3::init();
        $string = new \TeamSpeak3_Helper_String("Hello");
        $this->assertEquals("48656C6C6F", $string->toHex());
    }

    public function testFromHex()
    {
        $string = \TeamSpeak3_Helper_String::fromHex("48656C6C6F");
        $this->assertEquals("Hello", $string->toString());
    }

    public function testTransliterate()
    {
        $utf8_accents = array(
            "à" => "a",
            "ô" => "o",
            "ď" => "d",
            "ḟ" => "f",
            "ë" => "e",
            "š" => "s",
            "ơ" => "o",
            "ß" => "ss",
            "ă" => "a",
            "ř" => "r",
            "ț" => "t",
            "ň" => "n",
            "ā" => "a",
            "ķ" => "k",
            "ŝ" => "s",
            "ỳ" => "y",
            "ņ" => "n",
            "ĺ" => "l",
            "ħ" => "h",
            "ṗ" => "p",
            "ó" => "o",
            "ú" => "u",
            "ě" => "e",
            "é" => "e",
            "ç" => "c",
            "ẁ" => "w",
            "ċ" => "c",
            "õ" => "o",
            "ṡ" => "s",
            "ø" => "o",
            "ģ" => "g",
            "ŧ" => "t",
            "ș" => "s",
            "ė" => "e",
            "ĉ" => "c",
            "ś" => "s",
            "î" => "i",
            "ű" => "u",
            "ć" => "c",
            "ę" => "e",
            "ŵ" => "w",
            "ṫ" => "t",
            "ū" => "u",
            "č" => "c",
            "ö" => "oe",
            "è" => "e",
            "ŷ" => "y",
            "ą" => "a",
            "ł" => "l",
            "ų" => "u",
            "ů" => "u",
            "ş" => "s",
            "ğ" => "g",
            "ļ" => "l",
            "ƒ" => "f",
            "ž" => "z",
            "ẃ" => "w",
            "ḃ" => "b",
            "å" => "a",
            "ì" => "i",
            "ï" => "i",
            "ḋ" => "d",
            "ť" => "t",
            "ŗ" => "r",
            "ä" => "ae",
            "í" => "i",
            "ŕ" => "r",
            "ê" => "e",
            "ü" => "ue",
            "ò" => "o",
            "ē" => "e",
            "ñ" => "n",
            "ń" => "n",
            "ĥ" => "h",
            "ĝ" => "g",
            "đ" => "d",
            "ĵ" => "j",
            "ÿ" => "y",
            "ũ" => "u",
            "ŭ" => "u",
            "ư" => "u",
            "ţ" => "t",
            "ý" => "y",
            "ő" => "o",
            "â" => "a",
            "ľ" => "l",
            "ẅ" => "w",
            "ż" => "z",
            "ī" => "i",
            "ã" => "a",
            "ġ" => "g",
            "ṁ" => "m",
            "ō" => "o",
            "ĩ" => "i",
            "ù" => "u",
            "į" => "i",
            "ź" => "z",
            "á" => "a",
            "û" => "u",
            "þ" => "th",
            "ð" => "dh",
            "æ" => "ae",
            "µ" => "u",
            "ĕ" => "e",
            "œ" => "oe",
            "À" => "A",
            "Ô" => "O",
            "Ď" => "D",
            "Ḟ" => "F",
            "Ë" => "E",
            "Š" => "S",
            "Ơ" => "O",
            "Ă" => "A",
            "Ř" => "R",
            "Ț" => "T",
            "Ň" => "N",
            "Ā" => "A",
            "Ķ" => "K",
            "Ŝ" => "S",
            "Ỳ" => "Y",
            "Ņ" => "N",
            "Ĺ" => "L",
            "Ħ" => "H",
            "Ṗ" => "P",
            "Ó" => "O",
            "Ú" => "U",
            "Ě" => "E",
            "É" => "E",
            "Ç" => "C",
            "Ẁ" => "W",
            "Ċ" => "C",
            "Õ" => "O",
            "Ṡ" => "S",
            "Ø" => "O",
            "Ģ" => "G",
            "Ŧ" => "T",
            "Ș" => "S",
            "Ė" => "E",
            "Ĉ" => "C",
            "Ś" => "S",
            "Î" => "I",
            "Ű" => "U",
            "Ć" => "C",
            "Ę" => "E",
            "Ŵ" => "W",
            "Ṫ" => "T",
            "Ū" => "U",
            "Č" => "C",
            "Ö" => "Oe",
            "È" => "E",
            "Ŷ" => "Y",
            "Ą" => "A",
            "Ł" => "L",
            "Ų" => "U",
            "Ů" => "U",
            "Ş" => "S",
            "Ğ" => "G",
            "Ļ" => "L",
            "Ƒ" => "F",
            "Ž" => "Z",
            "Ẃ" => "W",
            "Ḃ" => "B",
            "Å" => "A",
            "Ì" => "I",
            "Ï" => "I",
            "Ḋ" => "D",
            "Ť" => "T",
            "Ŗ" => "R",
            "Ä" => "Ae",
            "Í" => "I",
            "Ŕ" => "R",
            "Ê" => "E",
            "Ü" => "Ue",
            "Ò" => "O",
            "Ē" => "E",
            "Ñ" => "N",
            "Ń" => "N",
            "Ĥ" => "H",
            "Ĝ" => "G",
            "Đ" => "D",
            "Ĵ" => "J",
            "Ÿ" => "Y",
            "Ũ" => "U",
            "Ŭ" => "U",
            "Ư" => "U",
            "Ţ" => "T",
            "Ý" => "Y",
            "Ő" => "O",
            "Â" => "A",
            "Ľ" => "L",
            "Ẅ" => "W",
            "Ż" => "Z",
            "Ī" => "I",
            "Ã" => "A",
            "Ġ" => "G",
            "Ṁ" => "M",
            "Ō" => "O",
            "Ĩ" => "I",
            "Ù" => "U",
            "Į" => "I",
            "Ź" => "Z",
            "Á" => "A",
            "Û" => "U",
            "Þ" => "Th",
            "Ð" => "Dh",
            "Æ" => "Ae",
            "Ĕ" => "E",
            "Œ" => "Oe",
        );

        $string = "";
        $result = "";

        foreach ($utf8_accents as $k => $v) {
            $string .= $k;
            $result .= $v;
        }

        $string = new \TeamSpeak3_Helper_String($string);
        $this->assertEquals($result, $string->transliterate()->toString());
    }

    public function testSpaceToPercent()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");
        $this->assertEquals("Hello%20world!", $string->spaceToPercent());
    }

    public function testJsonSerialize()
    {
        $string = new \TeamSpeak3_Helper_String("Hello world!");

        $this->assertJsonStringEqualsJsonString(
            json_encode(["a" => $string]),
            json_encode(["a" => "Hello world!"])
        );
    }
}
