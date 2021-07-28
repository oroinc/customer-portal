<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Makes redirect to the default site URL if URL does not contain the default site URL.
 */
class RedirectListener
{
    /** @var WebsiteManager */
    private $websiteManager;

    /** @var WebsiteUrlResolver */
    private $urlResolver;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(
        WebsiteManager $websiteManager,
        WebsiteUrlResolver $websiteUrlResolver,
        FrontendHelper $frontendHelper
    ) {
        $this->websiteManager = $websiteManager;
        $this->urlResolver = $websiteUrlResolver;
        $this->frontendHelper = $frontendHelper;
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->isSupported($event)) {
            return;
        }

        $request = $event->getRequest();

        $website = $this->getWebsite($request);
        if (null === $website) {
            return;
        }

        $redirectUrl = $this->getRedirectUrl($request, $website);
        if ($redirectUrl) {
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    private function isSupported(RequestEvent $event): bool
    {
        return
            $event->isMasterRequest()
            && !$event->getResponse() instanceof RedirectResponse
            && $this->frontendHelper->isFrontendRequest()
            && !str_starts_with($event->getRequest()->getPathInfo(), '/media/cache/');
    }

    private function getWebsite(Request $request): ?Website
    {
        $website = $request->attributes->get(RequestWebsiteProvider::REQUEST_WEBSITE_ATTRIBUTE);
        if (!$website) {
            $website = $this->websiteManager->getCurrentWebsite();
        }

        return $website;
    }

    private function getRedirectUrl(Request $request, Website $website): ?string
    {
        $websiteUrl = $this->urlResolver->getWebsiteUrl($website, true);
        if (!$websiteUrl) {
            return null;
        }

        $redirectUrl = null;
        if (!str_contains($request->getUri(), $websiteUrl)) {
            $queryString = http_build_query($request->query->all());
            $queryString = $queryString ? '?' . $queryString : $queryString;
            $redirectUrl = $websiteUrl . $request->getPathInfo() . $queryString;
        }

        return $redirectUrl;
    }
}
