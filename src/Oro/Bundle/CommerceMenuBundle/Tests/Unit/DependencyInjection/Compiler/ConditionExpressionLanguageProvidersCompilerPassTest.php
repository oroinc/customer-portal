<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\ConditionExpressionLanguageProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConditionExpressionLanguageProvidersCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(ConditionExpressionLanguageProvidersCompilerPass::TAG_NAME)
            ->willReturn([1 => 'provider_1', 2 => 'provider_2']);

        /** @var Definition|\PHPUnit\Framework\MockObject\MockObject $definition */
        $definition = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();
        $definition->expects($this->exactly(2))
            ->method('addMethodCall')
            ->willReturnMap([
                ['registerProvider', [new Reference(1)], $definition],
                ['registerProvider', [new Reference(2)], $definition],
            ]);

        $container->expects($this->once())
            ->method('getDefinition')
            ->with(ConditionExpressionLanguageProvidersCompilerPass::EXPRESSION_LANGUAGE_SERVICE_ID)
            ->willReturn($definition);

        $compilerPass = new ConditionExpressionLanguageProvidersCompilerPass();
        $compilerPass->process($container);
    }
}
