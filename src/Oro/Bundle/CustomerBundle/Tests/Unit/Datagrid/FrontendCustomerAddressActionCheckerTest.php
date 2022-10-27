<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Datagrid\FrontendCustomerAddressActionChecker;

class FrontendCustomerAddressActionCheckerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var FrontendCustomerAddressActionChecker */
    private $actionChecker;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->actionChecker = new FrontendCustomerAddressActionChecker($this->configManager);
    }

    public function testCheckActionsEnabled()
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(true);

        $this->assertEquals([], $this->actionChecker->checkActions());
    }

    public function testCheckActionsDisabled()
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.maps_enabled')
            ->willReturn(false);

        $this->assertEquals(['show_map' => false], $this->actionChecker->checkActions());
    }
}
