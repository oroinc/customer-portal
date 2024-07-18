<?php

namespace Oro\Bundle\WebsiteBundle\Captcha;

use Oro\Bundle\FormBundle\Captcha\ReCaptchaService as BaseService;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;

/**
 * Provides website URL for frontend requests instead of application_url.
 */
class ReCaptchaService extends BaseService
{
    private WebsiteUrlResolver $urlResolver;
    private WebsiteManager $websiteManager;
    private FrontendHelper $frontendHelper;

    public function setUrlResolver(WebsiteUrlResolver $urlResolver): void
    {
        $this->urlResolver = $urlResolver;
    }

    public function setWebsiteManager(WebsiteManager $websiteManager): void
    {
        $this->websiteManager = $websiteManager;
    }

    public function setFrontendHelper(FrontendHelper $frontendHelper): void
    {
        $this->frontendHelper = $frontendHelper;
    }

    protected function getCurrentUrl(): string
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return $this->urlResolver->getWebsiteUrl($this->websiteManager->getCurrentWebsite());
        }

        return parent::getCurrentUrl();
    }
}
