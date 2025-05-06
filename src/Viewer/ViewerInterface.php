<?php

namespace PlanetTeamSpeak\TeamSpeak3Framework\Viewer;

use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;

/**
 * @class PlanetTeamSpeak\TeamSpeak3Framework\Viewer\ViewerInterface
 * @brief Interface class describing a TeamSpeak 3 viewer.
 */
interface ViewerInterface
{
    /**
     * Returns the code needed to display a node in a TeamSpeak 3 viewer.
     *
     * @param Node $node
     * @param array $siblings
     * @return string
     */
    public function fetchObject(Node $node, array $siblings = []): string;
}
