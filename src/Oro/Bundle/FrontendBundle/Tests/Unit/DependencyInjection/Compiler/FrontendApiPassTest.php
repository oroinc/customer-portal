<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ApiBundle\EventListener\UnauthorizedApiRequestListener as BaseUnauthorizedApiRequestListener;
use Oro\Bundle\ApiBundle\EventListener\UnhandledApiErrorExceptionListener as BaseUnhandledApiErrorExceptionListener;
use Oro\Bundle\ApiBundle\Request\ApiRequestHelper;
use Oro\Bundle\ApiBundle\Request\Rest\RequestActionHandler;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiPass;
use Oro\Bundle\FrontendBundle\EventListener\UnauthorizedApiRequestListener;
use Oro\Bundle\FrontendBundle\EventListener\UnhandledApiErrorExceptionListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class FrontendApiPassTest extends \PHPUnit\Framework\TestCase
{
    private const PROCESSORS = [
        'oro_api.collect_resources.load_dictionaries',
        'oro_api.collect_resources.load_custom_entities',
        'oro_api.collect_resources.add_excluded_actions_for_dictionaries',
        'oro_api.options.rest.set_cache_control',
        'oro_api.rest.cors.set_allow_origin',
        'oro_api.rest.cors.set_allow_and_expose_headers',
        'oro_api.options.rest.cors.set_max_age',
        'oro_locale.api.complete_localized_values',
        'oro_activity.api.get_config.add_activity_associations',
        'oro_activity.api.get_config.add_activity_association_descriptions',
        'oro_attachment.api.get_config.add_attachment_associations',
        'oro_attachment.api.get_config.add_attachment_association_descriptions',
        'oro_attachment.collect_subresources.exclude_change_attachment_subresources',
        'oro_comment.api.get_config.add_comment_associations',
        'oro_comment.api.get_config.add_comment_association_descriptions',
        'oro_comment.collect_subresources.exclude_change_comment_subresources',
    ];

    /** @var ContainerBuilder */
    private $container;

    /** @var FrontendApiPass */
    private $compilerPass;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container
            ->register(
                'oro_api.rest.unauthorized_api_request_listener',
                BaseUnauthorizedApiRequestListener::class
            )
            ->setArguments([
                new Reference(ContainerInterface::class),
                new Reference(ApiRequestHelper::class)
            ])
            ->addTag('kernel.event_listener', ['event' => 'kernel.response', 'priority' => -100])
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_api.rest.request_action_handler',
                'key' => RequestActionHandler::class
            ]);
        $this->container
            ->register(
                'oro_api.rest.unhandled_error_exception_listener',
                BaseUnhandledApiErrorExceptionListener::class
            )
            ->setArguments([
                new Reference(ContainerInterface::class),
                new Reference(ApiRequestHelper::class)
            ])
            ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'priority' => -10])
            ->addTag('container.service_subscriber', [
                'id'  => 'oro_api.rest.request_action_handler',
                'key' => RequestActionHandler::class
            ]);

        $this->compilerPass = new FrontendApiPass();
    }

    private function registerProcessor(string $serviceId): Definition
    {
        $definition = $this->container->setDefinition($serviceId, new Definition());
        $definition->addTag('oro.api.processor');

        return $definition;
    }

    /**
     * @param string|null $serviceIdToBeSkipped
     *
     * @return Definition[]
     */
    private function registerProcessors(string $serviceIdToBeSkipped = null): array
    {
        $definitions = [];
        foreach (self::PROCESSORS as $serviceId) {
            if ($serviceIdToBeSkipped && $serviceId === $serviceIdToBeSkipped) {
                continue;
            }
            $definitions[] = $this->registerProcessor($serviceId);
        }

        return $definitions;
    }

    /**
     * @dataProvider processorsDataProvider
     */
    public function testProcessWhenSomeProcessorDoesNotExist(string $processorServiceId): void
    {
        $this->registerProcessors($processorServiceId);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('non-existent service "%s"', $processorServiceId));

        $this->compilerPass->process($this->container);
    }

    public function processorsDataProvider(): array
    {
        return array_map(
            function ($serviceId) {
                return [$serviceId];
            },
            self::PROCESSORS
        );
    }

    public function testProcessWhenAllProcessorsExist(): void
    {
        $definitions = $this->registerProcessors();

        $this->compilerPass->process($this->container);

        foreach ($definitions as $definition) {
            self::assertEquals(
                [['requestType' => '!frontend']],
                $definition->getTag('oro.api.processor')
            );
        }
    }

    public function testConfigureUnauthorizedApiRequestListener(): void
    {
        $this->registerProcessors();
        $this->compilerPass->process($this->container);

        $listenerDefinition = $this->container->getDefinition('oro_api.rest.unauthorized_api_request_listener');
        self::assertEquals(UnauthorizedApiRequestListener::class, $listenerDefinition->getClass());
        self::assertEquals(
            [
                new Reference(ContainerInterface::class),
                new Reference(ApiRequestHelper::class),
                new Reference(FrontendHelper::class),
                '%web_backend_prefix%'
            ],
            $listenerDefinition->getArguments()
        );
        self::assertEquals(
            [
                'kernel.event_listener'        => [
                    ['event' => 'kernel.response', 'priority' => -100]
                ],
                'container.service_subscriber' => [
                    ['id' => 'oro_api.rest.request_action_handler', 'key' => 'handler'],
                    ['id' => 'oro_frontend.api.rest.request_action_handler', 'key' => 'frontend_handler']
                ]
            ],
            $listenerDefinition->getTags()
        );
    }

    public function testConfigureUnhandledApiErrorExceptionListener(): void
    {
        $this->registerProcessors();
        $this->compilerPass->process($this->container);

        $listenerDefinition = $this->container->getDefinition('oro_api.rest.unhandled_error_exception_listener');
        self::assertEquals(UnhandledApiErrorExceptionListener::class, $listenerDefinition->getClass());
        self::assertEquals(
            [
                new Reference(ContainerInterface::class),
                new Reference(ApiRequestHelper::class),
                new Reference(FrontendHelper::class),
                '%web_backend_prefix%'
            ],
            $listenerDefinition->getArguments()
        );
        self::assertEquals(
            [
                'kernel.event_listener'        => [
                    ['event' => 'kernel.exception', 'priority' => -10]
                ],
                'container.service_subscriber' => [
                    ['id' => 'oro_api.rest.request_action_handler', 'key' => 'handler'],
                    ['id' => 'oro_frontend.api.rest.request_action_handler', 'key' => 'frontend_handler']
                ]
            ],
            $listenerDefinition->getTags()
        );
    }
}
