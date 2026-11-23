<?php

declare(strict_types=1);

namespace MailContent;

/** Who a mail is for. */
enum Audience
{
    /** A member, about their own account. */
    case member;

    /** The site's staff, about something to check or do. */
    case staff;
}
