<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** Text set apart, such as a payer's reply. It keeps its line breaks. */
final readonly class Panel implements Block
{
    public function __construct(public string $text) {}

    /** @return list<string> its lines, each trimmed, blank ones dropped */
    public function lines(): array
    {
        // Not \R: with no /u it also matches the byte 0x85, which is inside a character such as م.
        return preg_split('/\s*[\r\n]\s*/', trim($this->text), flags: PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
