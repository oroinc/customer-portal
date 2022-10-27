<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess\Provider;

/**
 * Chain provider for guest allowed urls providers allows to extend it's behavior by adding guest access allowed urls
 * providers from other bundles.
 * This class should be injected as a dependency in services where needed a list of the allowed urls for guests.
 */
class ChainGuestAccessAllowedUrlsProvider implements GuestAccessAllowedUrlsProviderInterface
{
    /**
     * @var GuestAccessAllowedUrlsProviderInterface[]
     */
    private $providers;

    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllowedUrlsPatterns(): array
    {
        $allowedUrls = [];
        foreach ($this->providers as $provider) {
            $allowedUrls[] = $provider->getAllowedUrlsPatterns();
        }
        if (empty($allowedUrls)) {
            return $allowedUrls;
        }

        return call_user_func_array('array_merge', $allowedUrls);
    }
}
