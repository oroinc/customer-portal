<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Configures HTTP session to be able to use separate sessions for storefront and management console.
 */
class FrontendSessionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $options = $container->getParameter(OroFrontendExtension::FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME);
        if (empty($options)) {
            return;
        }

        $this->configureHttpKernel($container);
    }

    /**
     * Decorates "http_kernel" service.
     *
     * @param ContainerBuilder $container
     */
    private function configureHttpKernel(ContainerBuilder $container): void
    {
        $httpKernelDecoratorServiceId = 'oro_frontend.http_kernel.dynamic_session';
        $container
            ->register($httpKernelDecoratorServiceId, DynamicSessionHttpKernelDecorator::class)
            ->setArguments([
                new Reference($httpKernelDecoratorServiceId . '.inner'),
                new Reference('service_container'),
                new Reference('oro_frontend.request.frontend_helper'),
                '%' . OroFrontendExtension::FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME . '%'
            ])
            ->setDecoratedService('http_kernel', null, 250)
            ->setPublic(false);
    }
}
