<?php

declare(strict_types=1);

namespace MailContent;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use MailContent\Blocks\Button;
use MailContent\Blocks\LinkedLine;

/**
 * The template a package uses while the app names none: Laravel's own notification layout. The heading is the line it
 * prints large, the first button is the action, a linked line is a paragraph with a Markdown link, and every other block
 * prints its lines().
 */
final class PlainTemplate implements Template
{
    public function build(Content $content, object $notifiable): MailMessage
    {
        // Every text an Htmlable, which Laravel's template prints as it is: it would escape a string's references again.
        // @phpstan-ignore argument.type (Laravel types the greeting a string, and its template prints an Htmlable)
        $message = new MailMessage()->subject($content->subject)->greeting(self::escape($content->heading));

        if ($content->greeting !== null) {
            $message->line(self::escape($content->greeting));
        }

        // Lines before the action are intro lines and after it outro lines: MailMessage sorts them by call order.
        foreach ($content->blocks as $block) {
            if ($block instanceof Button && $message->actionText === null) {
                // @phpstan-ignore argument.type (escaped for the line under the card too, where Laravel prints it raw)
                $message->action(self::escape($block->text), $block->url);
            } elseif ($block instanceof LinkedLine) {
                $message->line(self::linked($block));
            } else {
                $message->lines(array_map(self::escape(...), $block->lines()));
            }
        }

        return $content->error ? $message->error() : $message;
    }

    /**
     * Text that Markdown prints as written: on one line (a blank one starts a new block), each ASCII punctuation mark a
     * character reference, which the text part decodes.
     */
    private static function escape(string $text): HtmlString
    {
        return new HtmlString(self::marks(self::oneLine($text)));
    }

    /** The line with its link as Markdown's own: the URL as references too, which Markdown decodes in a destination. */
    private static function linked(LinkedLine $line): HtmlString
    {
        $link = '[' . self::marks($line->link->text) . '](' . self::marks($line->link->url) . ')';

        return new HtmlString(self::oneLine(self::marks($line->before) . $link . self::marks($line->after)));
    }

    /** Each ASCII punctuation mark as a character reference. */
    private static function marks(string $text): string
    {
        // No /u: [[:punct:]] then matches ASCII alone, and a multibyte character such as · passes whole.
        return (string)preg_replace_callback('/[[:punct:]]/', static fn (array $mark): string => '&#' . ord($mark[0]) . ';', $text);
    }

    /** Every run of whitespace, line breaks included, as one space, trimmed. Bytes, not /u: an invalid sequence stays. */
    private static function oneLine(string $text): string
    {
        return trim((string)preg_replace('/\s+/', ' ', $text));
    }
}
