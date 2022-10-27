<?php

namespace Oro\Bundle\CustomerBundle;

use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ProcessorBagCompilerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\ConfigureFrontendHelperPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\DataAuditEntityMappingPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\FrontendApiPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\LoginManagerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\RolesChangeListenerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCustomerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OwnerTreeListenerPass());
        $container->addCompilerPass(new RolesChangeListenerPass());
        $container->addCompilerPass(new DataAuditEntityMappingPass());
        $container->addCompilerPass(new LoginManagerPass());
        $container->addCompilerPass(new ConfigureFrontendHelperPass());

        if ($container instanceof ExtendedContainerBuilder) {
            $container->addCompilerPass(new FrontendApiPass());
            $container->moveCompilerPassBefore(FrontendApiPass::class, ProcessorBagCompilerPass::class);
        }

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new AnonymousCustomerUserFactory());
    }
}
