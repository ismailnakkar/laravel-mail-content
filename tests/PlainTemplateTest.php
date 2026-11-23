<?php

declare(strict_types=1);

namespace MailContent\Tests;

use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use MailContent\Audience;
use MailContent\Blocks\Block;
use MailContent\Blocks\Bullets;
use MailContent\Blocks\Button;
use MailContent\Blocks\Code;
use MailContent\Blocks\Fact;
use MailContent\Blocks\Facts;
use MailContent\Blocks\Line;
use MailContent\Blocks\Link;
use MailContent\Blocks\Links;
use MailContent\Blocks\Panel;
use MailContent\Content;
use MailContent\PlainTemplate;
use MailContent\Tests\Fixtures\Alert;
use Orchestra\Testbench\TestCase;

final class PlainTemplateTest extends TestCase
{
    public function test_blocks_print_in_order_and_the_first_button_is_the_action(): void
    {
        $message = $this->build([
            new Line('First line'),
            new Bullets(['One', 'Two']),
            new Facts([new Fact('Amount', '40 USD'), new Fact('Method', 'PayPal')]),
            new Panel("Panel one\n\nPanel two"),
            new Links([new Link('Held', 'https://example.test/held')]),
            new Button('Review', 'https://example.test/review'),
            new Line('After the button'),
            new Button('Controls', 'https://example.test/controls'),
            new Code('123456'),
        ], greeting: 'Hi Sam,');

        $this->assertSame('Subject', $message->subject);
        $this->assertSame('Heading', (string)$message->greeting);
        $this->assertSame(
            ['Hi Sam,', 'First line', 'One', 'Two', 'Amount: 40 USD', 'Method: PayPal', 'Panel one', 'Panel two', 'Held: https://example.test/held'],
            $this->decoded($message->introLines),
        );
        $this->assertSame('Review', (string)$message->actionText);
        $this->assertSame('https://example.test/review', $message->actionUrl);
        $this->assertSame(['After the button', 'Controls: https://example.test/controls', '123456'], $this->decoded($message->outroLines));
        $this->assertSame('info', $message->level);
    }

    public function test_every_kind_of_text_prints_as_written(): void
    {
        $message = $this->build([
            new Line('A <b>bold</b> claim'),
            new Bullets(['_under_']),
            new Facts([new Fact('**Label**', '*not emphasis*')]),
            new Panel('# not a heading'),
            new Links([new Link('[x](https://evil.test)', 'https://example.test/held')]),
            new Button('Pay <now>', 'https://example.test/pay'),
            new Code('`12`'),
        ], heading: '<i>Heading</i>', greeting: 'Hi [Sam](https://evil.test),');

        $html = (string)$message->render();

        foreach (['<b>', '<i>', '<em>', '<strong>', '<code>', 'href="https://evil.test"', '<now>'] as $markup) {
            $this->assertStringNotContainsString($markup, $html);
        }

        foreach ([
            '&lt;i&gt;Heading&lt;/i&gt;',
            'Hi [Sam](https://evil.test),',
            'A &lt;b&gt;bold&lt;/b&gt; claim',
            '_under_',
            '**Label**: *not emphasis*',
            '# not a heading',
            '[x](https://evil.test): https://example.test/held',
            '`12`',
        ] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        $text = app(Markdown::class)->renderText($message->markdown, $message->data())->toHtml();

        $this->assertStringContainsString('**Label**: *not emphasis*', $text);
        $this->assertStringContainsString('Pay <now>: https://example.test/pay', $text);
    }

    public function test_multibyte_text_prints_whole(): void
    {
        // م is D9 85 and ا D8 A7: no byte of a character may be split, collapsed or escaped on its own.
        $message = $this->build([new Panel("مرحبا\nسلام"), new Line('Sent · twice')]);

        $this->assertSame(['مرحبا', 'سلام', 'Sent · twice'], $this->decoded($message->introLines));
    }

    public function test_an_error_is_laravels_error_level(): void
    {
        $message = $this->build([new Button('Retry', 'https://example.test/retry')], error: true);

        $this->assertSame('error', $message->level);
        $this->assertSame([], $message->introLines);
    }

    public function test_a_block_it_has_no_layout_for_prints_its_lines(): void
    {
        $message = $this->build([new class implements Block
        {
            public function lines(): array
            {
                return ['First', 'A <b>bold</b> *claim*'];
            }
        }]);

        $this->assertSame(['First', 'A <b>bold</b> *claim*'], $this->decoded($message->introLines));
        $this->assertStringContainsString('A &lt;b&gt;bold&lt;/b&gt; *claim*', (string)$message->render());
    }

    /** @param  list<Block>  $blocks */
    private function build(array $blocks, string $heading = 'Heading', ?string $greeting = null, bool $error = false): MailMessage
    {
        $content = new Content(Alert::failed, Audience::staff, 'Subject', $heading, $blocks, $greeting, error: $error);

        return new PlainTemplate()->build($content, new class {});
    }

    /**
     * @param  array<mixed>  $lines
     * @return list<string>
     */
    private function decoded(array $lines): array
    {
        return array_map(static fn (mixed $line): string => html_entity_decode((string)$line), array_values($lines));
    }
}
