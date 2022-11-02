<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers Customer and CustomerUser as supported by the owner tree entities.
 */
class OwnerTreeListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('oro_security.ownership_tree_listener')
            ->addMethodCall('addSupportedClass', [Customer::class, ['parent', 'organization']])
            ->addMethodCall('addSupportedClass', [CustomerUser::class, ['customer', 'organization']]);
    }
}
