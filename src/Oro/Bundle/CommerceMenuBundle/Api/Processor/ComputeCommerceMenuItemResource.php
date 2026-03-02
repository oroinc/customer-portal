<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\CommerceMenuBundle\Api\Repository\RouteInfoProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes a value of "resource" field for CommerceMenuItem entity.
 */
class ComputeCommerceMenuItemResource implements ProcessorInterface
{
    private const string RESOURCE_FIELD = 'resource';

    public function __construct(
        private readonly RouteInfoProvider $routeInfoProvider
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        if (!$context->isFieldRequested(self::RESOURCE_FIELD)) {
            return;
        }

        $menuItems = $context->getData();
        $uriFieldName = $context->getResultFieldName('uri');
        $uris = $this->getUris($menuItems, $uriFieldName);
        if (empty($uris)) {
            return;
        }

        $routesInfo = $this->routeInfoProvider->getRoutesInfo($uris, $context->getRequestType());
        foreach ($menuItems as $key => $menuItem) {
            if (isset($menuItem[$uriFieldName])) {
                $menuItems[$key][self::RESOURCE_FIELD] = $routesInfo[$menuItem[$uriFieldName]] ?? null;
            }
        }

        $context->setData($menuItems);
    }

    private function getUris(array $menuItems, string $uriFieldName): array
    {
        $uris = [];
        foreach ($menuItems as $menuItem) {
            $uri = $menuItem[$uriFieldName] ?? null;
            if ($uri) {
                $uris[] = $uri;
            }
        }

        return $uris;
    }
}
