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

namespace Cerberus;

use Cerberus\Plugins\Plugin;

/**
 * Class Event
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Events
{
    /**
     * @var Bot
     */
    private $bot = null;
    
    /**
     * @var array
     */
    private $eventList = null;
    
    /**
     * @var array
     */
    private $pluginEvents = [];

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
     * @return \Cerberus\Bot
     */
    public function getBot(): Bot
    {
        return $this->bot;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return array
     */
    public function __call(string $name, array $arguments)
    {
        try {
            $return['event'] = $this->getBot()->getCaller()->call('\\Cerberus\\Events\\Event', $name, $arguments);
            $return['plugins'] = $this->runPluginEvent($name, $arguments);
            return $return;
        } catch (\Throwable $e) {
            $this->getBot()->getSystem()->getLogger()->error($e->getMessage(), ['name' => $name, 'arguments' => $arguments]);
        }
    }

    /**
     * @return array
     */
    public function getEventList(): array
    {
        if (null !== $this->eventList) {
            return $this->eventList;
        }
        $listClasses = [];
        $dir = $this->getBot()->getSystem()->getPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Events' . DIRECTORY_SEPARATOR;
        $files = glob($dir . 'EventOn*.php');
        foreach ($files as $file) {
            $listClasses[] = lcfirst(str_replace([$dir . 'Event' , '.php'], '', $file));
        }
        $listThis = get_class_methods($this);
        $list = array_merge($listClasses, $listThis);
        foreach ($list as $key => $value) {
            if ('on' !== substr($value, 0, 2)) {
                unset($list[$key]);
            }
        }
        $this->eventList = $list;
        return $this->eventList;
    }

    /**
     * @param string $event
     * @param Plugin $object
     * @param string|null $method
     * @param int $priority
     * @throws Exception
     */
    public function addPluginEvent(string $event, Plugin $object, string $method = null, int $priority = 5)
    {
        if (false === in_array($event, $this->getEventList(), true)) {
            throw new Exception('The event ' . $event . ' not exists.');
        }
        $method = (null === $method ? $event : $method);
        $pluginArray = ['object' => $object, 'method' => $method];
        $this->pluginEvents[$event][$priority][] = $pluginArray;
    }

    /**
     * @param string $event
     * @param array $data
     * @throws Exception
     * @return array
     */
    public function runPluginEvent(string $event, array $data)
    {
        if (true === array_key_exists($event, $this->pluginEvents)) {
            $results = [];
            for ($priority = 10; $priority > 0; $priority--) {
                if (true === array_key_exists($priority, $this->pluginEvents[$event])) {
                    foreach ($this->pluginEvents[$event][$priority] as $pluginArray) {
                        $pluginObject = $pluginArray['object'];
                        $pluginMethod = $pluginArray['method'];
                        if (true === method_exists($pluginObject, $pluginMethod)) {
                            $results[] = $pluginObject->$pluginMethod($data);
                        } else {
                            throw new Exception('The Class ' . get_class($pluginObject) . ' has not the method ' . $pluginMethod . '.');
                        }
                    }
                }
            }
            return $results;
        }
    }
}
