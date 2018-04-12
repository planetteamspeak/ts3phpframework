<?php

require_once('libraries/TeamSpeak3/Exception.php');
require_once('libraries/TeamSpeak3/Helper/Exception.php');
require_once('libraries/TeamSpeak3/Helper/Signal.php');
require_once('libraries/TeamSpeak3/Helper/Signal/Handler.php');
require_once('libraries/TeamSpeak3/Helper/Signal/Exception.php');

use PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;

use \TeamSpeak3_Helper_Signal as TS3_Signal;
use \TeamSpeak3_Helper_Signal_Handler as TS3_Signal_Handler;

class SignalTest extends TestCase
{
  protected static $cTriggers;
  
  protected static $signal = 'notifyEvent';
  protected static $callback = __CLASS__ . '::onEvent';
  protected static $testString = '!@w~//{tI_8G77<qS+g*[Gb@u`pJ^2>rO*f=KS:8Yj';
  
  protected function setUp() {
    static::$cTriggers = [];
    foreach(TS3_Signal::getInstance()->getSignals() as $signal)
      TS3_Signal::getInstance()->clearHandlers($signal);
  }
  
  public function testGetInstance() {
    $snapshot = clone TS3_Signal::getInstance();
    $this->assertEquals($snapshot, TS3_Signal::getInstance());
    $this->assertEmpty(TS3_Signal::getInstance()->getSignals());
  }
  
  public function testGetCallbackHash() {
    $this->assertEquals(
      md5(static::$callback),
      TS3_Signal::getInstance()->getCallbackHash(static::$callback));
  }
  
  public function testGetCallbackHashException() {
    $this->expectException(TeamSpeak3_Helper_Signal_Exception::class);
    $this->expectExceptionMessage('invalid callback specified');
    TS3_Signal::getInstance()->getCallbackHash([]);
  }
  
  public function testSubscribe() {
    $snapshot = clone TS3_Signal::getInstance();
    $instSignal = TS3_Signal::getInstance();
    
    $signalHandler = $instSignal->subscribe(static::$signal, static::$callback);
    // Test state: returned TeamSpeak3_Helper_Signal_Handler
    $this->assertInstanceOf(TS3_Signal_Handler::class, $signalHandler);
    $this->assertNotEquals($snapshot, TS3_Signal::getInstance());
  
    // Test state: subscribed signals
    $signals  = $instSignal->getSignals();
    $this->assertInternalType(PHPUnit_IsType::TYPE_ARRAY, $signals);
    $this->assertEquals(1, count($signals));
    $this->assertEquals(static::$signal, $signals[0]);
  
    // Test state: subscribed signal handlers
    $handlers = $instSignal->getHandlers(static::$signal);
    $this->assertInternalType(PHPUnit_IsType::TYPE_ARRAY, $handlers);
    $this->assertEquals(1, count($handlers));
    $this->assertArrayHasKey(
      TS3_Signal::getInstance()->getCallbackHash(static::$callback),
      $handlers);
    $handler = $handlers[TS3_Signal::getInstance()->getCallbackHash(static::$callback)];
    $this->assertInstanceOf(TeamSpeak3_Helper_Signal_Handler::class, $handler); 
    $this->assertEquals($signalHandler, $handler);
  }
  
  public function testEmit() {
    $callbackHash = TS3_Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent');
    TS3_Signal::getInstance()->subscribe(static::$signal, static::$callback);
    $response = TS3_Signal::getInstance()->emit(static::$signal, static::$testString);
    $this->assertEquals(static::$testString, $response);
    $this->assertInternalType(gettype(static::$testString), $response);
    
    // Verify correct count of callback executions
    $this->assertEquals(1, count(static::$cTriggers));
    $this->assertEquals(
      '0-'.static::$testString,
      static::$cTriggers[$callbackHash]);
  }
  
  public function testSubscribeTwo() {
    $instSignal = TS3_Signal::getInstance();
    $signalHandler1 = $instSignal->subscribe(
      static::$signal, static::$callback);
    $signalHandler2 = $instSignal->subscribe(
      static::$signal, static::$callback.'2');
  
    // Test state: subscribed signals
    $signals = $instSignal->getSignals();
    $this->assertEquals(1, count($signals));
    $this->assertEquals(static::$signal, $signals[0]);
  
    // Test state: subscribed signal handlers
    $handlers = $instSignal->getHandlers(static::$signal);
    $this->assertEquals(2, count($handlers));
    $this->assertArrayHasKey(
      $instSignal->getCallbackHash(static::$callback),
      $handlers);
    $this->assertArrayHasKey(
      $instSignal->getCallbackHash(static::$callback.'2'),
      $handlers);
    
    $handler1 = $handlers[$instSignal->getCallbackHash(static::$callback)];
    $this->assertEquals($signalHandler1, $handler1);
    $handler2 = $handlers[$instSignal->getCallbackHash(static::$callback.'2')];
    $this->assertEquals($signalHandler2, $handler2);
  }
  
  public function testEmitToTwoSubscribers() {
    $instSignal = TS3_Signal::getInstance();
    $callbackHash1 = $instSignal->getCallbackHash(__CLASS__ . '::onEvent');
    $callbackHash2 = $instSignal->getCallbackHash(__CLASS__ . '::onEvent2');
    
    $instSignal->subscribe(static::$signal, static::$callback);
    $instSignal->subscribe(static::$signal, static::$callback.'2');
  
    $response = $instSignal->emit(static::$signal, static::$testString);
    $this->assertEquals(static::$testString, $response);
    $this->assertInternalType(gettype(static::$testString), $response);
  
    // Verify correct count of callback executions
    $this->assertEquals(2, count(static::$cTriggers));
    $this->assertEquals(
      '0-' . static::$testString,
      static::$cTriggers[$callbackHash1]);
    $this->assertEquals(
      '1-' . static::$testString,
      static::$cTriggers[$callbackHash2]);
  }

  public static function onEvent($data) {
    $signature = TS3_Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent');
    
    static::$cTriggers[$signature] = count(static::$cTriggers).'-'.$data;
    return $data;
  }
  
  public static function onEvent2($data) {
    $signature = TS3_Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent2');
  
    static::$cTriggers[$signature] = count(static::$cTriggers).'-'.$data;
    return $data;
  }
}

