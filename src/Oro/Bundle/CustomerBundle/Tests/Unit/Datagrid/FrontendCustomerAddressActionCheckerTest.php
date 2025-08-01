<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Datagrid\FrontendCustomerAddressActionChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendCustomerAddressActionCheckerTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private FrontendCustomerAddressActionChecker $actionChecker;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->actionChecker = new FrontendCustomerAddressActionChecker($this->configManager);
    }

    public function testCheckActionsEnabled(): void
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(true);

        $this->assertEquals([], $this->actionChecker->checkActions());
    }

    public function testCheckActionsDisabled(): void
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(false);

        $this->assertEquals(['show_map' => false], $this->actionChecker->checkActions());
    }
}
