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

namespace Cerberus\Formatter;

use Exception;

/**
 * Class Formatter
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
abstract class Formatter
{
    protected $type = null;
    
    abstract protected function matchColor(int $id): string;

    abstract protected function getColor(int $fontColor = null, int $backgroundColor = null): string;

    abstract protected function bold(string $text): string;

    abstract protected function underline(string $text): string;

    /**
     * @param string $text
     * @param string $delimiter
     * @param string $start
     * @param string $stop
     * @return string
     */
    protected function format(string $text, string $delimiter, string $start = null, string $stop = null): string
    {
        $formatArray = explode($delimiter, $text);
        $text = array_shift($formatArray);
        $open = false;
        foreach ($formatArray as $part) {
            if (!$open) {
                $text .= $start;
                $open = true;
            } else {
                $text .= $stop;
                $open = false;
            }
            $text .= $part;
        }
        if ($open) {
            $text .= $stop;
        }

        return $text;
    }

    /**
     * @param string $text
     * @throws Exception
     * @return string
     */
    public function color(string $text): string
    {
        if ('HTML' !== $this->type && 'CONSOLE' !== $this->type) {
            throw new Exception('Type must be HTML or Console.');
        }
        $coloredText = '';
        $colorType = $fontColor = $backgroundColor = '';
        $reset = false;
        foreach (str_split($text) as $char) {
            if ("\x03" === $char) {
                $colorType = 'font';
            } elseif ('font' === $colorType && (0 === strlen($fontColor) || 1 === strlen($fontColor)) && 48 <= ord($char) && 57 >= ord($char)) {
                $fontColor .= $char;
            } elseif ('font' === $colorType && (1 === strlen($fontColor) || 2 === strlen($fontColor)) && ',' === $char) {
                $colorType = 'background';
            } elseif ('background' === $colorType && (0 === strlen($backgroundColor) || 1 === strlen($backgroundColor)) && 48 <= ord($char) && 57 >= ord($char)) {
                $backgroundColor .= $char;
            } elseif ('font' === $colorType || 'background' === $colorType) {
                if ('' !== $backgroundColor) {
                    $coloredText .= $this->getColor($fontColor, $backgroundColor);
                    $reset = true;
                } elseif ('' !== $fontColor) {
                    $coloredText .= $this->getColor($fontColor);
                    $reset = true;
                } else {
                    $coloredText .= $this->getColor();
                    $reset = false;
                }
                if ('background' === $colorType && '' === $backgroundColor) {
                    $coloredText .= ',';
                }
                $colorType = $fontColor = $backgroundColor = '';
                $coloredText .= $char;
            } else {
                $coloredText .= $char;
            }
        }
        if ($reset) {
            $coloredText .= $this->getColor();
        }

        return $coloredText;
    }
}
