<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendSessionPass;
use Oro\Bundle\FrontendBundle\Request\DynamicSessionHttpKernelDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FrontendSessionPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendSessionPass */
    private $compiler;

    /** @var ContainerBuilder */
    private $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compiler = new FrontendSessionPass();
    }

    public function testFrontendSessionWasNotConfigured()
    {
        $this->container->setParameter('oro_frontend.session.storage.options', []);

        $this->compiler->process($this->container);

        self::assertFalse($this->container->hasDefinition('oro_frontend.http_kernel.dynamic_session'));
    }

    public function testFrontendSessionWasConfigured()
    {
        $this->container->setParameter(
            'oro_frontend.session.storage.options',
            ['name' => 'TEST']
        );

        $this->compiler->process($this->container);

        self::assertTrue($this->container->hasDefinition('oro_frontend.http_kernel.dynamic_session'));
        $expectedKernelDecorator = new Definition(
            DynamicSessionHttpKernelDecorator::class,
            [
                new Reference('oro_frontend.http_kernel.dynamic_session.inner'),
                new Reference('service_container'),
                new Reference('oro_frontend.request.frontend_helper'),
                '%oro_frontend.session.storage.options%'
            ]
        );
        $expectedKernelDecorator
            ->setDecoratedService('http_kernel', null, 250)
            ->setPublic(false);
        self::assertEquals(
            $expectedKernelDecorator,
            $this->container->getDefinition('oro_frontend.http_kernel.dynamic_session')
        );
    }
}
