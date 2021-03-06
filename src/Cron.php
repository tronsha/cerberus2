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

use Exception;

/**
 * Class Cron
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link https://en.wikipedia.org/wiki/Cron Cron
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Cron
{
    /**
     * @var array
     */
    private $cronjobs = [];

    /**
     * @var int
     */
    private $cronIdCount = 0;

    /**
     * @param string $cronString
     * @param object $object
     * @param string $method
     * @param array $param
     * @return int
     */
    public function add(string $cronString, $object, string $method = 'run', array $param = null): int
    {
        $this->cronIdCount++;
        $cronString = preg_replace('/\s+/', ' ', $cronString);
        $this->cronjobs[$this->cronIdCount] = ['cron' => $cronString, 'object' => $object, 'method' => $method, 'param' => $param];

        return $this->cronIdCount;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id): bool
    {
        if (true === array_key_exists($id, $this->cronjobs)) {
            unset($this->cronjobs[$id]);

            return true;
        }

        return false;
    }

    /**
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     */
    public function run($minute, $hour, $day_of_month, $month, $day_of_week)
    {
        foreach ($this->cronjobs as $cron) {
            if (true === $this->compare($cron['cron'], $minute, $hour, $day_of_month, $month, $day_of_week)) {
                $cron['object']->{$cron['method']}($cron['param']);
            }
        }
    }

    /**
     * @param string $cronString
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     * @throws Exception
     * @return bool
     */
    protected function compare(string $cronString, int $minute, int $hour, int $day_of_month, int $month, int $day_of_week): bool
    {
        $cronString = trim($cronString);
        $cronArray = explode(' ', $cronString);
        if (5 !== count($cronArray)) {
            throw new Exception('a cron has an error');
        }
        list($cronMinute, $cronHour, $cronDayOfMonth, $cronMonth, $cronDayOfWeek) = $cronArray;
        $cronDayOfWeek = $this->dowNameToNumber($cronDayOfWeek);
        $cronMonth = $this->monthNameToNumber($cronMonth);
        $cronDayOfWeek = ('7' === $cronDayOfWeek ? '0' : $cronDayOfWeek);
        $cronMinute = ('*' !== $cronMinute ? $this->prepare($cronMinute, 0, 59) : $cronMinute);
        $cronHour = ('*' !== $cronHour ? $this->prepare($cronHour, 0, 23) : $cronHour);
        $cronDayOfMonth = ('*' !== $cronDayOfMonth ? $this->prepare($cronDayOfMonth, 1, 31) : $cronDayOfMonth);
        $cronMonth = ('*' !== $cronMonth ? $this->prepare($cronMonth, 1, 12) : $cronMonth);
        $cronDayOfWeek = ('*' !== $cronDayOfWeek ? $this->prepare($cronDayOfWeek, 0, 6) : $cronDayOfWeek);
        if (
            (
                '*' === $cronMinute  || true === in_array($minute, $cronMinute, true)
            ) && (
                '*' === $cronHour || true === in_array($hour, $cronHour, true)
            ) && (
                '*' === $cronMonth || true === in_array($month, $cronMonth, true)
            ) && (
                (
                    (
                        '*' === $cronDayOfMonth || true === in_array($day_of_month, $cronDayOfMonth, true)
                    ) && (
                        '*' === $cronDayOfWeek || true === in_array($day_of_week, $cronDayOfWeek, true)
                    )
                ) || (
                    (
                        '*' !== $cronDayOfMonth
                    ) && (
                        '*' !== $cronDayOfWeek
                    ) && (
                        (
                            true === in_array($day_of_month, $cronDayOfMonth, true)
                        ) || (
                            true === in_array($day_of_week, $cronDayOfWeek, true)
                        )
                    )
                )
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $string
     * @param int $a
     * @param int $b
     * @return array
     */
    protected function prepare(string $string, int $a, int $b): array
    {
        $values = [];
        if (false !== strpos($string, ',')) {
            $values = explode(',', $string);
        } else {
            $values[] = $string;
        }
        $array = [];
        foreach ($values as $value) {
            $steps = 1;
            if (false !== strpos($string, '/')) {
                list($value, $steps) = explode('/', $string);
            }
            if ('*' === $value) {
                $value = $a . '-' . $b;
            }
            if (false !== strpos($value, '-')) {
                list($min, $max) = explode('-', $value);
                $min = intval($min);
                $max = intval($max);
                for ($i = $min, $j = 0; $i <= $max; $i++, $j++) {
                    if (0 === ($j % $steps)) {
                        $array[] = $i;
                    }
                }
            } else {
                $array[] = intval($value);
            }
        }

        return $array;
    }

    /**
     * @param string $subject
     * @return string
     */
    protected function monthNameToNumber(string $subject): string
    {
        $subject = strtolower($subject);
        $search = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $replace = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

        return str_replace($search, $replace, $subject);
    }

    /**
     * @param string $subject
     * @return string
     */
    protected function dowNameToNumber(string $subject): string
    {
        $subject = strtolower($subject);
        $search = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $replace = ['0', '1', '2', '3', '4', '5', '6'];

        return str_replace($search, $replace, $subject);
    }
}
