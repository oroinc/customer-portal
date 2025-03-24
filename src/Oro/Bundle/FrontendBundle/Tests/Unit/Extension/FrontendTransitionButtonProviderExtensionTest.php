<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Extension;

use Oro\Bundle\FrontendBundle\Extension\FrontendTransitionButtonProviderExtension;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Extension\TransitionButtonProviderExtensionTestCase;

class FrontendTransitionButtonProviderExtensionTest extends TransitionButtonProviderExtensionTestCase
{
    #[\Override]
    protected function getApplication(): string
    {
        return FrontendCurrentApplicationProvider::COMMERCE_APPLICATION;
    }

    #[\Override]
    protected function createExtension(): FrontendTransitionButtonProviderExtension
    {
        return new FrontendTransitionButtonProviderExtension(
            $this->workflowRegistry,
            $this->routeProvider,
            $this->originalUrlProvider
        );
    }
}
