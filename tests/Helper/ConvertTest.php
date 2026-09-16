<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Convert;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

class ConvertTest extends TestCase
{
    public function setUp(): void
    {
        date_default_timezone_set("UTC");
    }
    public function testConvertBytesToHumanReadableWithFactor1000()
    {
        $output = Convert::bytes(0);
        $this->assertEquals('0 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000);
        $this->assertEquals('1000 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000 * 1000);
        $this->assertEquals('976.5625 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000 * 1000 * 1000);
        $this->assertEquals('953.6743164063 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000 * 1000 * 1000 * 1000);
        $this->assertEquals('931.3225746155 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1000 * 1000 * 1000 * 1000 * 1000);
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

        $output = Convert::bytes(1024 * 1024);
        $this->assertEquals('1 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024);
        $this->assertEquals('1 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024 * 1024);
        $this->assertEquals('1 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024 * 1024 * 1024);
        $this->assertEquals('1 PiB', $output);
        $this->assertIsString($output);
    }

    public function testConvertBytesToHumanReadableWithOddNumbers()
    {
        $output = Convert::bytes(1);
        $this->assertEquals('1 B', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 + 256);
        $this->assertEquals('1.25 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 + 256);
        $this->assertEquals('1.0002441406 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024 + 256);
        $this->assertEquals('1.0000002384 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024 * 1024 + 256);
        $this->assertEquals('1.0000000002 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(1024 * 1024 * 1024 * 1024 * 1024 + (256 * 1024 * 1024 * 1024));
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

        $output = Convert::bytes(-1000 * 1000);
        $this->assertEquals('-976.5625 KiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1000 * 1000 * 1000);
        $this->assertEquals('-953.6743164063 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024 * 1024);
        $this->assertEquals('-1 MiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024 * 1024 * 1024);
        $this->assertEquals('-1 GiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024 * 1024 * 1024 * 1024);
        $this->assertEquals('-1 TiB', $output);
        $this->assertIsString($output);

        $output = Convert::bytes(-1024 * 1024 * 1024 * 1024 - 256);
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


        $output = Convert::seconds((59 * 60) + 59);
        $this->assertEquals('0D 00:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds((59 * 60) + 60);
        $this->assertEquals('0D 01:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            (23 * (60 ** 2)) + (59 * 60) + 59
        );
        $this->assertEquals('0D 23:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            (23 * (60 ** 2)) + (59 * 60) + 60
        );
        $this->assertEquals('1D 00:00:00', $output);
        $this->assertIsString($output);


        $output = Convert::seconds(
            (47 * (60 ** 2)) + (59 * 60) + 59
        );
        $this->assertEquals('1D 23:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(90.083);
        $this->assertEquals('0D 00:01:30', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(-0);
        $this->assertEquals('0D 00:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(-1);
        $this->assertEquals('-0D 00:00:01', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(-59);
        $this->assertEquals('-0D 00:00:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(-60);
        $this->assertEquals('-0D 00:01:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(((59 * 60) + 59) * -1);
        $this->assertEquals('-0D 00:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(((59 * 60) + 60) * -1);
        $this->assertEquals('-0D 01:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            ((23 * (60 ** 2)) + (59 * 60) + 59) * -1
        );
        $this->assertEquals('-0D 23:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            ((23 * (60 ** 2)) + (59 * 60) + 60) * -1
        );
        $this->assertEquals('-1D 00:00:00', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(
            ((47 * (60 ** 2)) + (59 * 60) + 59) * -1
        );
        $this->assertEquals('-1D 23:59:59', $output);
        $this->assertIsString($output);

        $output = Convert::seconds(-90.083);
        $this->assertEquals('-0D 00:01:30', $output);
        $this->assertIsString($output);
    }

    public function testConvertCodecIDToHumanReadable()
    {
        foreach ([
            TeamSpeak3::CODEC_SPEEX_NARROWBAND => 'Speex Narrowband', TeamSpeak3::CODEC_SPEEX_WIDEBAND => 'Speex Wideband',
            TeamSpeak3::CODEC_SPEEX_ULTRAWIDEBAND => 'Speex Ultra-Wideband', TeamSpeak3::CODEC_CELT_MONO => 'CELT Mono',
            TeamSpeak3::CODEC_OPUS_VOICE => 'Opus Voice', TeamSpeak3::CODEC_OPUS_MUSIC => 'Opus Music',
        ] as $codec => $name) {
            $this->assertSame($name, Convert::codec($codec));
        }
        $this->assertSame('Unknown', Convert::codec(-1));
    }

    public function testConvertGroupTypeIDToHumanReadable()
    {
        foreach ([TeamSpeak3::GROUP_DBTYPE_TEMPLATE => 'Template', TeamSpeak3::GROUP_DBTYPE_REGULAR => 'Regular', TeamSpeak3::GROUP_DBTYPE_SERVERQUERY => 'ServerQuery'] as $type => $name) {
            $this->assertSame($name, Convert::groupType($type));
        }
        $this->assertSame('Unknown', Convert::groupType(-1));
    }

    public function testConvertPermTypeIDToHumanReadable()
    {
        foreach ([TeamSpeak3::PERM_TYPE_SERVERGROUP => 'Server Group', TeamSpeak3::PERM_TYPE_CLIENT => 'Client', TeamSpeak3::PERM_TYPE_CHANNEL => 'Channel', TeamSpeak3::PERM_TYPE_CHANNELGROUP => 'Channel Group', TeamSpeak3::PERM_TYPE_CHANNELCLIENT => 'Channel Client'] as $type => $name) {
            $this->assertSame($name, Convert::permissionType($type));
        }
        $this->assertSame('Unknown', Convert::permissionType(-1));
    }

    public function testConvertPermCategoryIDToHumanReadable()
    {
        foreach ([
            TeamSpeak3::PERM_CAT_GLOBAL => 'Global', TeamSpeak3::PERM_CAT_GLOBAL_INFORMATION => 'Global / Information', TeamSpeak3::PERM_CAT_GLOBAL_SERVER_MGMT => 'Global / Virtual Server Management', TeamSpeak3::PERM_CAT_GLOBAL_ADM_ACTIONS => 'Global / Administration', TeamSpeak3::PERM_CAT_GLOBAL_SETTINGS => 'Global / Settings',
            TeamSpeak3::PERM_CAT_SERVER => 'Virtual Server', TeamSpeak3::PERM_CAT_SERVER_INFORMATION => 'Virtual Server / Information', TeamSpeak3::PERM_CAT_SERVER_ADM_ACTIONS => 'Virtual Server / Administration', TeamSpeak3::PERM_CAT_SERVER_SETTINGS => 'Virtual Server / Settings',
            TeamSpeak3::PERM_CAT_CHANNEL => 'Channel', TeamSpeak3::PERM_CAT_CHANNEL_INFORMATION => 'Channel / Information', TeamSpeak3::PERM_CAT_CHANNEL_CREATE => 'Channel / Create', TeamSpeak3::PERM_CAT_CHANNEL_MODIFY => 'Channel / Modify', TeamSpeak3::PERM_CAT_CHANNEL_DELETE => 'Channel / Delete', TeamSpeak3::PERM_CAT_CHANNEL_ACCESS => 'Channel / Access',
            TeamSpeak3::PERM_CAT_GROUP => 'Group', TeamSpeak3::PERM_CAT_GROUP_INFORMATION => 'Group / Information', TeamSpeak3::PERM_CAT_GROUP_CREATE => 'Group / Create', TeamSpeak3::PERM_CAT_GROUP_MODIFY => 'Group / Modify', TeamSpeak3::PERM_CAT_GROUP_DELETE => 'Group / Delete',
            TeamSpeak3::PERM_CAT_CLIENT => 'Client', TeamSpeak3::PERM_CAT_CLIENT_INFORMATION => 'Client / Information', TeamSpeak3::PERM_CAT_CLIENT_ADM_ACTIONS => 'Client / Admin', TeamSpeak3::PERM_CAT_CLIENT_BASICS => 'Client / Basics', TeamSpeak3::PERM_CAT_CLIENT_MODIFY => 'Client / Modify', TeamSpeak3::PERM_CAT_FILETRANSFER => 'File Transfer', TeamSpeak3::PERM_CAT_NEEDED_MODIFY_POWER => 'Grant',
        ] as $category => $name) {
            $this->assertSame($name, Convert::permissionCategory($category));
        }
        $this->assertSame('Unknown', Convert::permissionCategory(-1));
    }

    public function testConvertLogLevelIDToHumanReadable()
    {
        foreach ([TeamSpeak3::LOGLEVEL_CRITICAL => 'CRITICAL', TeamSpeak3::LOGLEVEL_ERROR => 'ERROR', TeamSpeak3::LOGLEVEL_DEBUG => 'DEBUG', TeamSpeak3::LOGLEVEL_WARNING => 'WARNING', TeamSpeak3::LOGLEVEL_INFO => 'INFO'] as $level => $name) {
            $this->assertSame($name, Convert::logLevel($level));
            $this->assertEquals($level, Convert::logLevel(strtolower($name)));
        }
        $this->assertSame('DEVELOP', Convert::logLevel(-1));
        $this->assertEquals(TeamSpeak3::LOGLEVEL_ERROR, Convert::logLevel('error'));
        $this->assertEquals(TeamSpeak3::LOGLEVEL_DEVEL, Convert::logLevel('unexpected'));
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
            Convert::version('3.0.13.6 [Build: 1478594913]')->toString()
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
