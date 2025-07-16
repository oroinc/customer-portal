<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\LoginManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoginManagerPassTest extends TestCase
{
    private LoginManagerPass $compiler;

    #[\Override]
    protected function setUp(): void
    {
        $this->compiler = new LoginManagerPass();
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $loginManagerDef = $container->register('oro_customer.security.login_manager')
            ->setArguments([null, null, null, null, null, null, null]);
        $container->setParameter('oro_customer.firewall_name', 'test_firewall_name');

        $container->register('security.user_checker.test_firewall_name');

        $this->compiler->process($container);

        self::assertEquals(
            new Reference('security.user_checker.test_firewall_name'),
            $loginManagerDef->getArgument(1)
        );
    }
}
