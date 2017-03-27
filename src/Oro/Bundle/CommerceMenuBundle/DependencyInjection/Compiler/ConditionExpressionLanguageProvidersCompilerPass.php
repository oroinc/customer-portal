<?php

namespace Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConditionExpressionLanguageProvidersCompilerPass implements CompilerPassInterface
{
    const TAG_NAME              = 'oro_commerce_menu.condition.expression_language_provider';
    const EXPRESSION_LANGUAGE_SERVICE_ID  = 'oro_commerce_menu.expression_language';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $providers = $container->findTaggedServiceIds(self::TAG_NAME);

        $service = $container->getDefinition(self::EXPRESSION_LANGUAGE_SERVICE_ID);

        foreach ($providers as $providerId => $tags) {
            $service->addMethodCall('registerProvider', [new Reference($providerId)]);
        }
    }
}
