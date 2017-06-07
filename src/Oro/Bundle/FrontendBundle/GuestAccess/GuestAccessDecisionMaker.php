<?php

namespace Oro\Bundle\FrontendBundle\GuestAccess;

use Oro\Bundle\FrontendBundle\GuestAccess\Provider\GuestAccessAllowedUrlsProviderInterface;
use Oro\Bundle\RedirectBundle\Routing\MatchedUrlDecisionMaker;

class GuestAccessDecisionMaker implements GuestAccessDecisionMakerInterface
{
    /**
     * @var MatchedUrlDecisionMaker
     */
    private $matchedUrlDecisionMaker;

    /**
     * @var GuestAccessAllowedUrlsProviderInterface
     */
    private $guestAccessAllowedUrlsProvider;

    /**
     * @param GuestAccessAllowedUrlsProviderInterface $guestAccessAllowedUrlsProvider
     * @param MatchedUrlDecisionMaker                 $matchedUrlDecisionMaker
     */
    public function __construct(
        GuestAccessAllowedUrlsProviderInterface $guestAccessAllowedUrlsProvider,
        MatchedUrlDecisionMaker $matchedUrlDecisionMaker
    ) {
        $this->matchedUrlDecisionMaker = $matchedUrlDecisionMaker;
        $this->guestAccessAllowedUrlsProvider = $guestAccessAllowedUrlsProvider;
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
