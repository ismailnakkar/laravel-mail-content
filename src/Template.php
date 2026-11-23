<?php

declare(strict_types=1);

namespace MailContent;

use Illuminate\Notifications\Messages\MailMessage;

/** Lays out a package's mail in the app's own design: the one class an app names in each package's config. */
interface Template
{
    /**
     * The message Laravel sends. Resolved on every send and called in the mail's locale.
     *
     * @param  object  $notifiable  who the mail is for: the member, even when it goes to a routed address such as the new
     *                              one on an email change confirmation; for a staff alert, the staff notifiable
     */
    public function build(Content $content, object $notifiable): MailMessage;
}
