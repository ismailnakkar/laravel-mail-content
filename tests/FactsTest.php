<?php

declare(strict_types=1);

namespace MailContent\Tests;

use MailContent\Blocks\Fact;
use MailContent\Blocks\Facts;
use PHPUnit\Framework\TestCase;

final class FactsTest extends TestCase
{
    public function test_blank_values_are_dropped_and_each_value_is_one_line(): void
    {
        $facts = new Facts([
            new Fact('Amount', '$40.00'),
            new Fact('Reason', ''),
            new Fact('Note', " \n\t "),
            new Fact('Reply', "  Sent it\r\n\r\n  twice  "),
        ]);

        $this->assertEquals([new Fact('Amount', '$40.00'), new Fact('Reply', 'Sent it twice')], $facts->rows);
    }

    public function test_two_rows_may_share_a_label(): void
    {
        $facts = new Facts([new Fact('Address', '0xabc'), new Fact('Address', '0xdef')]);

        $this->assertEquals([new Fact('Address', '0xabc'), new Fact('Address', '0xdef')], $facts->rows);
    }

    public function test_a_value_with_an_invalid_byte_stays(): void
    {
        $facts = new Facts([new Fact('Reply', "Sent \xC3 twice")]);

        $this->assertSame("Sent \xC3 twice", $facts->rows[0]->value);
    }
}
