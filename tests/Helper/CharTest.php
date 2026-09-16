<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Char;

class CharTest extends TestCase
{
    /**
     * @throws HelperException
     */
    public function testASCIILetter()
    {
        $testLower = chr(97);
        $testUpper = chr(65);
        $testOrd   = 97;
        $char = new Char(chr(97));

        $this->assertTrue($char->isLetter());
        $this->assertTrue($char->isPrintable());
        $this->assertTrue($char->isLower());

        $this->assertFalse($char->isDigit());
        $this->assertFalse($char->isSpace());
        $this->assertFalse($char->isMark());
        $this->assertFalse($char->isControl());
        $this->assertFalse($char->isNull());
        $this->assertFalse($char->isUpper());

        $this->assertEquals($testLower, (string)$char);  // Expect: 'a'
        $this->assertEquals($testUpper, (string)$char->toUpper()); // Expect: 'A'
        $this->assertEquals($testLower, (string)$char->toLower()); // Expect: 'a'

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 97
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('61')
        $this->assertEquals(
            $testLower,
            (string)Char::fromHex('61')
        );

        $this->assertEquals($testLower, $char->toString()); // Expect: 97
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testLower, $char->toInt()); // Expect: 97
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testASCIIDigit()
    {
        $testChar = chr(57);
        $testOrd  = 57;
        $char      = new Char($testChar); // (ASCII) '9'

        $this->assertTrue($char->isDigit());
        $this->assertTrue($char->isPrintable());
        $this->assertTrue($char->isUpper());
        $this->assertTrue($char->isLower());

        $this->assertFalse($char->isLetter());
        $this->assertFalse($char->isSpace());
        $this->assertFalse($char->isMark());
        $this->assertFalse($char->isControl());
        $this->assertFalse($char->isNull());

        $this->assertEquals($testChar, (string)$char);  // Expect: '9'
        $this->assertEquals($testChar, (string)$char->toUpper()); // Expect: '9'
        $this->assertEquals($testChar, (string)$char->toLower()); // Expect: '9'

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 57
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('39')
        $this->assertEquals(
            $testChar,
            (string)Char::fromHex('39')
        );

        $this->assertEquals($testChar, $char->toString()); // Expect: 57
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testChar, $char->toInt()); // Expect: 57
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testASCIISpace()
    {
        $testChar = chr(32);
        $testOrd  = 32;
        $char      = new Char($testChar); // (ASCII) ' '

        $this->assertTrue($char->isSpace());
        $this->assertTrue($char->isPrintable());
        $this->assertTrue($char->isUpper());
        $this->assertTrue($char->isLower());

        $this->assertFalse($char->isLetter());
        $this->assertFalse($char->isDigit());
        $this->assertFalse($char->isMark());
        $this->assertFalse($char->isControl());
        $this->assertFalse($char->isNull());

        $this->assertEquals($testChar, (string)$char);  // Expect: ' '
        $this->assertEquals($testChar, (string)$char->toUpper()); // Expect: ' '
        $this->assertEquals($testChar, (string)$char->toLower()); // Expect: ' '

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 32
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('20')
        $this->assertEquals(
            $testChar,
            (string)Char::fromHex('20')
        );

        $this->assertEquals($testChar, $char->toString()); // Expect: 32
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testChar, $char->toInt()); // Expect: 32
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testASCIIMark()
    {
        $testChar = chr(45);
        $testOrd  = 45;
        $char     = new Char($testChar); // (ASCII) '-'

        $this->assertTrue($char->isMark());
        $this->assertTrue($char->isPrintable());
        $this->assertTrue($char->isLower());
        $this->assertTrue($char->isUpper());

        $this->assertFalse($char->isLetter());
        $this->assertFalse($char->isDigit());
        $this->assertFalse($char->isSpace());
        $this->assertFalse($char->isControl());
        $this->assertFalse($char->isNull());

        $this->assertEquals($testChar, (string)$char);  // Expect: '-'
        $this->assertEquals($testChar, (string)$char->toUpper()); // Expect: '-'
        $this->assertEquals($testChar, (string)$char->toLower()); // Expect: '-'

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 45
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('2d')
        $this->assertEquals(
            $testChar,
            (string)Char::fromHex('2d')
        );

        $this->assertEquals($testChar, $char->toString()); // Expect: 45
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testChar, $char->toInt()); // Expect: 45
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testASCIIControl()
    {
        $testChar = chr(6);
        $testOrd  = 6;
        $char     = new Char($testChar); // (ASCII) [ACK]

        $this->assertTrue($char->isControl());
        $this->assertTrue($char->isLower());
        $this->assertTrue($char->isUpper());

        $this->assertFalse($char->isLetter());
        $this->assertFalse($char->isDigit());
        $this->assertFalse($char->isSpace());
        $this->assertFalse($char->isMark());
        $this->assertFalse($char->isPrintable());
        $this->assertFalse($char->isNull());

        $this->assertEquals($testChar, (string)$char);  // Expect: [ACK]
        $this->assertEquals($testChar, (string)$char->toUpper()); // Expect: [ACK]
        $this->assertEquals($testChar, (string)$char->toLower()); // Expect: [ACK]

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 6
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('06')
        $this->assertEquals(
            $testChar,
            (string)Char::fromHex('06')
        );

        $this->assertEquals($testChar, $char->toString()); // Expect: 6
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testChar, $char->toInt()); // Expect: 6
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testASCIINull()
    {
        $testChar = chr(0);
        $testOrd  = 0;
        $char     = new Char($testChar); // (ASCII) [NUL]

        $this->assertTrue($char->isControl());
        $this->assertTrue($char->isNull());
        $this->assertTrue($char->isLower());
        $this->assertTrue($char->isUpper());

        $this->assertFalse($char->isLetter());
        $this->assertFalse($char->isDigit());
        $this->assertFalse($char->isSpace());
        $this->assertFalse($char->isMark());
        $this->assertFalse($char->isPrintable());

        $this->assertEquals($testChar, (string)$char);  // Expect: [NUL]
        $this->assertEquals($testChar, (string)$char->toUpper()); // Expect: [NUL]
        $this->assertEquals($testChar, (string)$char->toLower()); // Expect: [NUL]

        $this->assertEquals($testOrd, $char->toAscii()); // Expect: 0
        $this->assertEquals($testOrd, hexdec($char->toHex())); // hexdec('00')
        $this->assertEquals(
            $testChar,
            (string)Char::fromHex('00')
        );

        $this->assertEquals($testChar, $char->toString()); // Expect: 0
        $this->assertIsString($char->toString());
        $this->assertEquals((int)$testChar, $char->toInt()); // Expect: 0
        $this->assertIsInt($char->toInt());
    }

