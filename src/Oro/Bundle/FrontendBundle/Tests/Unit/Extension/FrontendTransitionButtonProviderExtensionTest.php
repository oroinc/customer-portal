<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extension;

use Oro\Bundle\FrontendBundle\Extension\FrontendTransitionButtonProviderExtension;
use Oro\Bundle\FrontendBundle\Provider\ActionCurrentApplicationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Extension\TransitionButtonProviderExtensionTestCase;

class FrontendTransitionButtonProviderExtensionTest extends TransitionButtonProviderExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getApplication()
    {
        return ActionCurrentApplicationProvider::COMMERCE_APPLICATION;
    }

    /**
     * {@inheritdoc}
     */
    protected function createExtension()
    {
        return new FrontendTransitionButtonProviderExtension(
            $this->workflowRegistry,
            $this->routeProvider,
            $this->destinationPageResolver
        );
    }
}
