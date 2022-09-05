<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   TeamSpeak3
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) Planet TeamSpeak. All rights reserved.
 */

namespace PlanetTeamSpeak\TeamSpeak3Framework\Viewer;

use PlanetTeamSpeak\TeamSpeak3Framework\Helper\StringHelper;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Node;

/**
 * @class Text
 * @package PlanetTeamSpeak\TeamSpeak3Framework\Viewer
 * @brief Renders nodes used in ASCII-based TeamSpeak 3 viewers.
 */
class Text implements ViewerInterface
{
    /**
     * A pre-defined pattern used to display a node in a TeamSpeak 3 viewer.
     *
     * @var string
     */
    protected string $pattern = "%0%1 %2\n";

    /**
     * Returns the code needed to display a node in a TeamSpeak 3 viewer.
     *
     * @param Node $node
     * @param array $siblings
     * @return string
     */
    public function fetchObject(Node $node, array $siblings = []): string
    {
        $this->currObj = $node;
        $this->currSib = $siblings;

        $args = [
            $this->getPrefix(),
            $this->getCorpusIcon(),
            $this->getCorpusName(),
        ];

        return StringHelper::factory($this->pattern)->arg($args);
    }

    /**
     * Returns the ASCII string to display the prefix of the current node.
     *
     * @return string
     */
    protected function getPrefix(): string
    {
        $prefix = "";

        if (count($this->currSib)) {
            $last = array_pop($this->currSib);

            foreach ($this->currSib as $sibling) {
                $prefix .= ($sibling) ? "| " : "  ";
            }

            $prefix .= ($last) ? "\\-" : "|-";
        }

        return $prefix;
    }

    /**
     * Returns an ASCII string which can be used to display the status icon for a
     * TeamSpeak_Node_Abstract object.
     *
     * @return string
     */
    protected function getCorpusIcon(): string
    {
        return $this->currObj->getSymbol();
    }

    /**
     * Returns a string for the current corpus element which contains the display name
     * for the current Node object.
     *
     * @return string
     */
    protected function getCorpusName(): string
    {
        return $this->currObj;
    }
}
