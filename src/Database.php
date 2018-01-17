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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Version;
use Exception;

/**
 * Class Database
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://www.doctrine-project.org/projects/dbal.html Database Abstraction Layer
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Database
{
    protected $bot = null;
    protected $config = [];
    protected $conn = null;

    /**
     * @param Bot $bot
     */
    public function __construct(Bot $bot = null)
    {
        $this->setBot($bot);
        //$config = $this->getBot()->getConfig()->get('database');
        //foreach ($config as $key => $value) {
        //    $this->setConfig($key, $value);
        //}
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @return \Doctrine\DBAL\Connection
     */
    public function connect()
    {
        return $this->conn = DriverManager::getConnection($this->config);
    }

    /**
     * @return mixed
     */
    public function close()
    {
        return $this->getConnection()->close();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
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
     * @return int
     */
    public function getBotId()
    {
        return $this->getBot()->getBotId();
    }

    /**
     * @param int $id
     */
    public function setBotId($id)
    {
        $this->getBot()->setBotId($id);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        try {
            return $this->getBot()->getCaller()->call('\Cerberus\Database\Database', $name, $arguments);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $error
     */
    public function error($error)
    {
        if (null !== $this->getBot()) {
            $this->getBot()->error($error);
        } else {
            echo $error;
        }
    }

    /**
     * the ping method is new in doctrine dbal at version 2.5.*
     * @link http://www.doctrine-project.org/2014/01/01/dbal-242-252beta1.html
     * @link https://packagist.org/packages/doctrine/dbal
     * @return mixed
     */
    public function ping()
    {
        return Version::compare('2.5') >= 0 ? $this->getConnection()->ping() : true;
    }

    /**
     * @param string|null $dbName
     * @throws Exception
     * @return int
     */
    public function lastInsertId($dbName = null): int
    {
        $lastInsertId = $this->getConnection()->lastInsertId();
        if (false === $lastInsertId && null !== $dbName) {
            $qb = $this->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('MAX(id) AS id')
                ->from($dbName)
                ->execute();
            $row = $stmt->fetch();
            $lastInsertId = $row['id'];
        }
        return intval($lastInsertId);
    }
}
