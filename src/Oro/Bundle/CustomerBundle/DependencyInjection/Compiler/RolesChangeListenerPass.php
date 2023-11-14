<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the CustomerUser class to the list of supported classes of oro_security.roles_change_listener service.
 * @deprecated The 'oro_security.roles_change_listener' service was removed. The listener now processed in scope of
 * CustomerUserDoctrineAclCacheListener.
 */
class RolesChangeListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        return;
        $container->getDefinition('oro_security.roles_change_listener')
            ->addMethodCall('addSupportedClass', [CustomerUser::class]);
    }
}
