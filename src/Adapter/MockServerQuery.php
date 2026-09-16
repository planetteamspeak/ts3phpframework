<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Adapter;

use PlanetTeamSpeak\TeamSpeak3Framework\Exception\AdapterException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Profiler;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;
use PlanetTeamSpeak\TeamSpeak3Framework\Transport\MockTCP;

class MockServerQuery extends ServerQuery
{
    /**
     * Connects the Transport object and performs initial actions on the remote
     * server.
     *
     * @return void
     * @throws AdapterException
     */
    protected function syn(): void
    {
        $this->initTransport($this->options, MockTCP::class);
        $this->transport->setAdapter($this);

        Profiler::init(spl_object_hash($this));

        $rdy = $this->getTransport()->readLine();

        $this->validateProtocolGreeting($rdy);

        Signal::getInstance()->emit("serverqueryConnected", $this);
    }
}
