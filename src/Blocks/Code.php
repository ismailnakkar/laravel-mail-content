<?php

declare(strict_types=1);

namespace MailContent\Blocks;

use SensitiveParameter;

/** A one-time code, to set apart. */
final readonly class Code implements Block
{
    public function __construct(#[SensitiveParameter] public string $code) {}

    /** @return list<string> the code */
    public function lines(): array
    {
        return [$this->code];
    }
}
