<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Oro\Bundle\SecurityBundle\DependencyInjection\Compiler\SessionPass;
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
     */
    private function configureHttpKernel(ContainerBuilder $container): void
    {
        $container
            ->getDefinition(SessionPass::HTTP_KERNEL_DECORATOR_SERVICE)
            ->setClass(DynamicSessionHttpKernelDecorator::class)
            ->addArgument(new Reference('oro_frontend.request.frontend_helper'))
            ->addArgument('%' . OroFrontendExtension::FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME . '%');
    }
}
