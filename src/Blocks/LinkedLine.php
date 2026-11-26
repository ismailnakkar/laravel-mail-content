<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/**
 * A paragraph with one link inside it. The text holds PLACEHOLDER where the link's text goes, so a translated sentence
 * places the link where its grammar wants. Only the link is markup: before and after are plain text.
 */
final readonly class LinkedLine implements Block
{
    /** Where the link goes in the text: Laravel's placeholder syntax, which translators keep. */
    public const string PLACEHOLDER = ':link';

    /** The text up to the link. */
    public string $before;

    /** The text after the link. */
    public string $after;

    /** @param  string  $text  holds PLACEHOLDER once; without it, the link follows the text */
    public function __construct(public string $text, public Link $link)
    {
        [$this->before, $this->after] = str_contains($text, self::PLACEHOLDER)
            ? explode(self::PLACEHOLDER, $text, 2)
            : [rtrim($text) . ' ', ''];
    }

    /** @return list<string> the text with "text <url>" in the link's place */
    public function lines(): array
    {
        return ["{$this->before}{$this->link->text} <{$this->link->url}>{$this->after}"];
    }
}
