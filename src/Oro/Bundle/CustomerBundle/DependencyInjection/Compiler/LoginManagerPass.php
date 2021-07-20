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
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_customer.security.login_manager')) {
            return;
        }

        $firewallName = $container->getParameter('oro_customer.firewall_name');
        $loginManager = $container->getDefinition('oro_customer.security.login_manager');

        // inject remember me services
        if ($container->hasDefinition('security.authentication.rememberme.services.persistent.'.$firewallName)) {
            $loginManager->replaceArgument(
                6,
                new Reference('security.authentication.rememberme.services.persistent.'.$firewallName)
            );
        } elseif ($container->hasDefinition('security.authentication.rememberme.services.simplehash.'.$firewallName)) {
            $loginManager->replaceArgument(
                6,
                new Reference('security.authentication.rememberme.services.simplehash.'.$firewallName)
            );
        }

        // inject user checker
        if ($container->has('security.user_checker.'.$firewallName)) {
            $loginManager->replaceArgument(1, new Reference('security.user_checker.'.$firewallName));
        }
    }
}
