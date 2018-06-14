<?php

namespace Oro\Bundle\StyleBookBundle;

use Oro\Bundle\StyleBookBundle\DependencyInjection\OroStyleBookExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle provides sample Oro UI library with examples of UI elements and their source codes
 */
class OroStyleBookBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroStyleBookExtension();
        }

        return $this->extension;
    }
}
