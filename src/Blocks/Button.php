<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** The thing to do, as a button. */
final readonly class Button implements Block
{
    public function __construct(public string $text, public string $url) {}

    /** @return list<string> "text: url" */
    public function lines(): array
    {
        return ["{$this->text}: {$this->url}"];
    }
}
