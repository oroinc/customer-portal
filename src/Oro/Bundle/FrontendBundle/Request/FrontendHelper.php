<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The helper class to check whether the current request is a storefront or management console request.
 */
class FrontendHelper
{
    /** @var string */
    private $backendPrefix;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param string       $backendPrefix
     * @param RequestStack $requestStack
     */
    public function __construct($backendPrefix, RequestStack $requestStack)
    {
        $this->backendPrefix = $backendPrefix;
        $this->requestStack = $requestStack;
    }

    /**
     * @return bool
     */
    public function isFrontendRequest(): bool
    {
        $request = $this->requestStack->getMasterRequest();

        return
            null !== $request
            && $this->isFrontendUrl($request->getPathInfo());
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function isFrontendUrl(string $url): bool
    {
        // the least time consuming method to check whether URL is frontend
        return strpos($url, $this->backendPrefix) !== 0;
    }
}
