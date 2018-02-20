<?php

namespace Oro\Bundle\CustomerBundle;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\DataAuditEntityMappingPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\WindowsStateManagerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCustomerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OwnerTreeListenerPass());
        $container->addCompilerPass(new DataAuditEntityMappingPass());
        $container->addCompilerPass(new WindowsStateManagerPass());

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new AnonymousCustomerUserFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroCustomerExtension();
        }

        return $this->extension;
    }
}
