<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Component\DependencyInjection\Compiler\TaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers frontend autocomplete search handlers.
 */
class FrontendAutocompleteCompilerPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $aclResources = [];
        $handlers = [];
        $taggedServices = $container->findTaggedServiceIds('oro_customer.form.autocomplete.frontend_search_handler');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $name = $this->getAttribute($attributes, 'alias', $id);
                $handlers[$name] = new Reference($id);
                $aclResource = $this->getAttribute($attributes, 'acl_resource');
                if ($aclResource) {
                    $aclResources[$name] = $aclResource;
                }
            }
        }


        $container->getDefinition('oro_customer.form.autocomplete.search_registry')
            ->addMethodCall('setFrontendSearchHandlers', [ServiceLocatorTagPass::register($container, $handlers)]);
        $container->getDefinition('oro_customer.form.autocomplete.security')
            ->addMethodCall('setAutocompleteAclResources', [$aclResources]);
    }
}
