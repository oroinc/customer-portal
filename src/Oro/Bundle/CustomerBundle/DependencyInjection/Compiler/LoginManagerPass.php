<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends firewall settings to make possible to work with guest users
 */
class LoginManagerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container)
    {
        $firewallName = $container->getParameter('oro_customer.firewall_name');
        $loginManager = $container->getDefinition('oro_customer.security.login_manager');

        // inject user checker
        if ($container->has('security.user_checker.' . $firewallName)) {
            $loginManager->replaceArgument(1, new Reference('security.user_checker.' . $firewallName));
        }
    }
}
