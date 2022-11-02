<?php

namespace Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * To build the correct paths for assets, we redefine the request context, which takes into account the website path.
 */
class AssetsRouterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $routerDefinition = $container->getDefinition('oro_website.asset.router');
        $routerDefinition->replaceArgument(3, new Reference('oro_website.asset.request_context'));

        $generatorDefinition = $container->getDefinition('oro_attachment.url_generator');
        $generatorDefinition->replaceArgument(0, new Reference('oro_website.asset.router'));

        $cacheManagerDefinition = $container->getDefinition('liip_imagine.cache.manager');
        $cacheManagerDefinition->replaceArgument(1, new Reference('oro_website.asset.router'));

        $cacheResolverDefinition = $container->getDefinition('liip_imagine.cache.resolver.default');
        $cacheResolverDefinition->replaceArgument(1, new Reference('oro_website.asset.request_context'));

        $consumptionExtension = $container->getDefinition('oro_ui.consumption_extension.request_context');
        $consumptionExtension->replaceArgument(0, new Reference('oro_website.asset.request_context'));
    }
}
