<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\AssetsRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AssetsRouterPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $containerBuilder;

    /** @var AssetsRouterPass */
    private $compilerPass;

    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);

        $this->compilerPass = new AssetsRouterPass();
    }

    public function testProcess(): void
    {
        $routerDefinition = $this->createMock(Definition::class);
        $routerDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(3, new Reference('oro_website.asset.request_context'));

        $generatorDefinition = $this->createMock(Definition::class);
        $generatorDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(0, new Reference('oro_website.asset.router'));

        $cacheManagerDefinition = $this->createMock(Definition::class);
        $cacheManagerDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(1, new Reference('oro_website.asset.router'));

        $cacheResolverDefinition = $this->createMock(Definition::class);
        $cacheResolverDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(1, new Reference('oro_website.asset.request_context'));

        $consumptionExtension = $this->createMock(Definition::class);
        $consumptionExtension->expects($this->once())
            ->method('replaceArgument')
            ->with(0, new Reference('oro_website.asset.request_context'));

        $this->containerBuilder->expects($this->any())
            ->method('getDefinition')
            ->willReturnMap(
                [
                    ['oro_website.asset.router', $routerDefinition],
                    ['oro_attachment.url_generator', $generatorDefinition],
                    ['liip_imagine.cache.manager', $cacheManagerDefinition],
                    ['liip_imagine.cache.resolver.default', $cacheResolverDefinition],
                    ['oro_ui.consumption_extension.request_context', $consumptionExtension],
                ]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
