<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

class GuestAccessUrlsProvider implements GuestAccessUrlsProviderInterface
{
    /**
     * @internal
     */
    const ALLOWED_URLS = [
        '^/exception/', // Required for the rendering of 404 page.
        '^/customer/user/login',
        '^/customer/user/reset-request',
        '^/customer/user/send-email',
        '^/customer/user/check-email',
        '^/customer/user/registration',
        '^/customer/user/confirm-email',
        '^/customer/user/reset',
    ];

    /**
     * @internal
     */
    const REDIRECT_URLS = ['^/$'];

    /**
     * {@inheritDoc}
     */
    public function getAllowedUrls()
    {
        return static::ALLOWED_URLS;
    }

    /**
     * {@inheritDoc}
     */
    public function getRedirectUrls()
    {
        return static::REDIRECT_URLS;
    }
}
