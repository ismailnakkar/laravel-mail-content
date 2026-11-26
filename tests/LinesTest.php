<?php

declare(strict_types=1);

namespace MailContent\Tests;

use MailContent\Blocks\Block;
use MailContent\Blocks\Bullets;
use MailContent\Blocks\Button;
use MailContent\Blocks\Code;
use MailContent\Blocks\Fact;
use MailContent\Blocks\Facts;
use MailContent\Blocks\Line;
use MailContent\Blocks\Link;
use MailContent\Blocks\LinkedLine;
use MailContent\Blocks\Links;
use MailContent\Blocks\Panel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LinesTest extends TestCase
{
    /** @return iterable<string, array{Block, list<string>}> */
    public static function blocks(): iterable
    {
        yield 'a line, its text' => [new Line('The payout provider refused it.'), ['The payout provider refused it.']];
        yield 'bullets, each item' => [new Bullets(['One', 'Two']), ['One', 'Two']];
        yield 'facts, label: value per row' => [
            new Facts([new Fact('Amount', '$40.00'), new Fact('Method', 'PayPal')]),
            ['Amount: $40.00', 'Method: PayPal'],
        ];
        yield 'a panel, its lines trimmed, blank ones dropped' => [new Panel("  Sent it  \r\n\r\n  twice  "), ['Sent it', 'twice']];
        // م is D9 85: a byte-wise \R would split it.
        yield 'a panel, a character that holds the byte 0x85 whole' => [new Panel("مرحبا\nسلام"), ['مرحبا', 'سلام']];
        yield 'links, text: url each' => [
            new Links([new Link('#41', 'https://example.test/41'), new Link('#42', 'https://example.test/42')]),
            ['#41: https://example.test/41', '#42: https://example.test/42'],
        ];
        yield 'a button, text: url' => [new Button('Review', 'https://example.test/review'), ['Review: https://example.test/review']];
        yield 'a code, the code' => [new Code('123456'), ['123456']];
        yield 'a linked line, text <url> in the link\'s place' => [
            new LinkedLine('If not, :link now.', new Link('contact support', 'https://example.test/contact')),
            ['If not, contact support <https://example.test/contact> now.'],
        ];
        yield 'a linked line without the placeholder, the link after the text' => [
            new LinkedLine('If not, contact us. ', new Link('Contact', 'https://example.test/contact')),
            ['If not, contact us. Contact <https://example.test/contact>'],
        ];
    }

    /** @param  list<string>  $lines */
    #[DataProvider('blocks')]
    public function test_a_block_gives_its_plain_text_lines(Block $block, array $lines): void
    {
        $this->assertSame($lines, $block->lines());
    }

    public function test_a_linked_line_splits_at_the_first_placeholder_only(): void
    {
        $line = new LinkedLine(':link first, :link stays text', new Link('Here', 'https://example.test'));

        $this->assertSame(['', ' first, :link stays text'], [$line->before, $line->after]);
    }
}
