<?php

namespace Oro\Bundle\WebsiteBundle;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\AssetsRouterPass;
use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroWebsiteBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new TwigSandboxConfigurationPass());
        $container->addCompilerPass(new AssetsRouterPass());
    }
}
