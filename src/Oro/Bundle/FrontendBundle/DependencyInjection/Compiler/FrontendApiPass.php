<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\Api\FrontendApiDependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\EventListener\UnauthorizedApiRequestListener;
use Oro\Bundle\FrontendBundle\EventListener\UnhandledApiErrorExceptionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configures frontend API processors, unauthorized request listener and exception listener for unhandled API errors.
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
            'oro_locale.api.complete_localized_values',
            'oro_search.api.add_search_text_filter',
            'oro_search.api.set_default_search_text_sorting',
            'oro_search.api.build_search_query',
            'oro_activity.api.get_config.add_activity_associations',
            'oro_activity.api.get_config.add_activity_association_descriptions',
            'oro_attachment.api.get_config.add_attachment_associations',
            'oro_attachment.api.get_config.add_attachment_association_descriptions',
            'oro_attachment.api.collect_subresources.exclude_change_attachment_subresources',
            'oro_comment.api.get_config.add_comment_associations',
            'oro_comment.api.get_config.add_comment_association_descriptions',
            'oro_comment.api.collect_subresources.exclude_change_comment_subresources',
        ];
        foreach ($processorsToBeDisabled as $serviceId) {
            FrontendApiDependencyInjectionUtil::disableProcessorForFrontendApi($container, $serviceId);
        }

        $container->getDefinition('oro_api.rest.unauthorized_api_request_listener')
            ->setClass(UnauthorizedApiRequestListener::class)
            ->addArgument(new Reference(FrontendHelper::class))
            ->addArgument('%web_backend_prefix%')
            ->clearTag('container.service_subscriber')
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_api.rest.request_action_handler',
                'key' => 'handler'
            ])
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_frontend.api.rest.request_action_handler',
                'key' => 'frontend_handler'
            ]);

        $container->getDefinition('oro_api.rest.unhandled_error_exception_listener')
            ->setClass(UnhandledApiErrorExceptionListener::class)
            ->addArgument(new Reference(FrontendHelper::class))
            ->addArgument('%web_backend_prefix%')
            ->clearTag('container.service_subscriber')
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
