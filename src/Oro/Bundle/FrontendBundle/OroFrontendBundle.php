<?php

namespace Oro\Bundle\FrontendBundle;

use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroFrontendBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroFrontendExtension();
        }

        return $this->extension;
    }
}
