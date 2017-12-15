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
use Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

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

    /**
     * @param Bot $bot
     * @param FormatterConsole $formatter
     * @param StreamOutput $output
     */
    public function __construct(Bot $bot, FormatterConsole $formatter, StreamOutput $output = null)
    {
        $this->setBot($bot);
        $this->setFormatter($formatter);
        $this->setOutput($output ?? new ConsoleOutput);
    }
    
    /**
     * @param Bot $bot
     */
    public function setBot(Bot $bot)
    {
        $this->bot = $bot;
    }
    
    /**
     * @return Bot
     */
    public function getBot(): Bot
    {
        return $this->bot;
    }

    /**
     * @param FormatterConsole $formatter
     */
    public function setFormatter(FormatterConsole $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @return FormatterConsole
     */
    public function getFormatter(): FormatterConsole
    {
        return $this->formatter;
    }

    /**
     * @param StreamOutput $output
     */
    public function setOutput(StreamOutput $output)
    {
        $output->getFormatter()->setStyle('timestamp', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('input', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('output', new OutputFormatterStyle('magenta'));
        $this->output = $output;
    }

    /**
     * @return StreamOutput
     */
    public function getOutput(): StreamOutput
    {
        return $this->output;
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
        if (null === $this->getBot()->getParam('-noconsole')) {
            $text = $this->getFormatter()->bold($text);
            $text = $this->getFormatter()->underline($text);
            $text = $this->getFormatter()->color($text);
            if (false !== $length) {
                if (null === $length) {
                    $length = $this->getColumns();
                }
                if ($length > $offset) {
                    $text = $this->build($text, $length, $break, $wordwrap, $offset);
                }
            }
            $text .= ('\\' === mb_substr($text, -1)) ? ' ' : '';
        }
        return true === $escape ? $this->escape($text) : $text;
    }
    
    /**
     * @param string $text
     * @param int $length
     * @param bool $break
     * @param bool $wordwrap
     * @param int $offset
     * @return string
     */
    private function build(string $text, int $length, bool $break, bool $wordwrap, int $offset): string
    {
        $length -= $offset;
        if ($this->len($text) > $length) {
            if (true === $break) {
                if (true === $wordwrap) {
                    $text = $this->wordwrap($text, $length);
                } else {
                    $text = $this->split($text, $length, PHP_EOL);
                }
                $text = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', $offset), $text);
            } else {
                $text = $this->cut($text, $length - 3) . '...';
                if (false !== mb_strpos($text, "\033")) {
                    $text .= "\033[0m";
                }
            }
        }
        return $text;
    }
    
    /**
     * @return int
     */
    protected function getColumns(): int
    {
        $length = 0;
        if (true === $this->getBot()->getSystem()->isExecAvailable()) {
            $length = $this->getBot()->getSystem()->getConsoleColumns();
        }
        return $length;
    }

    /**
     * @param string $text
     * @return int
     */
    protected function len(string $text): int
    {
        return mb_strlen(preg_replace("/\033\[[0-9;]+m/", '', $text));
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
        $len = mb_strlen($text);
        $textArray = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($textArray[$i], $count, $ignore);
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
        $len = mb_strlen($text);
        $textArray = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($textArray[$i], $count, $ignore);
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
