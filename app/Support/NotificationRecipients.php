<?php

namespace App\Support;

class NotificationRecipients
{
    /**
     * Recipients for contact form and quote request notification emails.
     *
     * Only production sends to the full distribution list; every other
     * environment sends to a single inbox to avoid spamming Jean/James
     * while testing.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        if (! app()->environment('production')) {
            return ['ngmcleaning2026@gmail.com'];
        }

        return [
            'ngmcleaning2026@gmail.com',
            'jnguillaume4@gmail.com',
            'jldajeune@gmail.com',
        ];
    }
}
