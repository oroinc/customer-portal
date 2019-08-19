<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\Security\TokenAwareFrontendHelper;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Substitutes FrontendHelper with TokenAwareFrontendHelper.
 */
class ConfigureFrontendHelperPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $frontendHelperDef = $container->getDefinition('oro_frontend.request.frontend_helper');
        if ($frontendHelperDef->getClass() === FrontendHelper::class) {
            $frontendHelperDef
                ->setClass(TokenAwareFrontendHelper::class)
                ->addArgument(new Reference('security.token_storage'));
        }
    }
}
