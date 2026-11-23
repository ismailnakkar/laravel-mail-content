# laravel-mail-content

The shared contract for package mail: what a mail says, as ordered plain-text blocks, and the one template your app lays
every package's mail out with. Interfaces and value objects only, plus a default template: no service provider, config,
views or translations.

Stable: from 1.0 it grows only by addition. A template prints the `lines()` of a block it has no layout for, so a new
block type may come in a 1.x minor and older templates still print it. A new `Template` method or `Audience` case is
still a break and waits for a major.

## Install

Needs PHP 8.4+ and Laravel 13. The packages that send mail require it; an app that writes its own template uses its
classes, so it requires it too:

```bash
composer require ismailnakkar/laravel-mail-content
```

## Your template

A package builds a `MailContent\Content` for every mail it sends, then hands it to the `MailContent\Template` its
`notifications.template` config names. One class serves every package. With
[laravel-mail-frame](https://github.com/ismailnakkar/laravel-mail-frame), `content()` lays the whole mail out and your
template picks the tone:

```php
use Illuminate\Notifications\Messages\MailMessage;
use MailContent\Content;
use MailContent\Template;
use MailFrame\Message;

final class SiteTemplate implements Template
{
    public function build(Content $content, object $notifiable): MailMessage
    {
        return new Message(MailTone::routine)
            ->tone($content->error ? MailTone::security : MailTone::routine)
            ->content($content);
    }
}
```

Name it in each package's config, such as `'template' => SiteTemplate::class` under `notifications` in
`config/withdrawals.php`. To build the message any other way, read `$content->blocks` in order with `instanceof`, print
the `lines()` of a block you have no layout for, and escape every text (see Escaping).

The template is resolved from the container on every send, and `build()` is called in the mail's locale. `$notifiable` is who the mail is
for: the member, even when the mail goes to a routed address (such as the new address on an email change confirmation),
or for a staff alert the staff notifiable.

While the config names no template, a package uses `MailContent\PlainTemplate`: Laravel's own notification layout. The
heading is the line Laravel prints large, the greeting the first line, the first button the action, and every other
block prints its `lines()` in order; `error` makes it Laravel's error mail. Its lines and action text are
`HtmlString`s of character references, so a test compares `html_entity_decode((string)$line)`.

## Escaping

All text in the contract is plain text, never markup, and your template escapes it. A payer's reply, a plan label or a
member's name can hold `_`, `*`, `[..](..)` and `< >`. Escape for where it lands: `e()` in an HTML view, and in Laravel's
Markdown mail also Markdown's own marks, which `PlainTemplate` writes as character references. A block's `url` is an
absolute URL: escape it with `e()` in an `href`.

## The contract

`MailContent\Content`, readonly:

| Property    | Type          | Meaning                                                                                         |
|-------------|---------------|-------------------------------------------------------------------------------------------------|
| `mail`      | `UnitEnum`    | Which mail this is: the sending package's own case, such as `Withdrawals\Enums\Alert::failed`.  |
| `audience`  | `Audience`    | `Audience::member` or `Audience::staff`.                                                        |
| `subject`   | `string`      | The subject line.                                                                               |
| `heading`   | `string`      | The title of the mail.                                                                          |
| `blocks`    | `list<Block>` | The body, in print order. A `null` block passed in is left out, so this list holds no `null`.   |
| `greeting`  | `?string`     | The package's greeting, such as `Hi Sam,`. Your template may word its own.                      |
| `preheader` | `?string`     | The inbox preview. `null` lets the client take the first line.                                  |
| `error`     | `bool`        | Something failed and its reader has to act.                                                     |

The blocks, each a readonly class in `MailContent\Blocks` implementing `Block`, whose `lines(): list<string>` gives the
block as plain-text lines:

| Block     | Properties                                  | Prints                                    | `lines()`                       |
|-----------|---------------------------------------------|-------------------------------------------|---------------------------------|
| `Line`    | `text`                                      | A paragraph.                              | Its text.                       |
| `Bullets` | `items`, a list of strings                  | A list.                                   | Each item.                      |
| `Facts`   | `rows`, a list of `Fact` (`label`, `value`) | Label and value rows.                     | `Label: value` per row.         |
| `Panel`   | `text`                                      | Text set apart. It keeps its line breaks. | Its lines, blank ones dropped.  |
| `Links`   | `links`, a list of `Link` (`text`, `url`)   | Links in a list.                          | `text: url` per link.           |
| `Button`  | `text`, `url`                               | The thing to do.                          | `text: url`.                    |
| `Code`    | `code`                                      | A one-time code, to set apart.            | The code.                       |

`Facts` drops a row whose value is blank and puts every value on one line. Its rows are a list, never `label => value`:
two rows may share a label. A sending package builds a mail's content like this, one block per line, with `null`
for a block it leaves out:

```php
new Content(
    mail: Alert::failed,
    audience: Audience::staff,
    subject: 'Withdrawal #42 failed',
    heading: 'A withdrawal failed',
    blocks: [
        new Line('The payout provider refused it.'),
        new Facts([new Fact('Withdrawal', '#42'), new Fact('Method', 'PayPal')]),
        $reply === null ? null : new Panel($reply),
        new Button('Review withdrawal', $url),
    ],
    error: true,
);
```

## Upgrading

### From 1.0

Nothing to change. New: `Content` takes `null` in `blocks` and leaves it out, so an optional block is written in its
place, such as `$reply === null ? null : new Panel($reply)`. Code that writes one requires `^1.1`, as 1.0 hands the
`null` to the template. `$content->blocks` still holds real blocks alone.

## Licence

MIT.
