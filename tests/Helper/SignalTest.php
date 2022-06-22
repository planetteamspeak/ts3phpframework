<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\Constraint\IsType as PHPUnit_IsType;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\HelperException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\SignalException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal\Handler;

class SignalTest extends TestCase
{
    protected static $cTriggers;

    protected static $signal = 'notifyEvent';
    protected static $callback = __CLASS__ . '::onEvent';
    protected static $testString = '!@w~//{tI_8G77<qS+g*[Gb@u`pJ^2>rO*f=KS:8Yj';

    protected function setUp(): void
    {
        static::$cTriggers = [];
        foreach (Signal::getInstance()->getSignals() as $signal) {
            Signal::getInstance()->clearHandlers($signal);
        }
    }

    public function testGetInstance()
    {
        $snapshot = clone Signal::getInstance();
        $this->assertEquals($snapshot, Signal::getInstance());
        $this->assertEmpty(Signal::getInstance()->getSignals());
    }

    public function testGetCallbackHash()
    {
        $this->assertEquals(
            md5(static::$callback),
            Signal::getInstance()->getCallbackHash(static::$callback)
        );
    }

    public function testGetCallbackHashException()
    {
        $this->expectException(SignalException::class);
        $this->expectExceptionMessage('invalid callback specified');
        Signal::getInstance()->getCallbackHash([]);
    }

    public function testSubscribe()
    {
        $snapshot = clone Signal::getInstance();
        $instSignal = Signal::getInstance();

        $signalHandler = $instSignal->subscribe(static::$signal, static::$callback);
        // Test state: returned TeamSpeak3_Helper_Handler
        $this->assertInstanceOf(Handler::class, $signalHandler);
        $this->assertNotEquals($snapshot, Signal::getInstance());

        // Test state: subscribed signals
        $signals  = $instSignal->getSignals();
        $this->assertIsArray($signals);
        $this->assertEquals(1, count($signals));
        $this->assertEquals(static::$signal, $signals[0]);

        // Test state: subscribed signal handlers
        $handlers = $instSignal->getHandlers(static::$signal);
        $this->assertIsArray($handlers);
        $this->assertEquals(1, count($handlers));
        $this->assertArrayHasKey(
            Signal::getInstance()->getCallbackHash(static::$callback),
            $handlers
        );
        $handler = $handlers[Signal::getInstance()->getCallbackHash(static::$callback)];
        $this->assertInstanceOf(Handler::class, $handler);
        $this->assertEquals($signalHandler, $handler);
    }

    public function testEmit()
    {
        $callbackHash = Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent');
        Signal::getInstance()->subscribe(static::$signal, static::$callback);
        $response = Signal::getInstance()->emit(static::$signal, static::$testString);
        $this->assertEquals(static::$testString, $response);
        $this->assertIsString(static::$testString);
        $this->assertIsString($response);

        // Verify correct count of callback executions
        $this->assertEquals(1, count(static::$cTriggers));
        $this->assertEquals(
            '0-'.static::$testString,
            static::$cTriggers[$callbackHash]
        );
    }

    public function testSubscribeTwo()
    {
        $instSignal = Signal::getInstance();
        $signalHandler1 = $instSignal->subscribe(
            static::$signal,
            static::$callback
        );
        $signalHandler2 = $instSignal->subscribe(
            static::$signal,
            static::$callback.'2'
        );

        // Test state: subscribed signals
        $signals = $instSignal->getSignals();
        $this->assertEquals(1, count($signals));
        $this->assertEquals(static::$signal, $signals[0]);

        // Test state: subscribed signal handlers
        $handlers = $instSignal->getHandlers(static::$signal);
        $this->assertEquals(2, count($handlers));
        $this->assertArrayHasKey(
            $instSignal->getCallbackHash(static::$callback),
            $handlers
        );
        $this->assertArrayHasKey(
            $instSignal->getCallbackHash(static::$callback.'2'),
            $handlers
        );

        $handler1 = $handlers[$instSignal->getCallbackHash(static::$callback)];
        $this->assertEquals($signalHandler1, $handler1);
        $handler2 = $handlers[$instSignal->getCallbackHash(static::$callback.'2')];
        $this->assertEquals($signalHandler2, $handler2);
    }

    public function testEmitToTwoSubscribers()
    {
        $instSignal = Signal::getInstance();
        $callbackHash1 = $instSignal->getCallbackHash(__CLASS__ . '::onEvent');
        $callbackHash2 = $instSignal->getCallbackHash(__CLASS__ . '::onEvent2');

        $instSignal->subscribe(static::$signal, static::$callback);
        $instSignal->subscribe(static::$signal, static::$callback.'2');

        $response = $instSignal->emit(static::$signal, static::$testString);
        $this->assertEquals(static::$testString, $response);
        $this->assertIsString(static::$testString);
        $this->assertIsString($response);

        // Verify correct count of callback executions
        $this->assertEquals(2, count(static::$cTriggers));
        $this->assertEquals(
            '0-' . static::$testString,
            static::$cTriggers[$callbackHash1]
        );
        $this->assertEquals(
            '1-' . static::$testString,
            static::$cTriggers[$callbackHash2]
        );
    }

    public static function onEvent($data)
    {
        $signature = Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent');

        static::$cTriggers[$signature] = count(static::$cTriggers).'-'.$data;
        return $data;
    }

    public static function onEvent2($data)
    {
        $signature = Signal::getInstance()
      ->getCallbackHash(__CLASS__ . '::onEvent2');

        static::$cTriggers[$signature] = count(static::$cTriggers).'-'.$data;
        return $data;
    }
}
