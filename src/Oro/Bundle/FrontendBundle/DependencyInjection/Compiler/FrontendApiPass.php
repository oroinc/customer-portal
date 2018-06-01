<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

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
            'oro_api.collect_resources.load_dictionaries'
        );
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.collect_resources.load_custom_entities'
        );
    }
}
