<?php

declare(strict_types=1);

namespace OpsRil\Sanitize;


class Html2Text extends \Html2Text\Html2Text
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

    public const ENCODING = 'UTF-8';

    /**
     * @param string $html    Source HTML
     * @param array  $options Set configuration options
     */
    public function __construct($html = '', $options = array())
    {
        $this->html = $html;
        $this->options = array_merge($this->options, $options);
        $this->htmlFuncFlags = ENT_QUOTES | ENT_HTML5;
    }

    /**
     * List of pattern replacements corresponding to patterns searched.
     *
     * @var array $replace
     * @see $search
     */
    protected $replace = [
        '',                              // Non-legal carriage return
        ' ',                             // Newlines and tabs
        '',                              // <head>
        '',                              // <script>s -- which strip_tags supposedly has problems with
        '',                              // <style>s -- which strip_tags supposedly has problems with
        '\\1',                           // <i>
        '\\1',                           // <em>
        '\\1',                           // <ins>
        "\n\n",                          // <ul> and </ul>
        "\n\n",                          // <ol> and </ol>
        "\n\n",                          // <dl> and </dl>
        "\t* \\1\n",                     // <li> and </li>
        " \\1\n",                        // <dd> and </dd>
        "\t* \\1",                       // <dt> and </dt>
        "\n\t* ",                        // <li>
        "\n-------------------------\n", // <hr>
        "<div>\n",                       // <div>
        "\n\n",                          // <table> and </table>
        "\n",                            // <tr> and </tr>
        "\t\t\\1\n",                     // <td> and </td>
        "",                              // <span class="_html2text_ignore">...</span>
        '[\\2]',                         // <img> with alt tag
    ];

    /**
     * Callback function for preg_replace_callback use.
     *
     * @param  array  $matches PREG matches
     * @return string
     */
    protected function pregCallback($matches)
    {
        switch (mb_strtolower($matches[1])) {
            case 'p':
                // Replace newlines with spaces.
                $para = str_replace("\n", " ", $matches[3]);

                // Trim trailing and leading whitespace within the tag.
                $para = trim($para);

                // Add trailing newlines for this para.
                return "\n" . $para . "\n";
            case 'br':
                return "\n";
            case 'b':
            case 'del':
            case 'strong':
                return $matches[3];
            case 'th':
                return "\t\t" . $matches[3] . "\n";
            case 'h':
                return "\n\n" . $matches[3] . "\n\n";
            case 'a':
                // override the link method
                $linkOverride = null;
                if (preg_match('/_html2text_link_(\w+)/', $matches[4], $linkOverrideMatch)) {
                    $linkOverride = $linkOverrideMatch[1];
                }
                // Remove spaces in URL (#1487805)
                $url = str_replace(' ', '', $matches[3]);

                return $this->buildlinkList($url, $matches[5], $linkOverride);
        }

        // For every tag that enters this method well defined behavior must exist.
        throw new \Exception('Do not know how to handle tag');
    }
}
