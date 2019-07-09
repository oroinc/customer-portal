<?php

namespace Oro\Bundle\WebsiteBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Redirect listener which make redirect to the default site url
 */
class RedirectListener
{
    const CURRENT_WEBSITE = 'current_website';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var WebsiteUrlResolver
     */
    protected $urlResolver;

    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @param ConfigManager $configManager
     * @param WebsiteManager $websiteManager
     * @param WebsiteUrlResolver $websiteUrlResolver
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(
        ConfigManager $configManager,
        WebsiteManager $websiteManager,
        WebsiteUrlResolver $websiteUrlResolver,
        FrontendHelper $frontendHelper
    ) {
        $this->websiteManager = $websiteManager;
        $this->configManager = $configManager;
        $this->urlResolver = $websiteUrlResolver;
        $this->frontendHelper = $frontendHelper;
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

        /** @var Website $website */
        $website = $request->attributes->get(
            self::CURRENT_WEBSITE,
            $this->websiteManager->getCurrentWebsite()
        );
        if (!$website) {
            return;
        }

        $redirectUrl = $this->getRedirectUrl($request, $website);
        if ($redirectUrl) {
            $response = new RedirectResponse($redirectUrl);
            $event->setResponse($response);

            return;
        }
    }

    /**
     * @param GetResponseEvent $event
     * @return bool
     */
    protected function isSupported(GetResponseEvent $event)
    {
        return $event->isMasterRequest()
            && $this->frontendHelper->isFrontendRequest()
            && !$event->getResponse() instanceof RedirectResponse;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getCleanUrl($url)
    {
        return rtrim(explode('?', $url)[0], '/');
    }

    /**
     * @param Request $request
     * @param Website $website
     * @return null|string
     */
    protected function getRedirectUrl(Request $request, Website $website)
    {
        $redirectUrl = null;
        $requestUri = $request->getUri();
        $websiteUrl = $this->getCleanUrl($this->urlResolver->getWebsiteUrl($website));

        if ($websiteUrl && false === strpos($requestUri, $websiteUrl)) {
            $queryString = http_build_query($request->query->all());
            $queryString = $queryString ? '?' . $queryString : $queryString;
            $redirectUrl = $websiteUrl . $request->getPathInfo() . $queryString;
        }

        return $redirectUrl;
    }
}
