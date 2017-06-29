<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

class GuestAccessAllowedUrlsProvider implements GuestAccessAllowedUrlsProviderInterface
{
    /**
     * @internal
     */
    const ALLOWED_URLS = [
        // Required for the rendering of 404 page.
        '^/exception/',
        // Internal URLs and assets.
        '^/_profiler',
        '^/_wdt',
        '^/_fragment',
        '^/js/',
        '^/api/',
        // Allow embedded forms.
        '^/embedded-form',
        // Explicitly allowed URLs.
        '^/customer/user/login$',
        '^/customer/user/reset-request$',
        '^/customer/user/send-email$',
        '^/customer/user/check-email$',
        '^/customer/user/registration$',
        '^/customer/user/confirm-email$',
        '^/customer/user/reset$',
    ];

    /**
     * {@inheritDoc}
     */
    public function getAllowedUrlsPatterns()
    {
        return static::ALLOWED_URLS;
    }
}
