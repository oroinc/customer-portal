<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessUrlsProviderInterface;
use Oro\Bundle\RedirectBundle\Routing\MatchedUrlDecisionMaker;

class GuestAccessDecisionMaker implements GuestAccessDecisionMakerInterface
{
    /**
     * @var MatchedUrlDecisionMaker
     */
    private $matchedUrlDecisionMaker;

    /**
     * @var GuestAccessUrlsProviderInterface
     */
    private $guestAccessUrlsProvider;

    /**
     * @param GuestAccessUrlsProviderInterface $guestAccessUrlsProvider
     * @param MatchedUrlDecisionMaker          $matchedUrlDecisionMaker
     */
    public function __construct(
        GuestAccessUrlsProviderInterface $guestAccessUrlsProvider,
        MatchedUrlDecisionMaker $matchedUrlDecisionMaker
    ) {
        $this->matchedUrlDecisionMaker = $matchedUrlDecisionMaker;
        $this->guestAccessUrlsProvider = $guestAccessUrlsProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function decide($url)
    {
        switch (true) {
            case ($this->matchedUrlDecisionMaker->matches($url) === false):
            case ($this->isAllowedUrl($url) === true):
                return GuestAccessDecisionMakerInterface::URL_ALLOW;
                break;

            case ($this->isRedirectUrl($url) === true):
                return GuestAccessDecisionMakerInterface::URL_REDIRECT;
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
        return $this->matches($this->guestAccessUrlsProvider->getAllowedUrls(), $url);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function isRedirectUrl($url)
    {
        return $this->matches($this->guestAccessUrlsProvider->getRedirectUrls(), $url);
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
