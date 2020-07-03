<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The helper class to check whether the current request is the storefront or management console request.
 */
class MediaCacheRequestHelper
{
    /** @var RequestStack */
    private $requestStack;

    /** @var string */
    private $mediaCachePrefix;

    /**
     * @param RequestStack $requestStack
     * @param string $mediaCachePrefix
     */
    public function __construct(RequestStack $requestStack, string $mediaCachePrefix)
    {
        $this->requestStack = $requestStack;
        $this->mediaCachePrefix =  '/' . trim($mediaCachePrefix, '/') . '/';
    }

    /**
     * Checks whether the current HTTP request is a request for media cache.
     *
     * @param Request|null $request
     *
     * @return bool
     */
    public function isMediaCacheRequest(?Request $request = null): bool
    {
        $request = $request ?? $this->requestStack->getMasterRequest();

        return $request && strpos($request->getPathInfo(), $this->mediaCachePrefix) === 0;
    }
}
