<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extension;

use Oro\Bundle\FrontendBundle\Extension\FrontendStartTransitionButtonProviderExtension;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Extension\StartTransitionButtonProviderExtensionTestCase;

class FrontendStartTransitionButtonProviderExtensionTest extends StartTransitionButtonProviderExtensionTestCase
{
    #[\Override]
    protected function getApplication()
    {
        return FrontendCurrentApplicationProvider::COMMERCE_APPLICATION;
    }

    #[\Override]
    protected function createExtension()
    {
        return new FrontendStartTransitionButtonProviderExtension(
            $this->workflowRegistry,
            $this->routeProvider,
            $this->originalUrlProvider
        );
    }
}
