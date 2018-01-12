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

use Cerberus\Formatter\Console as FormatterConsole;
use Cerberus\Output\Console;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

class OutputConsoleTest extends \PHPUnit\Framework\TestCase
{
    protected $stream;
    protected $console;

    protected function setUp()
    {
        $system = $this->createMock(System::class);
        $system->method('isExecAvailable')->will($this->returnValue(true));
        $system->method('getConsoleColumns')->will($this->returnValue(80));
        $bot = $this->createMock(Bot::class);
        $bot->method('getSystem')->will($this->returnValue($system));
        $formatter = $this->createMock(FormatterConsole::class);
        $formatter->method('bold')->will($this->returnArgument(0));
        $formatter->method('underline')->will($this->returnArgument(0));
        $formatter->method('color')->will($this->returnArgument(0));
        $this->console = new Console($bot, $formatter);
        $this->stream = fopen('php://memory', 'a', false);
    }

    protected function tearDown()
    {
        unset($this->console);
        $this->stream = null;
    }

    public function invokeMethod(&$object, $methodName, ...$parameters)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testNestedStyles()
    {
        $formatter = new OutputFormatter(true);
        $this->assertSame(
            "\033[32mTest \033[39m\033[37;41merror\033[39;49m\033[32m and \033[39m\033[33mcomment\033[39m\033[32m inside a info.\033[39m",
            $formatter->format('<info>Test <error>error</error> and <comment>comment</comment> inside a info.</info>')
        );
    }

    public function testFormattedEscapedOutput()
    {
        $output = new StreamOutput($this->stream, StreamOutput::VERBOSITY_NORMAL, true, null);
        $output->writeln('<info>' . OutputFormatter::escape('<error>some error</error>') . '</info>');
        rewind($output->getStream());
        $this->assertSame(
            "\033[32m<error>some error</error>\033[39m" . PHP_EOL,
            stream_get_contents($output->getStream())
        );
    }

    public function testEscapedOutput()
    {
        $this->assertSame('\<error>some error\</error>', $this->console->escape('<error>some error</error>'));
    }

