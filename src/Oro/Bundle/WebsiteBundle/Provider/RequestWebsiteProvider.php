<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The provider of the website for the current storefront request.
 */
class RequestWebsiteProvider
{
    public const REQUEST_WEBSITE_ATTRIBUTE = 'current_website';

    /** @var RequestStack */
    private $requestStack;

    /** @var WebsiteManager */
    private $websiteManager;

    public function __construct(RequestStack $requestStack, WebsiteManager $websiteManager)
    {
        $this->requestStack = $requestStack;
        $this->websiteManager = $websiteManager;
    }

    public function getWebsite(): ?Website
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return null;
        }

        if ($request->attributes->has(self::REQUEST_WEBSITE_ATTRIBUTE)) {
            return $request->attributes->get(self::REQUEST_WEBSITE_ATTRIBUTE);
        }

        $website = $this->websiteManager->getCurrentWebsite();
        $request->attributes->set(self::REQUEST_WEBSITE_ATTRIBUTE, $website);

        return $website;
    }
}
