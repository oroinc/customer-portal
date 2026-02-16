<?php

namespace Oro\Bundle\CommerceMenuBundle\Api\Processor;

use Oro\Bundle\CommerceMenuBundle\Api\RouteInfoProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes a value of "resource" field for CommerceMenuItem entity.
 */
class ComputeMenuItemResourceProcessor implements ProcessorInterface
{
    private const string RESOURCE_FIELD = 'resource';

    public function __construct(private readonly RouteInfoProvider $routeInfoProvider)
    {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        $menuItems = $context->getData();

        $uris = [];
        $uriFieldName = $context->getResultFieldName('uri');
        foreach ($menuItems as $menuItem) {
            $uri = $menuItem[$uriFieldName] ?? null;
            if ($uri !== null && $uri !== '') {
                $uris[] = $uri;
            }
        }

        if (empty($uris)) {
            return;
        }

        $routesInfo = $this->routeInfoProvider->getRoutesInfo($uris, $context->getRequestType());

        foreach ($menuItems as $key => $menuItem) {
            if (!isset($menuItem[$uriFieldName])) {
                continue;
            }

            $menuItems[$key][self::RESOURCE_FIELD] = $routesInfo[$menuItem[$uriFieldName]] ?? null;
        }

        $context->setData($menuItems);
    }
}
