<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendSessionPass;
use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Oro\Bundle\SecurityBundle\DependencyInjection\Compiler\SessionPass;
use Oro\Bundle\SecurityBundle\Request\SessionHttpKernelDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FrontendSessionPassTest extends \PHPUnit\Framework\TestCase
{
    private FrontendSessionPass $compiler;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->compiler = new FrontendSessionPass();
    }

    public function testFrontendSessionWasNotConfigured(): void
    {
        $this->container->setParameter('oro_frontend.session.storage.options', []);

        $this->compiler->process($this->container);

        self::assertFalse($this->container->hasDefinition('oro_frontend.http_kernel.dynamic_session'));
    }

    public function testFrontendSessionWasConfigured(): void
    {
        $this->container
            ->register(SessionPass::HTTP_KERNEL_DECORATOR_SERVICE, SessionHttpKernelDecorator::class)
            ->setArguments([
                new Reference('.inner'),
                new Reference('service_container')
            ])
            ->setDecoratedService('http_kernel', null, 250)
            ->setPublic(false);

        $this->container->setParameter(
            'oro_frontend.session.storage.options',
            ['name' => 'TEST']
        );

        $this->compiler->process($this->container);

        $definition = $this->container->getDefinition(SessionPass::HTTP_KERNEL_DECORATOR_SERVICE);
        self::assertEquals(DynamicSessionHttpKernelDecorator::class, $definition->getClass());
        self::assertEquals(new Reference('oro_frontend.request.frontend_helper'), $definition->getArgument(2));
        self::assertEquals('%oro_frontend.session.storage.options%', $definition->getArgument(3));
    }
}
