<?php

declare(strict_types=1);

namespace MailContent\Tests;

use MailContent\Audience;
use MailContent\Blocks\Block;
use MailContent\Blocks\Button;
use MailContent\Blocks\Line;
use MailContent\Blocks\Panel;
use MailContent\Content;
use MailContent\Tests\Fixtures\Alert;
use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase
{
    public function test_a_null_block_is_left_out_and_the_rest_keep_their_order_as_a_list(): void
    {
        $line = new Line('The payout provider refused it.');
        $panel = new Panel('Sent it twice');
        $button = new Button('Review', 'https://example.test/review');

        $content = $this->content([null, $line, null, $panel, $button, null]);

        // assertSame on arrays checks each key too, so the blocks are re-indexed 0, 1, 2.
        $this->assertSame([$line, $panel, $button], $content->blocks);
    }

    public function test_a_content_of_only_null_blocks_has_no_blocks(): void
    {
        $this->assertSame([], $this->content([null, null])->blocks);
    }

    public function test_with_blocks_keeps_every_other_field_drops_nulls_and_leaves_the_original_as_it_was(): void
    {
        $original = new Line('Original');
        $content = new Content(
            mail: Alert::failed,
            audience: Audience::staff,
            subject: 'Subject',
            heading: 'Heading',
            blocks: [$original],
            greeting: 'Hi Sam,',
            preheader: 'Preview',
            error: true,
        );
        $line = new Line('Replaced');
        $button = new Button('Review', 'https://example.test/review');

        $changed = $content->withBlocks([null, $line, null, $button]);

        $this->assertSame([$line, $button], $changed->blocks);
        $this->assertSame([$original], $content->blocks);
        // Every other field matches. The fixture sets each one off its default, so a field left
        // uncopied fails here; a field Content gains later needs a non-default value above too.
        $this->assertSame(
            [...get_object_vars($content), 'blocks' => null],
            [...get_object_vars($changed), 'blocks' => null],
        );
    }

    /** @param  list<?Block>  $blocks */
    private function content(array $blocks): Content
    {
        return new Content(Alert::failed, Audience::staff, 'Subject', 'Heading', $blocks);
    }
}
