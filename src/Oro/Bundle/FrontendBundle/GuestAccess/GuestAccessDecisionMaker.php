<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class GuestAccessDecisionMaker implements GuestAccessDecisionMakerInterface
{
    /**
     * @var GuestAccessAllowedUrlsProviderInterface
     */
    private $guestAccessAllowedUrlsProvider;

    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @param GuestAccessAllowedUrlsProviderInterface $guestAccessAllowedUrlsProvider
     * @param FrontendHelper                          $frontendHelper
     * @param bool                                    $installed
     */
    public function __construct(
        GuestAccessAllowedUrlsProviderInterface $guestAccessAllowedUrlsProvider,
        FrontendHelper $frontendHelper,
        $installed
    ) {
        $this->guestAccessAllowedUrlsProvider = $guestAccessAllowedUrlsProvider;
        $this->frontendHelper = $frontendHelper;
        $this->installed = $installed;
    }

    /**
     * {@inheritDoc}
     */
    public function decide($url)
    {
        switch (true) {
            case ($this->installed === false):
            case ($this->frontendHelper->isFrontendUrl($url) === false):
            case ($this->isAllowedUrl($url) === true):
                return GuestAccessDecisionMakerInterface::URL_ALLOW;
                break;

            default:
                return GuestAccessDecisionMakerInterface::URL_DISALLOW;
        }
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function isAllowedUrl($url)
    {
        return $this->matches($this->guestAccessAllowedUrlsProvider->getAllowedUrlsPatterns(), $url);
    }

    /**
     * @param array  $urlPatterns
     * @param string $url
     *
     * @return bool
     */
    private function matches(array $urlPatterns, $url)
    {
        foreach ($urlPatterns as $pattern) {
            if (preg_match('{' . $pattern . '}', rawurldecode($url))) {
                return true;
            }
        }

        return false;
    }
}
