<?php

declare(strict_types=1);

namespace MailContent;

use MailContent\Blocks\Block;
use UnitEnum;

/**
 * What a package mail says, before any layout: what a Template builds the message from. Every string is plain text in
 * the mail's locale, never markup: a payer's reply, a plan label or a member's name can hold _, *, [..](..) or < >, so a
 * template escapes it.
 */
final readonly class Content
{
    /** @var list<Block> the body, in print order: never a null, so a template sees real blocks alone */
    public array $blocks;

    /**
     * @param  UnitEnum  $mail  which mail this is: the sending package's own case, such as Withdrawals\Enums\Alert::failed
     * @param  list<?Block>  $blocks  the body, in print order; a null is a block left out, such as an optional one
     * @param  string|null  $greeting  the package's greeting, such as "Hi Sam,"; a template may word its own
     * @param  string|null  $preheader  the inbox preview; null lets the client take the first line
     * @param  bool  $error  something failed and its reader has to act
     */
    public function __construct(
        public UnitEnum $mail,
        public Audience $audience,
        public string $subject,
        public string $heading,
        array $blocks,
        public ?string $greeting = null,
        public ?string $preheader = null,
        public bool $error = false,
    ) {
        $this->blocks = array_values(array_filter($blocks, static fn (?Block $block): bool => $block !== null));
    }

    /**
     * This mail with another body, such as a template's own blocks around the package's: every other field kept.
     *
     * @param  list<?Block>  $blocks  the body, in print order; a null is a block left out, as in the constructor
     */
    public function withBlocks(array $blocks): self
    {
        return new self(
            mail: $this->mail,
            audience: $this->audience,
            subject: $this->subject,
            heading: $this->heading,
            blocks: $blocks,
            greeting: $this->greeting,
            preheader: $this->preheader,
            error: $this->error,
        );
    }
}
