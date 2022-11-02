<?php

namespace Oro\Bundle\FrontendBundle\Extension;

use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\WorkflowBundle\Extension\StartTransitionButtonProviderExtension;

/**
 * Prepares applicable buttons to start workflow transitions for the storefront.
 */
class FrontendStartTransitionButtonProviderExtension extends StartTransitionButtonProviderExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getApplication()
    {
        return FrontendCurrentApplicationProvider::COMMERCE_APPLICATION;
    }
}
