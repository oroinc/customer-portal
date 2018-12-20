<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\Api\FrontendApiDependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures frontend API processors.
 */
class FrontendApiPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_organization.api.config.add_owner_validator'
        );
    }
}
