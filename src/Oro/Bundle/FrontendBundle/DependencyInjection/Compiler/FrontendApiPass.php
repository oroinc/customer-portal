<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\Api\FrontendApiDependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        $container->getDefinition('oro_api.api_doc.formatter.html_formatter.composite')->addMethodCall(
            'addFormatter',
            ['frontend_rest_json_api', new Reference('oro_api.api_doc.formatter.html_formatter')]
        );

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
