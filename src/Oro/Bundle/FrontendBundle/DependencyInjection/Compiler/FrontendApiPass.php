<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\Api\FrontendApiDependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\EventListener\UnhandledApiErrorExceptionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures frontend API processors and exception listener for unhandled API errors.
 */
class FrontendApiPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $processorsToBeDisabled = [
            'oro_api.collect_resources.load_dictionaries',
            'oro_api.collect_resources.load_custom_entities',
            'oro_api.collect_resources.add_excluded_actions_for_dictionaries',
            'oro_api.options.rest.set_cache_control',
            'oro_api.rest.cors.set_allow_origin',
            'oro_api.rest.cors.set_allow_and_expose_headers',
            'oro_api.options.rest.cors.set_max_age',
            'oro_locale.api.complete_localized_values'
        ];
        foreach ($processorsToBeDisabled as $serviceId) {
            FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi($container, $serviceId);
        }

        $container->getDefinition('oro_api.rest.unhandled_error_exception_listener')
            ->setClass(UnhandledApiErrorExceptionListener::class)
            ->addArgument('%web_backend_prefix%')
            ->clearTag('container.service_subscriber')
            ->addTag('container.service_subscriber', ['id' => FrontendHelper::class])
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_api.rest.request_action_handler',
                'key' => 'handler'
            ])
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_frontend.api.rest.request_action_handler',
                'key' => 'frontend_handler'
            ]);
    }
}
