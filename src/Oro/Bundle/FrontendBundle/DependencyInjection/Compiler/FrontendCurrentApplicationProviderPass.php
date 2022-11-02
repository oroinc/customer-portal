<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProvider;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Substitutes CurrentApplicationProvider with FrontendCurrentApplicationProvider.
 */
class FrontendCurrentApplicationProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $currentApplicationProviderDef = $container->getDefinition('oro_action.provider.current_application');
        if ($currentApplicationProviderDef->getClass() === CurrentApplicationProvider::class) {
            $currentApplicationProviderDef
                ->setClass(FrontendCurrentApplicationProvider::class)
                ->addArgument(new Reference('oro_frontend.request.frontend_helper'));
        }
    }
}