    /**
     * @throws HelperException
     */
    public function testUnicode1Byte()
    {
        // Arbitrary value: first lowercase letter from English alphabet
        // (hex) "\x61": (ASCII) 'a'
        $this->assertEquals(
            static::calculateUTF8Ordinal("\x61"),
            Char::fromHex("61")->toUnicode()
        );

        // Lower bound: first available character
        // (hex) "\x00": (ASCII) 'NUL' (non-printable control character)
        $this->assertEquals(
            static::calculateUTF8Ordinal("\x00"),
            Char::fromHex("00")->toUnicode()
        );

        // Upper bound: last available character
        // (hex) "\x7F": (ASCII) 'DEL'
        $this->assertEquals(
            static::calculateUTF8Ordinal("\x7F"),
            Char::fromHex("7F")->toUnicode()
        );

        //
        // INVALID LEADING BYTE (< 0xC2)
        // e.g., 0x80 – 0xC1 should return false
        //
        $this->assertEquals(-1, Char::fromHex('80')->toUnicode());
        $this->assertEquals(-1, Char::fromHex('C1')->toUnicode());

        //
        // 2-BYTE UTF-8 (U+0080 – U+07FF)
        // Example: '¢' (U+00A2) → C2 A2
        //
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xC2\xA2"),
            Char::fromHex('C2A2')->toUnicode()
        );

