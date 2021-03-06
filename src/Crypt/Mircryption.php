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

namespace Cerberus\Crypt;

use Cerberus\Php;

/**
 * Class Mircryption
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @link https://www.donationcoder.com/Software/Mouser/mircryption/index.php Mircryption
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Mircryption
{
    /**
     * @param string $text
     * @param string $key
     * @return string
     */
    public static function encode($text, $key)
    {
        if (true === extension_loaded('mcrypt')) {
            return self::mcryptEncrypt($text, $key);
        } elseif (true === extension_loaded('openssl')) {
            return self::opensslEncrypt($text, $key);
        }

        return null;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     */
    public static function decode($text, $key)
    {
        if (true === extension_loaded('mcrypt')) {
            return self::mcryptDecrypt($text, $key);
        } elseif (true === extension_loaded('openssl')) {
            return self::opensslDecrypt($text, $key);
        }

        return null;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     */
    private static function mcryptEncrypt($text, $key): string
    {
        $iv = random_bytes(8);
        $encodedText = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, Pkcs7::pad($text), MCRYPT_MODE_CBC, $iv);
        $encodedTextIv = $iv . $encodedText;
        $decodedTextBaseIv64 = base64_encode($encodedTextIv);

        return '*' . $decodedTextBaseIv64;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.mcrypt-decrypt.php
     */
    private static function mcryptDecrypt($text, $key): string
    {
        $encodedTextIvBase64 = str_replace('*', '', $text);
        $encodedTextIv = base64_decode($encodedTextIvBase64, true);
        $iv = substr($encodedTextIv, 0, 8);
        $encodedText = substr($encodedTextIv, 8);
        $plaintext = Pkcs7::unpad(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encodedText, MCRYPT_MODE_CBC, $iv));

        return trim($plaintext);
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.openssl-encrypt.php
     */
    private static function opensslEncrypt($text, $key): string
    {
        $iv = random_bytes(8);
        $encodedText = openssl_encrypt($text, 'bf-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $encodedTextIv = $iv . $encodedText;
        $decodedTextBaseIv64 = base64_encode($encodedTextIv);

        return '*' . $decodedTextBaseIv64;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.openssl-decrypt.php
     */
    private static function opensslDecrypt($text, $key): string
    {
        $encodedTextIvBase64 = str_replace('*', '', $text);
        $encodedTextIv = base64_decode($encodedTextIvBase64, true);
        $iv = substr($encodedTextIv, 0, 8);
        $encodedText = substr($encodedTextIv, 8);
        $plaintext = openssl_decrypt($encodedText, 'bf-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return trim($plaintext);
    }
}
