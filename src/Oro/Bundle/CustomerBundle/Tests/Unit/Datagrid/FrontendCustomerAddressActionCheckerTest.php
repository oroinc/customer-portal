<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Datagrid\FrontendCustomerAddressActionChecker;

class FrontendCustomerAddressActionCheckerTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendCustomerAddressActionChecker */
    private $actionChecker;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->actionChecker = new FrontendCustomerAddressActionChecker($this->configManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->configManager, $this->actionChecker);
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
