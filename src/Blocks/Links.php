<?php

declare(strict_types=1);

namespace MailContent\Blocks;

/** Links in a list, such as the withdrawals a sweep held. */
final readonly class Links implements Block
{
    /** @param  list<Link>  $links */
    public function __construct(public array $links) {}

    /** @return list<string> "text: url" per link */
    public function lines(): array
    {
        return array_map(static fn (Link $link): string => "{$link->text}: {$link->url}", $this->links);
    }
}