    public function testPrepareOutput()
    {
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...';
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));
        
        $input = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...\033[0m";
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = "abc\033[1mdefghijklmnopqrstuvwxyz\033[22mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abc\033[1mdefghijklmnopqrstuvwxyz\033[22mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...\033[0m";
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, false, 0));

        $input = "abcdefghijklmnopqrstuvwxyz\033[1mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyz\033[1mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab" . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcd' . PHP_EOL . 'efghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdef' . PHP_EOL . 'ghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcd' . PHP_EOL . 'efghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdef' . PHP_EOL . 'ghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcd' . PHP_EOL . 'efghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdef' . PHP_EOL . 'ghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcd' . PHP_EOL . 'efghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde fghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde" . PHP_EOL . 'fghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = '0123456789\\';
        $output = '0123456789\\ ';
        $this->assertSame($output, $this->console->prepare($input, false, false));

        $input = '0123456789\\';
        $output = '0123456789\\ ';
        $this->assertSame($output, $this->console->prepare($input, false, 80));

        $input = '0123456789\\';
        $output = '01234' . PHP_EOL . '56789' . PHP_EOL . '\\ ';
        $this->assertSame($output, $this->console->prepare($input, false, 5, true, false));
    }
    
    public function testPrepareOutputMultibyte()
    {
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstüvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstüvwxy...';
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxüzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxü...';
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyüabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...';
        $this->assertSame($output, $this->console->prepare($input, false, 80, false, false, 0));
        
        $input = 'abcdefghijklmnopqrstüvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstüvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzaücdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzaü' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabüdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'üdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstüvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstüvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyü abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyü' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz übcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'übcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
        
        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstüvwxyzabcdefghijklmnopqrstu \033[1mabcde fghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstüvwxyzabcdefghijklmnopqrstu \033[1mabcde" . PHP_EOL . 'fghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
        
        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstü \033[1mabcde fghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstü \033[1mabcde" . PHP_EOL . 'fghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
        
        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde üghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde" . PHP_EOL . 'üghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, 80, true, true, 0));
    }
    
    public function testPrepareOutputSystem()
    {
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...';
                   
        $this->assertSame($output, $this->console->prepare($input, false, null, false, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, null, true, false, 0));
        
        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, null, true, true, 0));
        
        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde fghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde" . PHP_EOL . 'fghijklmnopqrstuvwxyz';
        $this->assertSame($output, $this->console->prepare($input, false, null, true, true, 0));
    }

    public function testPrepareOutputWithFormatter()
    {
        $bot = $this->createMock(Bot::class);
        $formatter = new FormatterConsole;
        $console = new Console($bot, $formatter);
        $input = 'abcmdefghijklmnopqrstuvwxyz' . "\x03" . '1,8abcmdefghijklmnopqrstuvwxyz';
        $output = 'abcmdefghijklmnopqrstuvwxyz' . "\033[38;5;0;48;5;11m" . 'abcmdefghijklmnopqrstuvwxyz' . "\033[39;49m";
        $this->assertSame($output, $console->prepare($input, false, 80, false, false, 0));
    }
 
    public function testPrepareOutputNoconsole()
    {
        $bot = $this->createMock(Bot::class);
        $bot->method('getParam')->will($this->returnValue(''));
        $formatter = new FormatterConsole;
        $console = new Console($bot, $formatter);
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $this->assertSame($output, $console->prepare($input, false, 80, false, false, 0));
    }

    /**
     * @dataProvider lenProvider
     */
    public function testLen($expected, $check, $text)
    {
        $this->assertSame($expected, $this->invokeMethod($this->console, 'len', $text));
        $this->assertSame($check, strlen($text));
    }
    
    public function lenProvider()
    {
        return [
            [4, 4, 'test'],
            [4, 12, "\033[1mtest\033[0m"],
        ];
    }
    
    /**
     * @dataProvider countProvider
     */
    public function testCount($char, $count, $ignore, $expectedCount, $expectedIgnore)
    {
        $this->assertSame($char, $this->console->count($char, $count, $ignore));
        $this->assertSame($expectedCount, $count);
        $this->assertSame($expectedIgnore, $ignore);
    }
    
    public function countProvider()
    {
        return [
            ['x', 1, false, 2, false],
            ['m', 2, false, 3, false],
            ["\033", 3, false, 3, true],
            ['[', 3, true, 3, true],
            ['1', 3, true, 3, true],
            ['m', 3, true, 3, false],
            ['x', 3, false, 4, false],
        ];
    }
    
    /**
     * @dataProvider cutProvider
     */
    public function testCut($expected, $check, $text, $length)
    {
        $this->assertSame($expected, $this->invokeMethod($this->console, 'cut', $text, $length));
        $this->assertSame($check, substr($text, 0, $length));
    }

    public function cutProvider()
    {
        return [
            ['foo', 'foo', 'foobar', 3],
            ["\033[1mfoo", "\033[1", "\033[1mfoobar\033[0m", 3],
            ["\033[1mfoobar", "\033[1mfo", "\033[1mfoobar\033[0m", 6],
            ["foo\033[1mbar", "foo\033[1", "foo\033[1mbar\033[0m", 6],
        ];
    }

    /**
     * @dataProvider wordwrapProvider
     */
    public function testWordwrap($expected, $check, $text, $length)
    {
        $this->assertSame($expected, $this->invokeMethod($this->console, 'wordwrap', $text, $length));
        $this->assertSame($check, wordwrap($text, $length, "\n"));
    }
    
    public function wordwrapProvider()
    {
        return [
            ["foo bar\nbaz", "foo bar\nbaz", 'foo bar baz', 10],
            ["foo \033[1mbar\nbaz", "foo\n\033[1mbar\nbaz", "foo \033[1mbar baz", 10],
        ];
    }

    /**
     * @dataProvider splitProvider
     */
    public function testSplit($expected, $check, $text, $length)
    {
        $this->assertSame($expected, $this->invokeMethod($this->console, 'split', $text, $length));
        $this->assertSame($check, chunk_split($text, $length, "\n"));
    }
    
    public function splitProvider()
    {
        return [
            ["foo b\nar ba\nz", "foo b\nar ba\nz\n", 'foo bar baz', 5],
            ["foo \033[1mb\nar ba\nz", "foo \033\n[1mba\nr baz\n", "foo \033[1mbar baz", 5],
        ];
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testException($method)
    {
        try {
            $this->invokeMethod($this->console, $method, 0, -1);
        } catch (\Exception $e) {
            $this->assertSame('Length cannot be negative or null.', $e->getMessage());
        }
    }
    
    public function exceptionProvider()
    {
        return [
            ['wordwrap'],
            ['split'],
            ['cut'],
        ];
    }
    
    public function testWriteln()
    {
        $bot = $this->createMock(Bot::class);
        $formatter = $this->createMock(FormatterConsole::class);
        $output = new StreamOutput($this->stream);
        $console = new Console($bot, $formatter, $output);
        $console->writeln('foo');
        rewind($output->getStream());
        $this->assertSame('foo' . PHP_EOL, stream_get_contents($output->getStream()));
    }
    
    /**
     * @dataProvider writelnAndPrepareProvider
     */
    public function testWritelnAndPrepare($expected, $text)
    {
        $bot = $this->createMock(Bot::class);
        $formatter = new FormatterConsole;
        $output = new StreamOutput($this->stream);
        $console = new Console($bot, $formatter, $output);
        $console->writeln($console->prepare($text, true, 80, true, true, 0));
        rewind($output->getStream());
        $this->assertSame($expected . PHP_EOL, stream_get_contents($output->getStream()));
    }
    
    public function writelnAndPrepareProvider()
    {
        return [
            [
                'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz',
                'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz'
            ],
            [
                '<error>some error</error>',
                '<error>some error</error>'
            ],
        ];
    }

    public function testSetterAndGetter()
    {
        $bot = $this->createMock(Bot::class);
        $formatter = $this->createMock(FormatterConsole::class);
        $output = new ConsoleOutput();
        $console = new Console($bot, $formatter, $output);
        $this->assertInstanceOf(Bot::class, $console->getBot());
        $this->assertInstanceOf(FormatterConsole::class, $console->getFormatter());
        $this->assertInstanceOf(StreamOutput::class, $console->getOutput());
    }
}
