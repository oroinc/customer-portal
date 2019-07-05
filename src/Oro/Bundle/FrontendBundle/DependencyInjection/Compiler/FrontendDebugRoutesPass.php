<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds oro_frontend.debug_routes global TWIG variable.
 */
class FrontendDebugRoutesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('twig')
            ->addMethodCall(
                'addGlobal',
                ['oro_frontend', ['debug_routes' => $container->getParameter('oro_frontend.debug_routes')]]
            );
    }
}
