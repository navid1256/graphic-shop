<?php

namespace Tests\Unit;

use App\Utilities\HtmlToPlainText;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlToPlainTextTest extends TestCase
{
    #[DataProvider('htmlDescriptions')]
    public function test_html_descriptions_are_converted_to_plain_text(string $html, string $expected): void
    {
        $this->assertSame($expected, HtmlToPlainText::convert($html));
    }

    public static function htmlDescriptions(): array
    {
        return [
            'paragraph and non-breaking space' => [
                '<p>پوستر بازی Lies Of P با کیفیت بالا&nbsp;</p>',
                'پوستر بازی Lies Of P با کیفیت بالا',
            ],
            'paragraphs keep their separation' => [
                '<p>First paragraph</p><p>Second paragraph</p>',
                "First paragraph\nSecond paragraph",
            ],
            'line breaks keep their separation' => [
                'First line<br>Second line',
                "First line\nSecond line",
            ],
            'plain text is unchanged' => [
                'Plain description',
                'Plain description',
            ],
        ];
    }
}
