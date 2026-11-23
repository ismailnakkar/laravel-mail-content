<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** One row of a Facts block. */
final readonly class Fact
{
    public function __construct(public string $label, public string $value) {}
}
