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
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.create.rest.set_location_header'
        );
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.options.rest.set_cache_control'
        );
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.rest.cors.set_allow_origin'
        );
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.rest.cors.set_allow_and_expose_headers'
        );
        FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi(
            $container,
            'oro_api.options.rest.cors.set_max_age'
        );
    }
}
