<?php

declare(strict_types = 1);

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
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

use Cerberus\Formatter\FormatterFactory;
use Cerberus\Output\Console;

/**
 * Class Bot
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://tools.ietf.org/html/rfc1459 Internet Relay Chat: Client Protocol - RFC1459
 * @link https://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol - RFC2812
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Bot
{
    private $botId = 0;
    
    private $param = null;

    private $caller = null;
    private $config = null;
    private $console = null;
    private $database = null;
    private $irc = null;
    private $system = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        set_time_limit(0);
        error_reporting(-1);
        date_default_timezone_set('Europe/Berlin');
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline, array $errcontext) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
        $this->setSystem(new System);
        $this->setCaller(new Caller);
        //$this->setConfig(new Config);
        $this->setConsole(new Console($this, FormatterFactory::console()));
        $this->setDatabase(new Database($this));
    }

    /**
     * run me as main method
     */
    public function run()
    {
        $this->getConsole()->writeln('<error>test</error>');
    }
    
    /**
     * @param $id int
     */
    public function setBotId($id)
    {
        $this->botId = $id;
    }
    
    /**
     * @return int
     */
    public function getBotId(): int
    {
        return $this->botId;
    }
    
    /**
     * @param array $param
     */
    public function setParam($param)
    {
        var_dump($param);
        $count = count($param);
        for ($i = 1; $i < $count; $i++) {
            $parts = explode('=', $param[$i]);
            $this->param[$parts[0]] = $parts[1] ?? '';
        }
    }
    
    /**
     * @return mixed
     */
    public function getParam($name)
    {
        $value = null;
        if (isset($this->param[$name])) {
            $value = $this->param[$name];
        }
        return $value;
    }
    
    /**
     * @param System $system
     */
    public function setSystem(System $system)
    {
        $this->system = $system;
    }
    
    /**
     * @return System
     */
    public function getSystem(): System
    {
        return $this->system;
    }

    /**
     * @param Database $database
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }
    
    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }
    
    /**
     * @param Console $console
     */
    public function setConsole(Console $console)
    {
        $this->console = $console;
    }
    
    /**
     * @return Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }
    
    /**
     * @param Caller $caller
     */
    public function setCaller(Caller $caller)
    {
        $this->caller = $caller;
    }
    
    /**
     * @return Caller
     */
    public function getCaller(): Caller
    {
        return $this->caller;
    }
    
    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Irc $irc
     */
    public function setIrc(Irc $irc)
    {
        $this->irc = $irc;
    }

    /**
     * @return Irc
     */
    public function getIrc(): Irc
    {
        return $this->irc;
    }
}
