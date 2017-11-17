<?php

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

class FormatterHtmlTest extends \PHPUnit\Framework\TestCase
{
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = FormatterFactory::html();
    }

    protected function tearDown()
    {
        unset($this->formatter);
    }

    public function testHtmlBold()
    {
        $this->assertSame(
            '<b style="font-weight: bold;">foo</b>',
            $this->formatter->bold("\x02foo\x02")
        );
        $this->assertSame(
            '<b style="font-weight: bold;">foo</b>',
            $this->formatter->bold("\x02foo")
        );
    }

    public function testHtmlUnderline()
    {
        $this->assertSame(
            '<u style="text-decoration: underline;">foo</u>',
            $this->formatter->underline("\x1Ffoo\x1F")
        );
        $this->assertSame(
            '<u style="text-decoration: underline;">foo</u>',
            $this->formatter->underline("\x1Ffoo")
        );
    }

    public function testHtmlColor()
    {
        $this->assertSame(
            '<span style="color: #FFFFFF;">foo</span>',
            $this->formatter->color("\x03" . '0foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #000000;">foo</span>',
            $this->formatter->color("\x03" . '1foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00007F;">foo</span>',
            $this->formatter->color("\x03" . '2foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #009300;">foo</span>',
            $this->formatter->color("\x03" . '3foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FF0000;">foo</span>',
            $this->formatter->color("\x03" . '4foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #7F0000;">foo</span>',
            $this->formatter->color("\x03" . '5foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #9C009C;">foo</span>',
            $this->formatter->color("\x03" . '6foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FC7F00;">foo</span>',
            $this->formatter->color("\x03" . '7foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FFFF00;">foo</span>',
            $this->formatter->color("\x03" . '8foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00FC00;">foo</span>',
            $this->formatter->color("\x03" . '9foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #009393;">foo</span>',
            $this->formatter->color("\x03" . '10foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00FFFF;">foo</span>',
            $this->formatter->color("\x03" . '11foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #0000FC;">foo</span>',
            $this->formatter->color("\x03" . '12foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FF00FF;">foo</span>',
            $this->formatter->color("\x03" . '13foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #7F7F7F;">foo</span>',
            $this->formatter->color("\x03" . '14foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #D2D2D2;">foo</span>',
            $this->formatter->color("\x03" . '15foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>',
            $this->formatter->color("\x03" . '0,1foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>',
            $this->formatter->color("\x03" . '0,1foo')
        );
        $this->assertSame(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>bar',
            $this->formatter->color("\x03" . '0,1foo' . "\x03" . 'bar')
        );
        $this->assertSame(
            '<span style="color: #FFFFFF;">,foo</span>',
            $this->formatter->color("\x03" . '0,foo' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00007F; background-color: #00FFFF;">foo</span>bar<span style="color: #7F0000;">baz</span>',
            $this->formatter->color("\x03" . '2,11foo' . "\x03" . 'bar' . "\x03" . '5baz' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00007F;">foo</span><span style="color: #7F0000;">bar</span>',
            $this->formatter->color("\x03" . '2foo' . "\x03" . "\x03" . '5bar' . "\x03")
        );
        $this->assertSame(
            '<span style="color: #00007F; background-color: #00FFFF;">foo</span><span style="color: #7F0000; background-color: #00FFFF;">bar</span>',
            $this->formatter->color("\x03" . '2,11foo' . "\x03" . '5bar' . "\x03")
        );
        $this->assertSame(
            'foo',
            $this->formatter->color("\x03" . 'foo' . "\x03")
        );
    }
}
