<?php

namespace Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for registering expression language providers for menu conditions.
 *
 * This compiler pass collects all tagged expression language providers and registers them
 * with the commerce menu expression language service, enabling custom functions and variables
 * for menu condition evaluation.
 */
class ConditionExpressionLanguageProvidersCompilerPass implements CompilerPassInterface
{
    const TAG_NAME              = 'oro_commerce_menu.condition.expression_language_provider';
    const EXPRESSION_LANGUAGE_SERVICE_ID  = 'oro_commerce_menu.expression_language';

    #[\Override]
    public function process(ContainerBuilder $container)
    {
        $providers = $container->findTaggedServiceIds(self::TAG_NAME);

        $service = $container->getDefinition(self::EXPRESSION_LANGUAGE_SERVICE_ID);

        foreach ($providers as $providerId => $tags) {
            $service->addMethodCall('registerProvider', [new Reference($providerId)]);
        }
    }
}
