<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OwnerTreeListenerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $listenerDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(OwnerTreeListenerPass::LISTENER_SERVICE)
            ->willReturn($listenerDefinition);

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with(OwnerTreeListenerPass::LISTENER_SERVICE)
            ->willReturn(true);

        $listenerDefinition->expects($this->at(0))
            ->method('addMethodCall')
            ->with(
                'addSupportedClass',
                [Customer::class, ['parent', 'organization']]
            );
        $listenerDefinition->expects($this->at(1))
            ->method('addMethodCall')
            ->with(
                'addSupportedClass',
                [CustomerUser::class, ['customer', 'organization']]
            );

        $compilerPass = new OwnerTreeListenerPass();
        $compilerPass->process($containerBuilder);
    }

    public function testProcessWithoutDefinition()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->never())->method('getDefinition');
        $containerBuilder->expects($this->never())->method('getParameter');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with(OwnerTreeListenerPass::LISTENER_SERVICE)
            ->willReturn(false);

        $compilerPass = new OwnerTreeListenerPass();
        $compilerPass->process($containerBuilder);
    }
}