        // Upper end of 2-byte range: '߿' (U+07FF) → DF BF
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xDF\xBF"),
            Char::fromHex('DFBF')->toUnicode()
        );

        //
        // 3-BYTE UTF-8 (U+0800 – U+FFFF)
        // Example: '€' (U+20AC) → E2 82 AC
        //
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xE2\x82\xAC"),
            Char::fromHex('E282AC')->toUnicode()
        );

        // Upper end of 3-byte range: '￿' (U+FFFF) → EF BF BF
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xEF\xBF\xBF"),
            Char::fromHex('EFBFBF')->toUnicode()
        );

        //
        // 4-BYTE UTF-8 (U+10000 – U+10FFFF)
        // Example: '😀' (U+1F600) → F0 9F 98 80
        //
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xF0\x9F\x98\x80"),
            Char::fromHex('F09F9880')->toUnicode()
        );

        // Upper end: U+10FFFF → F4 8F BF BF
        $this->assertEquals(
            static::calculateUTF8Ordinal("\xF4\x8F\xBF\xBF"),
            Char::fromHex('F48FBFBF')->toUnicode()
        );

        //
        // INVALID TOO-HIGH LEAD BYTE (> 0xF4)
        //
        $this->assertEquals(
            -1,
            Char::fromHex('F5')->toUnicode()
        );
    }

    /**
     * @throws HelperException
     */
    public function testUnicodeRejectsTruncatedUtf8Sequence(): void
    {
        $this->assertSame(-1, Char::fromHex('C2')->toUnicode());
    }

    /**
     * @throws HelperException
     */
    public function testFromHexToHexRoundTripsMultibyteUtf8Characters(): void
    {
        $this->assertSame('C2A2', Char::fromHex('C2A2')->toHex());
        $this->assertSame('E282AC', (new Char('€'))->toHex());
        $this->assertSame('F09F9880', (new Char('😀'))->toHex());
    }

    public function testFromHexRejectsMalformedInput(): void
    {
        $this->expectException(HelperException::class);

        Char::fromHex('zz');
    }

    /**
     * @throws HelperException
     */
    // @ToDo: Enable tests after updating TeamSpeak3_Helper_Char Unicode Support
    /*
    public function testUnicode2Bytes() {
      // Arbitrary value: first lowercase letter from English alphabet
      // (hex) "\x61": (ASCII) 'a'
      $this->assertEquals(
        static::calculateUTF8Ordinal("\xC2\x80"),
        (new \TeamSpeak3_Helper_Char("\xC2\x80"))->toUnicode()
      );

      // Lower bound: first available character
      // (hex) "\x00": (ASCII) 'NUL' (non-printable control character)
      $this->assertEquals(
        static::calculateUTF8Ordinal("\xC2\x80"),
        (new \TeamSpeak3_Helper_Char("\xC2\x80"))->toUnicode()
      );

      // Upper bound: last available character
      // (hex) "\x7F": (ASCII) 'DEL'
      $this->assertEquals(
        static::calculateUTF8Ordinal("\xC2\x80"),
        (new \TeamSpeak3_Helper_Char("\xC2\x80"))->toUnicode()
      );
    }
    */

    /**
     * Return integer value of a string, specifically for UTF8 strings.
     *
     * @param string $char
     *
     * @return int
     */
    private static function calculateUTF8Ordinal(string $char): int
    {
        $bytes = array_map('ord', str_split($char));
        $length = strlen($char);

        if ($length === 1) {
            // 1-byte (ASCII)
            return $bytes[0];
        } elseif ($length === 2) {
            // 2-byte
            return (($bytes[0] & 0x1F) << 6) |
                ($bytes[1] & 0x3F);
        } elseif ($length === 3) {
            // 3-byte
            return (($bytes[0] & 0x0F) << 12) |
                (($bytes[1] & 0x3F) << 6) |
                ($bytes[2] & 0x3F);
        } elseif ($length === 4) {
            // 4-byte
            return (($bytes[0] & 0x07) << 18) |
                (($bytes[1] & 0x3F) << 12) |
                (($bytes[2] & 0x3F) << 6) |
                ($bytes[3] & 0x3F);
        }

        // invalid UTF-8 (longer than 4 bytes)
        return -1;
    }
}
