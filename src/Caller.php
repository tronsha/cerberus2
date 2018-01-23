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
    protected $classes = [];
    protected $bot = null;

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
     * @param string $namespace
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function call(string $namespace, string $name, array $arguments)
    {
        $className = $namespace . ucfirst($name);
        $class = $this->getClass($className);
        if (false === is_a($class, $className)) {
            return call_user_func_array([$class, $name], $arguments);
        }
    }

    /**
     * @param string $className
     * @return object|null
     */
    public function getClass(string $className)
    {
        $classKey = md5($className);
        if (false === array_key_exists($classKey, $this->classes)) {
            $this->classes[$classKey] = $this->loadClass($className);
        }
        return $this->classes[$classKey];
    }

    /**
     * @param string $className
     * @return mixed
     */
    protected function loadClass(string $className)
    {
        $classFile = $this->getBot()->getSystem()->getPath() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Cerberus', 'src', $className)) . '.php';
        if (false === $this->getBot()->getSystem()->getFilesystem()->exists($classFile)) {
            throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException(null, 0, null, $classFile);
        }
        return new $className($this->getBot());
    }
}
