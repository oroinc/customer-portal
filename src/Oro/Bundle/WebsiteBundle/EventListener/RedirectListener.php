<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\FrontendAttachmentBundle\Request\MediaCacheRequestHelper;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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

    /** @var MediaCacheRequestHelper|null */
    private $mediaCacheRequestHelper;

    /**
     * @param WebsiteManager     $websiteManager
     * @param WebsiteUrlResolver $websiteUrlResolver
     * @param FrontendHelper     $frontendHelper
     */
    public function __construct(
        WebsiteManager $websiteManager,
        WebsiteUrlResolver $websiteUrlResolver,
        FrontendHelper $frontendHelper
    ) {
        $this->websiteManager = $websiteManager;
        $this->urlResolver = $websiteUrlResolver;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @param MediaCacheRequestHelper|null $mediaCacheRequestHelper
     */
    public function setMediaCacheRequestHelper(?MediaCacheRequestHelper $mediaCacheRequestHelper): void
    {
        $this->mediaCacheRequestHelper = $mediaCacheRequestHelper;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
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

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    private function isSupported(GetResponseEvent $event): bool
    {
        $isSupported =
            $event->isMasterRequest()
            && !$event->getResponse() instanceof RedirectResponse
            && $this->frontendHelper->isFrontendRequest();

        if ($isSupported && $this->mediaCacheRequestHelper) {
            $isSupported = !$this->mediaCacheRequestHelper->isMediaCacheRequest();
        }

        return $isSupported;
    }

    /**
     * @param Request $request
     *
     * @return Website|null
     */
    private function getWebsite(Request $request): ?Website
    {
        $website = $request->attributes->get(RequestWebsiteProvider::REQUEST_WEBSITE_ATTRIBUTE);
        if (!$website) {
            $website = $this->websiteManager->getCurrentWebsite();
        }

        return $website;
    }

    /**
     * @param Request $request
     * @param Website $website
     *
     * @return string|null
     */
    private function getRedirectUrl(Request $request, Website $website)
    {
        $websiteUrl = $this->urlResolver->getWebsiteUrl($website, true);
        if (!$websiteUrl) {
            return null;
        }

        $redirectUrl = null;
        if (false === strpos($request->getUri(), $websiteUrl)) {
            $queryString = http_build_query($request->query->all());
            $queryString = $queryString ? '?' . $queryString : $queryString;
            $redirectUrl = $websiteUrl . $request->getPathInfo() . $queryString;
        }

        return $redirectUrl;
    }
}
