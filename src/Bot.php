<?php

declare(strict_types = 1);

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

use Cerberus\Formatter\FormatterFactory;
use Cerberus\Output\Console;
use ErrorException;

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
    private $startTime = null;

    private $param = null;

    private $auth = null;
    private $caller = null;
    private $config = null;
    private $console = null;
    private $cron = null;
    private $database = null;
    private $events = null;
    private $irc = null;
    private $system = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->startTime = microtime(true);
        set_time_limit(0);
        error_reporting(-1);
        date_default_timezone_set('Europe/Berlin');
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline, array $errcontext) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
        $this->setSystem(new System);
        $this->setCron(new Cron);
        $this->setCaller(new Caller($this));
        $this->setConfig(new Config);
        $this->setConsole(new Console($this, FormatterFactory::console()));
        $this->setDatabase(new Database($this));
        $this->setAuth(new Auth($this));
        $this->setIrc(new Irc($this));
        $this->setEvents(new Events($this));
    }

    /**
     * run me as main method
     */
    public function run()
    {
        $this->getIrc()->run();
        $output = vsprintf('Execute time: %.5fs', microtime(true) - $this->startTime);
        $this->getConsole()->writeln('<info>' . $output . '</info>');
    }

    /**
     * @param $id int
     *
     * @return \Cerberus\Bot
     */
    public function setBotId($id): Bot
    {
        $this->botId = $id;

        return $this;
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
     *
     * @return \Cerberus\Bot
     */
    public function setParam($param): Bot
    {
        $count = count($param);
        for ($i = 1; $i < $count; $i++) {
            $parts = explode('=', $param[$i]);
            $this->param[$parts[0]] = $parts[1] ?? '';
        }

        return $this;
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
     *
     * @return \Cerberus\Bot
     */
    public function setSystem(System $system): Bot
    {
        $this->system = $system;

        return $this;
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
     *
     * @return \Cerberus\Bot
     */
    public function setDatabase(Database $database): Bot
    {
        $this->database = $database;

        return $this;
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
     * 
     * @return \Cerberus\Bot
     */
    public function setConsole(Console $console): Bot
    {
        $this->console = $console;

        return $this;
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
     * @param Cron $cron
     */
    public function setCron(Cron $cron)
    {
        $this->cron = $cron;
    }

    /**
     * @return Cron
     */
    public function getCron(): Cron
    {
        return $this->cron;
    }

    /**
     * @param Events $events
     */
    public function setEvents(Events $events)
    {
        $this->events = $events;
    }

    /**
     * @return Events
     */
    public function getEvents(): Events
    {
        return $this->events;
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

    /**
     * @param Auth $auth
     */
    public function setAuth(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }
}
