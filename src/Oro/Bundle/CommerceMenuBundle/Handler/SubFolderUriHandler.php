<?php

namespace Oro\Bundle\CommerceMenuBundle\Handler;

use Oro\Bundle\UIBundle\Tools\UrlHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepends subfolder to the uri if it exists
 */
class SubFolderUriHandler
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlHelper $urlHelper
    ) {
    }

    public function handle(string $uri): string
    {
        return $this->urlHelper->getAbsolutePath($uri);
    }

    public function hasSubFolder(): bool
    {
        return !empty($this->requestStack->getMainRequest()?->server->get('WEBSITE_PATH'));
    }
}
