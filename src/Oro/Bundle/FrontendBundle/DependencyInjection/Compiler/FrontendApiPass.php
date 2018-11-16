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
        $processorsToBeDisabled = [
            'oro_api.collect_resources.load_dictionaries',
            'oro_api.collect_resources.load_custom_entities',
            'oro_api.options.rest.set_cache_control',
            'oro_api.rest.cors.set_allow_origin',
            'oro_api.rest.cors.set_allow_and_expose_headers',
            'oro_api.options.rest.cors.set_max_age',
        ];

        foreach ($processorsToBeDisabled as $serviceId) {
            FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi($container, $serviceId);
        }
    }
}
