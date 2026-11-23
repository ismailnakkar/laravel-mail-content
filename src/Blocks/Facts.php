<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** Label and value rows, in order, each value on one line. A row whose value is blank is dropped. */
final readonly class Facts implements Block
{
    /** @var list<Fact> */
    public array $rows;

    /** @param  list<Fact>  $rows  a list, not label => value: two rows may share a label */
    public function __construct(array $rows)
    {
        $rows = array_map(static fn (Fact $row): Fact => new Fact($row->label, self::oneLine($row->value)), $rows);

        $this->rows = array_values(array_filter($rows, static fn (Fact $row): bool => $row->value !== ''));
    }

    /** @return list<string> "Label: value" per row */
    public function lines(): array
    {
        return array_map(static fn (Fact $row): string => "{$row->label}: {$row->value}", $this->rows);
    }

    /** Every run of whitespace, line breaks included, as one space, trimmed. Bytes, not /u: an invalid sequence stays. */
    private static function oneLine(string $text): string
    {
        return trim((string)preg_replace('/\s+/', ' ', $text));
    }
}
