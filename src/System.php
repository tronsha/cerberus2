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

/**
 * Class System
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class System
{
    /**
     * @return bool
     */
    public function isExecAvailable(): bool
    {
        $available = true;
        $safemode = ini_get('safe_mode');
        if (false === empty($safemode)) {
            $available = false;
        } else {
            $disable = ini_get('disable_functions');
            $blacklist = ini_get('suhosin.executor.func.blacklist');
            $disableOrBlacklist = $disable . $blacklist;
            if (false === empty($disableOrBlacklist)) {
                $array = preg_split('/,\s*/', $disable . ',' . $blacklist);
                if (true === in_array('exec', $array, true)) {
                    $available = false;
                }
            }
        }
        return $available;
    }

    /**
     * @return int
     */
    public function getConsoleColumns(): int
    {
        $matches = [];
        preg_match('/columns\s([0-9]+);/', strtolower(exec('stty -a | grep columns')), $matches);
        return (false === isset($matches[1]) || intval($matches[1]) <= 0) ? 0 : intval($matches[1]);
    }
}
