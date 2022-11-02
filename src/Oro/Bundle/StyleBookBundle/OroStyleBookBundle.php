<?php

namespace Oro\Bundle\StyleBookBundle;

use Oro\Bundle\StyleBookBundle\DependencyInjection\OroStyleBookExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroStyleBookBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new OroStyleBookExtension();
        }

        return $this->extension;
    }
}
