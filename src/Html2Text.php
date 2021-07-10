<?php

declare(strict_types=1);

namespace OpsRil\Sanitize;


class Html2Text
{
    /**
     * Class Html2Text
     *
     * Copyright (c) 2005-2007 Jon Abernathy <jon@chuggnutt.com>
     *
     * This script is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * The GNU General Public License can be found at
     * http://www.gnu.org/copyleft/gpl.html.
     *
     * This script is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     */

    protected const ENCODING = 'UTF-8';
    protected $htmlEntityDecodeFlags = ENT_QUOTES | ENT_HTML5;
    protected $options = [];
    protected $regexPatternToReplacementArray = [
        // Non-legal carriage return
        "/\r/" => '',
        // Newlines and tabs
        "/[\n\t]+/" => ' ',
        // <head>
        '/<head\b[^>]*>.*?<\/head\b\s*>/i' => '',
        // <script>s -- which strip_tags supposedly has problems with
        '/<script\b[^>]*>.*?<\/script\b\s*>/i' => '',
        // <style>s -- which strip_tags supposedly has problems with
        '/<style\b[^>]*>.*?<\/style\b\s*>/i' => '',
        // <ul> and </ul>
        '/(<ul\b[^>]*>|<\/ul\b\s*>)/i' => "\n\n",
        // <ol> and </ol>
        '/(<ol\b[^>]*>|<\/ol\b\s*>)/i' => "\n\n",
        // <dl> and </dl>
        '/(<dl\b[^>]*>|<\/dl\b\s*>)/i' => "\n\n",
        // <li>
        '/<li\b[^>]*>/i' => "\t* ",
        // </li>
        '/<\/li\b\s*>/i' => "\n",
        // <hr>
        '/<hr\b[^>]*>/i' => "\n-------------------------\n",
        // <div>
        '/<div\b[^>]*>/i' => "<div>\n",
        // <table> and </table>
        '/(<table\b[^>]*>|<\/table\b\s*>)/i' => "\n\n",
        // <tr> and </tr>
        '/(<tr\b[^>]*>|<\/tr\b\s*>)/i' => "\n",
        // </td>
        '/<\/td\b\s*>/i' => "</td>\n",
        // <br>
        '/<br\b[^>]*>/i' => "\n",
        // h1 - h6
        '/(<h[123456]\b[^>]*>|<\/h[123456]\b\s*>)/i' => "\n\n",
        // &nbsp; -- replace with regular space so that trim can remove it
        '/&nbsp;/i' => ' ',
    ];

    protected $normalizeWhitespaceRegexPatternToReplacementArray = [
        // Normalise empty lines
        "/\n\s+\n/" => "\n\n",
        "/[\n]{3,}/" => "\n\n",
        // Remove leading and trailing spaces per line
        '/^[ ]*|[ ]*$/m' => '',
        // Reduce multiple occurrences of space to a single space
        '/[ ]{2,}/' => ' ',
    ];

    /**
     * Html2Text constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->defaultOptions(), $options);
    }

    /**
     * @return array
     */
    public function defaultOptions(): array
    {
        return [
            'exceptions' => [
                UncovertedHtmlEntityException::class => false
            ]
        ];
    }

    /**
     * @param $exceptionClassName
     * @return bool
     */
    protected function isExceptionEnabled($exceptionClassName): bool
    {
        return isset($this->options['exceptions'][$exceptionClassName])
            && $this->options['exceptions'][$exceptionClassName] === true;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function convertPTags(string $text): string
    {
        // <p> with surrounding whitespace.
        $pTagRegexPattern = '/[ ]*<p\b[^>]*>(.*?)<\/p\b\s*>[ ]*/si';
        return preg_replace_callback(
            $pTagRegexPattern,
            static function ($match) {
                // Replace newlines with spaces.
                $paragraph = str_replace("\n", " ", $match[1]);

                // Trim trailing and leading whitespace within the tag.
                $paragraph = trim($paragraph);

                // Add trailing newlines for this paragraph.
                return "\n" . $paragraph . "\n";
            },
            $text
        );
    }

    protected function convertATags(string $text): string
    {
        $aTagRegexPattern = '/<a\b[^>]*href=["\']([^"\']+)[^>]*>(.*?)<\/a\b\s*>/i';
        return preg_replace_callback(
            $aTagRegexPattern,
            function ($matches) {
                // Remove spaces in URL
                $url = str_replace(' ', '', $matches[1]);
                $display = $matches[2];
                if (preg_match(
                    '/^(javascript:|#)/i',
                    html_entity_decode(
                        $url,
                        $this->htmlEntityDecodeFlags,
                        self::ENCODING
                    )
                )) {
                    return $display;
                }
                if (preg_match(
                    '/^mailto:/i',
                    html_entity_decode(
                        $url,
                        $this->htmlEntityDecodeFlags,
                        self::ENCODING
                    )
                )) {
                    return str_replace('mailto:', '', $url);
                }

                if ($url === $display) {
                    return $display;
                }
                return $display . ' [' . $url . ']';
            },
            $text
        );
    }

    /**
     * @param string $text
     * @return string
     */
    protected function normalizeWhitespace(string $text): string
    {
        // Remove excessive new lines and spaces
        $text = preg_replace(
            array_keys($this->normalizeWhitespaceRegexPatternToReplacementArray),
            $this->normalizeWhitespaceRegexPatternToReplacementArray,
            $text
        );

        // remove leading and trailing whitespace (can be produced by eg. P tag on the beginning)
        return trim($text);
    }

    /**
     * @param string $html
     * @return string
     * @throws UncovertedHtmlEntityException
     */
    public function convert(string $html): string
    {
        $html = trim($html);
        $text = preg_replace(
            array_keys($this->regexPatternToReplacementArray),
            $this->regexPatternToReplacementArray,
            $html
        );
        $text = $this->convertPTags($text);
        $text = $this->convertATags($text);
        $text = strip_tags($text);

        $text = html_entity_decode($text, $this->htmlEntityDecodeFlags, self::ENCODING);

        // Check for entities not decoded by html_entity_decode()
        // False positives are expected.
        // E.g. &apos;&amp;reg; is correctly decoded to '&reg; but will still lead to an exception.
        if ($this->isExceptionEnabled(UncovertedHtmlEntityException::class) && preg_match(
                '/&([a-zA-Z0-9]+|#[0-9]+);/',
                $text
            ) === 1) {
            throw new UncovertedHtmlEntityException();
        }

        return $this->normalizeWhitespace($text);
    }
}
