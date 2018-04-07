<?php

use PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;

require_once('lib/TeamSpeak3/Helper/Convert.php');

class ConvertTest extends TestCase
{
  public function testConvertBytesToHumanReadable() {
    $output = \TeamSpeak3_Helper_Convert::bytes(0);
    $this->assertEquals('0 B', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1);
    $this->assertEquals('1 B', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1018);
    $this->assertEquals('1018 B', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1019);
    $this->assertEquals('1.00 KB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1024);
    $this->assertEquals('1.00 KB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1029);
    $this->assertEquals('1.00 KB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(1030);
    $this->assertEquals('1.01 KB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
    
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes((1024**2 - (5*1024) - 118));
    $this->assertEquals('1018.88 KB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes((1024**2 - (5*1024) - 117));
    $this->assertEquals('1.00 MB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
    
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes(
      (1024**3 - (5*(1024**2)) - 1024*117-774));
    $this->assertEquals('1018.88 MB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes(
      (1024**3 - (5*(1024**2)) - 1024*117-773));
    $this->assertEquals('1.00 GB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
    
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes(
      (1024**4 - (5*(1024**3)) - (1024**2)*117-1024*773 - 118));
    $this->assertEquals('1018.88 GB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    // Note: Strange offset is due to ::bytes() rounding imprecision
    $output = \TeamSpeak3_Helper_Convert::bytes(
      (1024**4 - (5*(1024**3)) - (1024**2)*117-1024*773 - 117));
    $this->assertEquals('1.00 TB', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(-1);
    $this->assertEquals('-1 B', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::bytes(-1023);
    $this->assertEquals('-1023 B', $output); 
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    // @todo: Enable once ::bytes() can handle negatives values >= 1024
    //$output = \TeamSpeak3_Helper_Convert::bytes(-1024);
    //$this->assertEquals('-1.00 KB', $output);
    //$this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
  }
  
  public function testConvertSecondsToHumanReadable() {
    $output = \TeamSpeak3_Helper_Convert::seconds(0);
    $this->assertEquals('0D 00:00:00', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds(1);
    $this->assertEquals('0D 00:00:01', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds(59);
    $this->assertEquals('0D 00:00:59', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds(60);
    $this->assertEquals('0D 00:01:00', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
  
    $output = \TeamSpeak3_Helper_Convert::seconds((59*60) + 59);
    $this->assertEquals('0D 00:59:59', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds((59*60) + 60);
    $this->assertEquals('0D 01:00:00', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds(
      (23*(60**2)) + (59*60) + 59);
    $this->assertEquals('0D 23:59:59', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    $output = \TeamSpeak3_Helper_Convert::seconds(
      (23*(60**2)) + (59*60) + 60);
    $this->assertEquals('1D 00:00:00', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  
    
    $output = \TeamSpeak3_Helper_Convert::seconds(
      (47*(60**2)) + (59*60) + 59);
    $this->assertEquals('1D 23:59:59', $output);
    $this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
    
    // @todo: Enable after ::seconds() can handle negative integers
    //$output = \TeamSpeak3_Helper_Convert::seconds(-1);
    //$this->assertEquals('-0D 00:00:01', $output);
    //$this->assertInternalType(PHPUnit_IsType::TYPE_STRING, $output);
  }
  
  public function testConvertCodecIDToHumanReadable() {
    // @todo: Find logical / comprehensive test for checking codec names
  }
  
  public function testConvertGroupTypeIDToHumanReadable() {
    // @todo: Find logical / comprehensive test for checking codec names
  }
  
  public function testConvertPermTypeIDToHumanReadable() {
    // @todo: Find logical / comprehensive test for checking codec names
  }
  
  public function testConvertPermCategoryIDToHumanReadable() {
    // @todo: Find logical / comprehensive test for checking codec names
  }
  
  public function testConvertLogLevelIDToHumanReadable() {
    // @todo: Find logical / comprehensive test for checking codec names
  }
  
  public function testConvertLogEntryToArray() {
    // @todo: Implement matching integration test for testing real log entries
    $mock_data = [
      '2017-06-26 21:55:30.307009|INFO    |Query         |   |query from 47 [::1]:62592 issued: login with account "serveradmin"(serveradmin)'
    ];
    
    foreach($mock_data as $entry) {
      $entryParsed = \TeamSpeak3_Helper_Convert::logEntry($entry);
      $this->assertFalse(
        $entryParsed['malformed'], 
        'Log entry appears malformed, dumping: '.print_r($entryParsed, TRUE));
    }
  }
  
  public function testConvertToPassword() {
    $this->assertEquals(
      'W6ph5Mm5Pz8GgiULbPgzG37mj9g=',
      \TeamSpeak3_Helper_Convert::password('password'));
  }
  
  public function testConvertVersionToClientFormat() {
    $this->assertEquals(
      '3.0.13.6 (2016-11-08 08:48:33)',
      \TeamSpeak3_Helper_Convert::version('3.0.13.6 [Build: 1478594913]'));
  }
  
  public function testConvertVersionShortToClientFormat() {
    $this->assertEquals(
      '3.0.13.6',
      \TeamSpeak3_Helper_Convert::versionShort('3.0.13.6 [Build: 1478594913]'));
  }
  
  public function testDetectImageMimeType() {
    // Test image binary base64 encoded is 1px by 1px GIF
    $this->assertEquals(
      'image/gif',
      \TeamSpeak3_Helper_Convert::imageMimeType(
        base64_decode('R0lGODdhAQABAIAAAPxqbAAAACwAAAAAAQABAAACAkQBADs=')));
  }
}