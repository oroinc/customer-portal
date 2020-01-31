<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers Customer and CustomerUser as supported by the owner tree.
 */
class OwnerTreeListenerPass implements CompilerPassInterface
{
    const LISTENER_SERVICE = 'oro_security.ownership_tree_subscriber';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LISTENER_SERVICE)) {
            return;
        }

        $listenerDefinition = $container->getDefinition(self::LISTENER_SERVICE);
        $listenerDefinition->addMethodCall(
            'addSupportedClass',
            [
                Customer::class,
                ['parent', 'organization']
            ]
        );
        $listenerDefinition->addMethodCall(
            'addSupportedClass',
            [
                CustomerUser::class,
                ['customer', 'organization'],
            ]
        );
    }
}
