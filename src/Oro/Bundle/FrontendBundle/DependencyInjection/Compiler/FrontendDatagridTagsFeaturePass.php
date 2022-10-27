<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\Datagrid\Extension\TagsExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces the tag extension to whatever deactivates tags feature if datagrid displayed on the storefront.
 */
class FrontendDatagridTagsFeaturePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('oro_tag.grid.tags_extension')) {
            $definition = $container->getDefinition('oro_tag.grid.tags_extension');

            $definition->setClass(TagsExtension::class);
            $definition->addMethodCall('setFrontendHelper', [
                new Reference('oro_frontend.request.frontend_helper')
            ]);
        }
    }
}
