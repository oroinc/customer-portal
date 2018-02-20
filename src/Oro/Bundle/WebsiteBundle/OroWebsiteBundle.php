<?php

namespace Oro\Bundle\WebsiteBundle;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Oro\Bundle\WebsiteBundle\DependencyInjection\OroWebsiteExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroWebsiteBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigSandboxConfigurationPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroWebsiteExtension();
        }

        return $this->extension;
    }
}
