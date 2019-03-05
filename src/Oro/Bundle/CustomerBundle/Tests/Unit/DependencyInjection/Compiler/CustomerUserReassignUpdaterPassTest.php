<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\CustomerUserReassignUpdaterPass;
use Oro\Component\DependencyInjection\Tests\Unit\AbstractExtensionCompilerPassTest;

class CustomerUserReassignUpdaterPassTest extends AbstractExtensionCompilerPassTest
{
    public function testProcess()
    {
        $this->assertServiceDefinitionMethodCalled('addCustomerUserReassignEntityUpdater');
        $this->assertContainerBuilderCalled();

        $this->getCompilerPass()->process($this->containerBuilder);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilerPass()
    {
        return new CustomerUserReassignUpdaterPass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getServiceId()
    {
        return CustomerUserReassignUpdaterPass::UPDATER_SERVICE_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTagName()
    {
        return CustomerUserReassignUpdaterPass::TAG;
    }
}
