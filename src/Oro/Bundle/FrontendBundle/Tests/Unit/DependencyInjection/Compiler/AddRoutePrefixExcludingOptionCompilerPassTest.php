<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\AddRoutePrefixExcludingOptionCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AddRoutePrefixExcludingOptionCompilerPassTest extends TestCase
{
    public function testProcessWithRouteCollectionListener(): void
    {
        $excludingOption = 'exclude_option';
        $compilerPass = new AddRoutePrefixExcludingOptionCompilerPass($excludingOption);

        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $definition = $this->createMock(Definition::class);

        $containerBuilder->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_frontend.listener.route_collection')
            ->willReturn(true);

        $containerBuilder->expects(self::once())
            ->method('getDefinition')
            ->with('oro_frontend.listener.route_collection')
            ->willReturn($definition);

        $definition->expects(self::once())
            ->method('addMethodCall')
            ->with('addExcludingOption', [$excludingOption]);

        $compilerPass->process($containerBuilder);
    }

    public function testProcessWithoutRouteCollectionListener(): void
    {
        $excludingOption = 'exclude_option';
        $compilerPass = new AddRoutePrefixExcludingOptionCompilerPass($excludingOption);

        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_frontend.listener.route_collection')
            ->willReturn(false);

        $containerBuilder->expects(self::never())
            ->method('getDefinition');

        $compilerPass->process($containerBuilder);
    }
}
