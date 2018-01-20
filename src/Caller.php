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

    /**
     * @param string $namespace
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function call(string $namespace, string $name, array $arguments)
    {
        $class = $this->getClass($namespace, $name);
        if (null !== $class) {
            return call_user_func_array([$class, $name], $arguments);
        }
    }

    /**
     * @param string $namespace
     * @param string $name
     * @return object|null
     */
    public function getClass(string $namespace, string $name)
    {
        $key = strtolower($name);
        if (!array_key_exists($key, $this->classes)) {
            return $this->loadClass($namespace, $name);
        }
        $class = $this->classes[$key];
        $className = $namespace . ucfirst($name);
        if (!is_a($class, $className)) {
            return null;
        }
        return $class;
    }

    /**
     * @param string $namespace
     * @param string $name
     * @return mixed
     */
    protected function loadClass(string $namespace, string $name)
    {
        $key = strtolower($name);
        $className = $namespace . ucfirst($name);
        $this->classes[$key] = new $className($this);
        return $this->classes[$key];
    }
}
