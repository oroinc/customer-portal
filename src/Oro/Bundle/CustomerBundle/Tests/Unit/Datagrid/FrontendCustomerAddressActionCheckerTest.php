<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Datagrid\FrontendCustomerAddressActionChecker;

class FrontendCustomerAddressActionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FrontendCustomerAddressActionChecker */
    private $actionChecker;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->actionChecker = new FrontendCustomerAddressActionChecker($this->configManager);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
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
