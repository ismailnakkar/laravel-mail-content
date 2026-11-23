<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** A list. */
final readonly class Bullets implements Block
{
    /** @param  list<string>  $items */
    public function __construct(public array $items) {}

    /** @return list<string> each item */
    public function lines(): array
    {
        return $this->items;
    }
}
