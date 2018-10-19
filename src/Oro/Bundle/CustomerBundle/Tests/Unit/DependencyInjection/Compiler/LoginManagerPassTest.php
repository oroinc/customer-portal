<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\LoginManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LoginManagerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcessPersistentRememberMe()
    {
        $loginManager = $this->createMock(Definition::class);

        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->once())
            ->method('getParameter')
            ->with('oro_customer.firewall_name')
            ->willReturn('test_firewall_name');

        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with('oro_customer.security.login_manager')
            ->willReturn($loginManager);

        $containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->withConsecutive(
                ['oro_customer.security.login_manager'],
                ['security.authentication.rememberme.services.persistent.test_firewall_name']
            )
            ->willReturnOnConsecutiveCalls(true, true);

        $containerBuilder->expects($this->once())
            ->method('has')
            ->with('security.user_checker.test_firewall_name')
            ->willReturn(true);

        $loginManager->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive(
                [6, new Reference('security.authentication.rememberme.services.persistent.test_firewall_name')],
                [1, new Reference('security.user_checker.test_firewall_name')]
            );

        $compilerPass = new LoginManagerPass();
        $compilerPass->process($containerBuilder);
    }

    public function testProcessSimplehashRememberMe()
    {
        $loginManager = $this->createMock(Definition::class);

        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->once())
            ->method('getParameter')
            ->with('oro_customer.firewall_name')
            ->willReturn('test_firewall_name');

        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with('oro_customer.security.login_manager')
            ->willReturn($loginManager);

        $containerBuilder->expects($this->exactly(3))
            ->method('hasDefinition')
            ->withConsecutive(
                ['oro_customer.security.login_manager'],
                ['security.authentication.rememberme.services.persistent.test_firewall_name']
            )
            ->willReturnOnConsecutiveCalls(true, false, true);

        $containerBuilder->expects($this->once())
            ->method('has')
            ->with('security.user_checker.test_firewall_name')
            ->willReturn(true);

        $loginManager->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive(
                [6, new Reference('security.authentication.rememberme.services.simplehash.test_firewall_name')],
                [1, new Reference('security.user_checker.test_firewall_name')]
            );

        $compilerPass = new LoginManagerPass();
        $compilerPass->process($containerBuilder);
    }

    public function testProcessWithoutDefinition()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $containerBuilder */
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->never())->method('getDefinition');
        $containerBuilder->expects($this->never())->method('getParameter');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with('oro_customer.security.login_manager')
            ->willReturn(false);

        $compilerPass = new LoginManagerPass();
        $compilerPass->process($containerBuilder);
    }
}
