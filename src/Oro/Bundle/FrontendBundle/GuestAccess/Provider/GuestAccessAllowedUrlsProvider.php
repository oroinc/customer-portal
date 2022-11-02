<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

/**
 * Provides a list of patterns for URLs for which access is granted for non-authenticated visitors.
 */
class GuestAccessAllowedUrlsProvider implements GuestAccessAllowedUrlsProviderInterface
{
    /** @var string[] */
    private $allowedUrls = [
        // Required for the rendering of 404 page.
        '^/exception/',
        // Internal URLs and assets.
        '^/_profiler',
        '^/_wdt',
        '^/_fragment',
        '^/js/',
        '^/media/js/',
        // Images
        '^/media/cache/',
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
        '^/localization/set-current-localization$',
        '^/productprice/set-current-currency$',
        '^/cookies-accepted$',
    ];

    /**
     * Adds a pattern to the list of allowed URL patterns.
     *
     * @param string $pattern
     */
    public function addAllowedUrlPattern($pattern)
    {
        $this->allowedUrls[] = $pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllowedUrlsPatterns(): array
    {
        return $this->allowedUrls;
    }
}
