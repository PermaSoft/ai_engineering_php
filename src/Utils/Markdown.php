<?php

declare(strict_types=1);

namespace App\Utils;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

final readonly class Markdown
{
    public function __construct(
        private ConverterInterface $converter,
    ) {
    }

    /**
     * Converts Markdown to HTML.
     */
    public function toHtml(string $text): UnicodeString
    {
        try {
            $html = $this->converter->convert($text)->getContent();
        } catch (CommonMarkException $e) {
            $html = '';
        }

        return u($html);
    }
}
