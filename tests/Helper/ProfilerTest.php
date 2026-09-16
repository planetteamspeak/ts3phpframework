<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Tests\Helper;

use PHPUnit\Framework\TestCase;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler\Timer;

class ProfilerTest extends TestCase
{
    public function testTimerTracksRuntimeAndMemoryAcrossLifecycle(): void
    {
        $timer = new Timer('unit-test');
        $this->assertTrue($timer->isRunning());
        $this->assertGreaterThanOrEqual(0, $timer->getRuntime());
        $this->assertTrue($timer->isRunning());

        $timer->stop();
        $this->assertFalse($timer->isRunning());
        $this->assertGreaterThanOrEqual(0, $timer->getMemUsage());
        $this->assertGreaterThanOrEqual(0, $timer->getMemUsage(true));

        $timer->start();
        $this->assertTrue($timer->isRunning());
    }

    public function testProfilerCreatesStartsStopsAndReturnsNamedTimers(): void
    {
        Profiler::init('named');
        $timer = Profiler::get('named');
        $this->assertInstanceOf(Timer::class, $timer);

        Profiler::stop('named');
        $this->assertFalse($timer->isRunning());
        Profiler::start('named');
        $this->assertTrue($timer->isRunning());

        Profiler::stop('created-on-stop');
        $this->assertInstanceOf(Timer::class, Profiler::get('created-on-stop'));
    }

    public function testProfilerRemovesTimers(): void
    {
        Profiler::init('removed');
        Profiler::remove('removed');

        $property = new \ReflectionProperty(Profiler::class, 'timers');
        $this->assertArrayNotHasKey('removed', $property->getValue());
    }
}
