<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds a route prefix excluding option which prevents related routes with such option from being
 * prepended with a backend prefix in {@see \Oro\Bundle\FrontendBundle\EventListener\RouteCollectionListener}.
 */
class AddRoutePrefixExcludingOptionCompilerPass implements CompilerPassInterface
{
    public function __construct(private string $excludingOption)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('oro_frontend.listener.route_collection')) {
            $routeCollectionListener = $container->getDefinition('oro_frontend.listener.route_collection');
            $routeCollectionListener->addMethodCall('addExcludingOption', [$this->excludingOption]);
        }
    }
}
