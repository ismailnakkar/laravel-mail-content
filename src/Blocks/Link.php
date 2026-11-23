<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** One link of a Links block. */
final readonly class Link
{
    public function __construct(public string $text, public string $url) {}
}
