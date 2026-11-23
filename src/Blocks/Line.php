<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** A paragraph. */
final readonly class Line implements Block
{
    public function __construct(public string $text) {}

    /** @return list<string> its text */
    public function lines(): array
    {
        return [$this->text];
    }
}
