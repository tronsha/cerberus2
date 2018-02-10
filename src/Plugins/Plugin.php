<?php

declare(strict_types=1);

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2018 Stefan Hüsges
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Cerberus\Plugins;

use Cerberus\Bot;

/**
 * Class Plugin
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
abstract class Plugin
{
    /**
     * @var Bot
     */
    private $bot = null;

    /**
     * @param Bot $bot
     */
    public function __construct(Bot $bot = null)
    {
        $this->setBot($bot);
    }

    /**
     * @param Bot $bot
     */
    public function setBot($bot)
    {
        $this->bot = $bot;
    }
    
    /**
     * @return Bot
     */
    public function getBot(): Bot
    {
        return $this->bot;
    }
    
    /**
     * @param string $event
     * @param string|null $method
     * @param int $priority
     * @throws Exception
     */
    protected function addEvent($event, $method = null, $priority = 5)
    {
        try {
            $method = (null === $method ? $event : $method);
            if (false === in_array($method, get_class_methods($this), true)) {
                throw new Exception('The method ' . $method . ' not exists in the class.');
            }
            $this->getBot()->getEvents()->addPluginEvent($event, $this, $method, $priority);
        } catch (\Throwable $e) {
            $this->getBot()->getSystem()->getLogger()->error($e->getMessage(), ['event' => $event, 'method' => $method]);
        }
    }

    /**
     * @param string $event
     */
    protected function removeEvent($event)
    {
        //$this->getBot()->getEvents()->removePluginEvent($event, $this);
    }
}
