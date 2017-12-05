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

namespace Cerberus\Output;

use Cerberus\Bot;
use Cerberus\Formatter\Console as FormatterConsole;
use Cerberus\System;
use Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Console
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://symfony.com/doc/current/components/console/introduction.html The Console Component
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Console
{
    protected $bot;
    protected $formatter;
    protected $output;
    protected $return = false;
    protected $param = null;

    /**
     * @param Bot $bot
     * @param FormatterConsole $formatter
     */
    public function __construct(Bot $bot, FormatterConsole $formatter)
    {
        $this->bot = $bot;
        $this->formatter = $formatter;
        $this->output = new ConsoleOutput;
        $this->output->getFormatter()->setStyle('timestamp', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('input', new OutputFormatterStyle('cyan'));
        $this->output->getFormatter()->setStyle('output', new OutputFormatterStyle('magenta'));
    }

    /**
     * @param array $argv
     */
    public function setParam(array $argv = [])
    {
        $this->param = $argv;
    }

    /**
     * @param string $text
     */
    public function writeln(string $text = '')
    {
        $this->output->writeln($text);
    }

    /**
     * @param string $text
     * @return string
     */
    public function escape(string $text): string
    {
        return OutputFormatter::escape($text);
    }

    /**
     * @param string $text
     * @param bool $escape
     * @param mixed $length
     * @param bool $break
     * @param bool $wordwrap
     * @param int $offset
     * @return string
     */
    public function prepare(string $text, bool $escape = true, $length = null, bool $break = true, bool $wordwrap = true, int $offset = 0): string
    {
        if (!(isset($this->param) && is_array($this->param) && in_array('-noconsole', $this->param, true))) {
            $text = $this->build($text, $length, $break, $wordwrap, $offset);
        }
        return $escape ? $this->escape($text) : $text;
    }
    
    /**
     * @param string $text
     * @param mixed $length
     * @param bool $break
     * @param bool $wordwrap
     * @param int $offset
     * @return string
     */
    private function build(string $text, $length = null, bool $break = true, bool $wordwrap = true, int $offset = 0): string
    {  
        $text = $this->formatter->bold($text);
        $text = $this->formatter->underline($text);
        $text = $this->formatter->color($text);
        if (false === $length) {
            $text .= ('\\' === substr($text, -1)) ? ' ' : '';
            return $text;
        }
        if (null === $length) {
            if (false === System::isExecAvailable()) {
                return $text;
            }
            $length = System::getConsoleColumns();
            if (0 === $length) {
                return $text;
            }
        }
        $length -= $offset;
        if ($this->len($text) <= $length) {
            $text .= ('\\' === substr($text, -1)) ? ' ' : '';
            return $text;
        }
        $text = utf8_decode($text);
        if ($break) {
            if ($wordwrap) {
                $text = $this->wordwrap($text, $length);
            } else {
                $text = $this->split($text, $length, PHP_EOL);
            }
            $text = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', $offset), $text);
        } else {
            $text = $this->cut($text, $length - 3) . '...';
            if (false !== strpos($text, "\033")) {
                $text .= "\033[0m";
            }
        }
        $text = utf8_encode($text);
        $text .= ('\\' === substr($text, -1)) ? ' ' : '';
        return $text;
    }

    /**
     * @param string $text
     * @return int
     */
    protected function len(string $text): int
    {
        return strlen(preg_replace("/\033\[[0-9;]+m/", '', $text));
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $break
     * @param bool $cut
     * @throws Exception
     * @return string
     */
    protected function wordwrap(string $text, int $length = 80, string $break = PHP_EOL, bool $cut = true): string
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $textArray = explode(' ', $text);
        $count = 0;
        $lineCount = 0;
        $output = [];
        $output[$lineCount] = '';
        foreach ($textArray as $word) {
            $wordLength = $this->len($word);
            if (($count + $wordLength) <= $length) {
                $count += $wordLength + 1;
                $output[$lineCount] .= $word . ' ';
            } elseif ($cut && $wordLength > $length) {
                $wordArray = explode(' ', $this->split($word, $length, ' '));
                foreach ($wordArray as $word) {
                    $wordLength = $this->len($word);
                    $output[$lineCount] = trim($output[$lineCount]);
                    $lineCount++;
                    $count = $wordLength + 1;
                    $output[$lineCount] = $word . ' ';
                }
            } else {
                $output[$lineCount] = trim($output[$lineCount]);
                $lineCount++;
                $count = $wordLength + 1;
                $output[$lineCount] = $word . ' ';
            }
        }

        return trim(implode($break, $output));
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $end
     * @throws Exception
     * @return string
     */
    protected function split(string $text, int $length = 80, string $end = PHP_EOL): string
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count === $length) {
                $count = 0;
                $output .= $end;
            }
        }

        return $output;
    }

    /**
     * @param string $text
     * @param int $length
     * @throws Exception
     * @return string
     */
    protected function cut(string $text, int $length): string
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count === $length) {
                break;
            }
        }

        return $output;
    }

    /**
     * @param string $char
     * @param int $count
     * @param bool $ignore
     * @return string
     */
    protected function count(string $char, int &$count, bool &$ignore): string
    {
        if ("\033" === $char) {
            $ignore = true;
        }
        if (!$ignore) {
            $count++;
        }
        if ($ignore && 'm' === $char) {
            $ignore = false;
        }

        return $char;
    }
}
