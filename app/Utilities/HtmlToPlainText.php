<?php

namespace App\Utilities;

class HtmlToPlainText
{
    public static function convert(string $html): string
    {
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<\s*br\b[^>]*>|<\/\s*(?:p|div|li|h[1-6])\s*>/i', "\n", $text);
        $text = strip_tags($text);
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = preg_replace('/[ \t]*\n[ \t]*/u', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
