<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Provider\RedirectAfterLoginProvider;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;

/**
 * The default implementation of the target path provider.
 */
class SignInTargetPathProvider implements SignInTargetPathProviderInterface
{
    public function __construct(
        private RedirectAfterLoginProvider $redirectAfterLoginProvider,
        private SameSiteUrlHelper $sameSiteUrlHelper,
    ) {
    }

    #[\Override]
    public function getTargetPath(): ?string
    {
        $targetUrl = $this->redirectAfterLoginProvider->getRedirectTargetUrl();
        if (\is_string($targetUrl) && $this->sameSiteUrlHelper->isSameSiteUrl($targetUrl)) {
            return $targetUrl;
        }

        return null;
    }
}
