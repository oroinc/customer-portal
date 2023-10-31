<?php

namespace Oro\Bundle\CustomerBundle\Api\ApiDoc;

use Oro\Bundle\ApiBundle\ApiDoc\DocumentationProviderInterface;
use Oro\Bundle\ApiBundle\Provider\ResourcesProvider;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Request\Version;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Builds a documentation about the storefront API resources that can be used by non-authenticated visitors.
 */
class NonAuthenticatedVisitorsDocumentationProvider implements DocumentationProviderInterface
{
    private array $apiResources;
    private ConfigManager $configManager;
    private ValueNormalizer $valueNormalizer;
    private ResourcesProvider $resourcesProvider;

    public function __construct(
        array $apiResources,
        ConfigManager $configManager,
        ValueNormalizer $valueNormalizer,
        ResourcesProvider $resourcesProvider
    ) {
        $this->apiResources = $apiResources;
        $this->configManager = $configManager;
        $this->valueNormalizer = $valueNormalizer;
        $this->resourcesProvider = $resourcesProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getDocumentation(RequestType $requestType): ?string
    {
        if (!$this->configManager->get('oro_customer.non_authenticated_visitors_api')) {
            return null;
        }

        $apiResources = $this->getNonAuthenticatedVisitorsApiResources($requestType);
        if (empty($apiResources)) {
            return null;
        }

        $items = [];
        foreach ($apiResources as $apiResource) {
            $items[] = '- ' . $apiResource;
        }

        return sprintf($this->getTemplate(), implode("\n", $items));
    }

    private function getNonAuthenticatedVisitorsApiResources(RequestType $requestType): array
    {
        $result = [];
        foreach ($this->apiResources as $entityClass) {
            $entityType = ValueNormalizerUtil::convertToEntityType($this->valueNormalizer, $entityClass, $requestType);
            if ($entityType
                && $this->resourcesProvider->isResourceEnabled(
                    $entityClass,
                    ApiAction::OPTIONS,
                    Version::LATEST,
                    $requestType
                )
            ) {
                $result[] = $entityType;
            }
        }

        sort($result);

        return $result;
    }

    private function getTemplate(): string
    {
        return <<<MARKDOWN
The following API resources can be used by non-authenticated visitors:

%s
MARKDOWN;
    }
}
