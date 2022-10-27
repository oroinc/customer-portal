<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * The default implementation of the guest access decision maker
 * that allow access to all management console urls and all allowed storefront urls.
 */
class GuestAccessDecisionMaker implements GuestAccessDecisionMakerInterface
{
    /** @var GuestAccessAllowedUrlsProviderInterface */
    private $guestAccessAllowedUrlsProvider;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(
        GuestAccessAllowedUrlsProviderInterface $guestAccessAllowedUrlsProvider,
        FrontendHelper $frontendHelper
    ) {
        $this->guestAccessAllowedUrlsProvider = $guestAccessAllowedUrlsProvider;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function decide(string $url): int
    {
        if (!$this->frontendHelper->isFrontendUrl($url)) {
            return self::URL_ALLOW;
        }

        if ($this->matches($this->guestAccessAllowedUrlsProvider->getAllowedUrlsPatterns(), $url)) {
            return self::URL_ALLOW;
        }

        return self::URL_DISALLOW;
    }

    private function matches(array $urlPatterns, string $url): bool
    {
        foreach ($urlPatterns as $pattern) {
            if (preg_match('{' . $pattern . '}', rawurldecode($url))) {
                return true;
            }
        }

        return false;
    }
}
