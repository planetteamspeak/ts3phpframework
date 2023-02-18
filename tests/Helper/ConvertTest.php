<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;

class ConvertTest extends TestCase
{
    public function testConvertBytesToHumanReadableWithFactor1000()
    {
        $output = Convert::bytes(0);
        $this->assertEquals('0 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000);
        $this->assertEquals('1000 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000*1000);
        $this->assertEquals('976.5625 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000*1000*1000);
        $this->assertEquals('953.6743164063 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000*1000*1000*1000);
        $this->assertEquals('931.3225746155 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000*1000*1000*1000*1000);
        $this->assertEquals('909.4947017729 TiB', $output);
        $this->assertIsString($output);
    }

    public function testConvertBytesToHumanReadableWithFactor1024()
    {
        $output = Convert::bytes(0);
        $this->assertEquals('0 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024);
        $this->assertEquals('1 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024);
        $this->assertEquals('1 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024);
        $this->assertEquals('1 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024*1024);
        $this->assertEquals('1 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024*1024*1024);
        $this->assertEquals('1 PiB', $output);
        $this->assertIsString($output);
    }

    public function testConvertBytesToHumanReadableWithOddNumbers()
    {
        $output = Convert::bytes(1);
        $this->assertEquals('1 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024+256);
        $this->assertEquals('1.25 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024+256);
        $this->assertEquals('1.0002441406 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024+256);
        $this->assertEquals('1.0000002384 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024*1024+256);
        $this->assertEquals('1.0000000002 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024*1024*1024*1024*1024+(256*1024*1024*1024));
        $this->assertEquals('1.0002441406 PiB', $output);
        $this->assertIsString($output);
    }

    public function testConvertBytesToHumanReadableWithNegativeNumbers()
    {
        $output = Convert::bytes(0);
        $this->assertEquals('0 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1000);
        $this->assertEquals('-1000 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024);
        $this->assertEquals('-1 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1000*1000);
        $this->assertEquals('-976.5625 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1000*1000*1000);
        $this->assertEquals('-953.6743164063 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024*1024);
        $this->assertEquals('-1 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024*1024*1024);
        $this->assertEquals('-1 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024*1024*1024*1024);
        $this->assertEquals('-1 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024*1024*1024*1024-256);
        $this->assertEquals('-1.0000000002 TiB', $output);
        $this->assertIsString($output);
    }

    public function testConvertSecondsToHumanReadable()
    {
        $output = Convert::seconds(0);
        $this->assertEquals('0D 00:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(1);
        $this->assertEquals('0D 00:00:01', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(59);
        $this->assertEquals('0D 00:00:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(60);
        $this->assertEquals('0D 00:01:00', $output);
        $this->assertIsString($output);


        $output = Convert::seconds((59*60) + 59);
        $this->assertEquals('0D 00:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds((59*60) + 60);
        $this->assertEquals('0D 01:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            (23*(60**2)) + (59*60) + 59
        );
        $this->assertEquals('0D 23:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            (23*(60**2)) + (59*60) + 60
        );
        $this->assertEquals('1D 00:00:00', $output);
        $this->assertIsString($output);


        $output = Convert::seconds(
            (47*(60**2)) + (59*60) + 59
        );
        $this->assertEquals('1D 23:59:59', $output);
        $this->assertIsString($output);

        // @todo: Enable after ::seconds() can handle negative integers
    //$output = Convert::seconds(-1);
    //$this->assertEquals('-0D 00:00:01', $output);
    //$this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
    }

    public function testConvertCodecIDToHumanReadable()
    {
        // @todo: Find logical / comprehensive test for checking codec names
    }

    public function testConvertGroupTypeIDToHumanReadable()
    {
        // @todo: Find logical / comprehensive test for checking codec names
    }

    public function testConvertPermTypeIDToHumanReadable()
    {
        // @todo: Find logical / comprehensive test for checking codec names
    }

    public function testConvertPermCategoryIDToHumanReadable()
    {
        // @todo: Find logical / comprehensive test for checking codec names
    }

    public function testConvertLogLevelIDToHumanReadable()
    {
        // @todo: Find logical / comprehensive test for checking codec names
    }

    public function testConvertLogEntryToArray()
    {
        // @todo: Implement matching integration test for testing real log entries
        $mock_data = [
      '2017-06-26 21:55:30.307009|INFO    |Query         |   |query from 47 [::1]:62592 issued: login with account "serveradmin"(serveradmin)'
    ];

        foreach ($mock_data as $entry) {
            $entryParsed = Convert::logEntry($entry);
            $this->assertFalse(
                $entryParsed['malformed'],
                'Log entry appears malformed, dumping: '.print_r($entryParsed, true)
            );
        }
    }

    public function testConvertToPassword()
    {
        $this->assertEquals(
            'W6ph5Mm5Pz8GgiULbPgzG37mj9g=',
            Convert::password('password')
        );
    }

    public function testConvertVersionToClientFormat()
    {
        $this->assertEquals(
            '3.0.13.6 (2016-11-08 08:48:33)',
            Convert::version('3.0.13.6 [Build: 1478594913]')
        );
    }

    public function testConvertVersionShortToClientFormat()
    {
        $this->assertEquals(
            '3.0.13.6',
            Convert::versionShort('3.0.13.6 [Build: 1478594913]')
        );
    }

    public function testDetectImageMimeType()
    {
        // Test image binary base64 encoded is 1px by 1px GIF
        $this->assertEquals(
            'image/gif',
            Convert::imageMimeType(
            base64_decode('R0lGODdhAQABAIAAAPxqbAAAACwAAAAAAQABAAACAkQBADs=')
        )
        );
    }
}
