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

class FormatterConsoleTest extends \PHPUnit\Framework\TestCase
{
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = FormatterFactory::console();
    }

    protected function tearDown()
    {
        unset($this->formatter);
    }

    public function testConsoleBold()
    {
        $this->assertSame(
            "\033[1mfoo\033[22m",
            $this->formatter->bold("\x02foo\x02")
        );
        $this->assertSame(
            "\033[1mfoo\033[22m",
            $this->formatter->bold("\x02foo")
        );
    }

    public function testConsoleUnderline()
    {
        $this->assertSame(
            "\033[4mfoo\033[24m",
            $this->formatter->underline("\x1Ffoo\x1F")
        );
        $this->assertSame(
            "\033[4mfoo\033[24m",
            $this->formatter->underline("\x1Ffoo")
        );
    }

    public function testConsoleColor()
    {
        $this->assertSame(
            "\033[38;5;15mfoo\033[39;49m",
            $this->formatter->color("\x03" . '0foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;0mfoo\033[39;49m",
            $this->formatter->color("\x03" . '1foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;4mfoo\033[39;49m",
            $this->formatter->color("\x03" . '2foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;2mfoo\033[39;49m",
            $this->formatter->color("\x03" . '3foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;9mfoo\033[39;49m",
            $this->formatter->color("\x03" . '4foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;1mfoo\033[39;49m",
            $this->formatter->color("\x03" . '5foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;5mfoo\033[39;49m",
            $this->formatter->color("\x03" . '6foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;3mfoo\033[39;49m",
            $this->formatter->color("\x03" . '7foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;11mfoo\033[39;49m",
            $this->formatter->color("\x03" . '8foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;10mfoo\033[39;49m",
            $this->formatter->color("\x03" . '9foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;6mfoo\033[39;49m",
            $this->formatter->color("\x03" . '10foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;14mfoo\033[39;49m",
            $this->formatter->color("\x03" . '11foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;12mfoo\033[39;49m",
            $this->formatter->color("\x03" . '12foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;13mfoo\033[39;49m",
            $this->formatter->color("\x03" . '13foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;8mfoo\033[39;49m",
            $this->formatter->color("\x03" . '14foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;7mfoo\033[39;49m",
            $this->formatter->color("\x03" . '15foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;15;48;5;0mfoo\033[39;49m",
            $this->formatter->color("\x03" . '0,1foo' . "\x03")
        );
        $this->assertSame(
            "\033[38;5;15;48;5;0mfoo\033[39;49m",
            $this->formatter->color("\x03" . '0,1foo')
        );
        $this->assertSame(
            "\033[38;5;15;48;5;0mfoo\033[39;49mbar",
            $this->formatter->color("\x03" . '0,1foo' . "\x03" . 'bar')
        );
        $this->assertSame(
            "\033[38;5;15m,foo\033[39;49m",
            $this->formatter->color("\x03" . '0,foo' . "\x03")
        );
    }
}
