<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** A part of a mail's body, in Content::$blocks. Its text is plain text, never markup. */
interface Block
{
    /**
     * The block as plain-text lines, in order: what a template prints for a block it has no layout for, so a block type
     * newer than the template still prints.
     *
     * @return list<string>
     */
    public function lines(): array;
}
