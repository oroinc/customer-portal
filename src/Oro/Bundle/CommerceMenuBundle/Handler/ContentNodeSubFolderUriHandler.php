<?php

namespace Oro\Bundle\CommerceMenuBundle\Handler;

use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Symfony\Component\Routing\RequestContext;

/**
 * Prepends subfolder to the content node uri if it exists.
 */
class ContentNodeSubFolderUriHandler
{
    public function __construct(
        private LocalizationHelper $localizationHelper,
        private RequestContext $requestContext
    ) {
    }

    public function handle(ResolvedContentNode $resolvedNode, ?Localization $localization): string
    {
        $uri = $this->localizationHelper->getLocalizedValue(
            $resolvedNode->getResolvedContentVariant()->getLocalizedUrls(),
            $localization
        );

        $uri =  $uri ? $uri->getString() : '';

        return $this->getBaseUrl() . $uri;
    }

    private function getBaseUrl(): string
    {
        return $this->requestContext->getBaseUrl() ?: '';
    }
}
