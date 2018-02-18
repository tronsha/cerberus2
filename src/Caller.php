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

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class Caller
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Caller
{
    /**
     * @var array
     */
    private $classes = [];

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
     * @param string $namespace
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     */
    public function call(string $namespace, string $methodName, array $arguments)
    {
        $className = $namespace . ucfirst($methodName);
        $object = $this->getObject($className);
        if (false === is_a($object, $className)) {
            return call_user_func_array([$object, $methodName], $arguments);
        }
    }

    /**
     * @param Bot $bot
     */
    protected function setBot($bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Bot
     */
    protected function getBot(): Bot
    {
        return $this->bot;
    }

    /**
     * @param string $className
     * @return object|null
     */
    protected function getObject(string $className)
    {
        $classKey = md5($className);
        if (false === array_key_exists($classKey, $this->classes)) {
            $this->classes[$classKey] = $this->createObject($className);
        }

        return $this->classes[$classKey];
    }

    /**
     * @param string $className
     * @return mixed
     */
    protected function createObject(string $className)
    {
        $classFile = $this->getBot()->getSystem()->getPath() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Cerberus', 'src', $className)) . '.php';
        if (false === $this->getBot()->getSystem()->getFilesystem()->exists($classFile)) {
            throw new FileNotFoundException(null, 0, null, $classFile);
        }

        return new $className($this->getBot());
    }
}
