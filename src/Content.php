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
    /**
     * @param  UnitEnum  $mail  which mail this is: the sending package's own case, such as Withdrawals\Enums\Alert::failed
     * @param  list<Block>  $blocks  the body, in print order
     * @param  string|null  $greeting  the package's greeting, such as "Hi Sam,"; a template may word its own
     * @param  string|null  $preheader  the inbox preview; null lets the client take the first line
     * @param  bool  $error  something failed and its reader has to act
     */
    public function __construct(
        public UnitEnum $mail,
        public Audience $audience,
        public string $subject,
        public string $heading,
        public array $blocks,
        public ?string $greeting = null,
        public ?string $preheader = null,
        public bool $error = false,
    ) {}
}
