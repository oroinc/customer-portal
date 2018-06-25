<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\AddFrontendClassMigrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddFrontendClassMigrationPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(AddFrontendClassMigrationPass::FRONTEND_CLASS_MIGRATION_SERVICE_ID)
            ->willReturn(true);

        /** @var Definition|\PHPUnit\Framework\MockObject\MockObject $definition */
        $definition = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();
        $definition->expects($this->exactly(2))
            ->method('addMethodCall')
            ->willReturnMap([
                ['append', ['FrontendNavigation', 'CommerceMenu'], $definition],
                ['append', ['frontendnavigation', 'commercemenu'], $definition],
            ]);

        $container->expects($this->once())
            ->method('findDefinition')
            ->with(AddFrontendClassMigrationPass::FRONTEND_CLASS_MIGRATION_SERVICE_ID)
            ->willReturn($definition);

        $compilerPass = new AddFrontendClassMigrationPass();
        $compilerPass->process($container);
    }

    public function testProcessSkip()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with(AddFrontendClassMigrationPass::FRONTEND_CLASS_MIGRATION_SERVICE_ID)
            ->willReturn(false);

        $container->expects($this->never())
            ->method('findDefinition');

        $compilerPass = new AddFrontendClassMigrationPass();
        $compilerPass->process($container);
    }
}
