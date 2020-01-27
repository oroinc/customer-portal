<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\DataAuditEntityMappingPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DataAuditEntityMappingPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addAuditEntryClasses', $this->isType('array'));

        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(DataAuditEntityMappingPass::MAPPER_SERVICE)
            ->willReturn($definition);

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with(DataAuditEntityMappingPass::MAPPER_SERVICE)
            ->willReturn($definition);

        $compilerPass = new DataAuditEntityMappingPass();
        $compilerPass->process($containerBuilder);
    }

    public function testProcessWithoutDefinition()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->never())->method('getDefinition');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with(DataAuditEntityMappingPass::MAPPER_SERVICE)
            ->willReturn(false);

        $compilerPass = new DataAuditEntityMappingPass();
        $compilerPass->process($containerBuilder);
    }
}
