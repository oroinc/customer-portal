<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsFormOptionsEvent;
use Oro\Bundle\WebsiteBundle\EventListener\RoutingSystemConfigFormOptionsListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RoutingSystemConfigFormOptionsListenerTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private RoutingSystemConfigFormOptionsListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->listener = new RoutingSystemConfigFormOptionsListener();
    }

    public function testOnFormOptionsWhenNoUrlAndSecureUrlFields(): void
    {
        $this->configManager->expects(self::once())
            ->method('getScopeEntityName')
            ->willReturn(GlobalScopeManager::SCOPE_NAME);

        $allFormOptions = ['key1' => ['option1' => 'value1']];
        $event = new ConfigSettingsFormOptionsEvent($this->configManager, $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasUrlField(): void
    {
        $this->configManager->expects(self::once())
            ->method('getScopeEntityName')
            ->willReturn(GlobalScopeManager::SCOPE_NAME);

        $allFormOptions = [
            'key1'            => ['option1' => 'value1'],
            'oro_website.url' => ['option2' => 'value2']
        ];
        $expected = $allFormOptions;
        $expected['oro_website.url']['resettable'] = false;
        $event = new ConfigSettingsFormOptionsEvent($this->configManager, $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($expected, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasUrlFieldButNotApplicationLevelConfig(): void
    {
        $this->configManager->expects(self::once())
            ->method('getScopeEntityName')
            ->willReturn('organization');

        $allFormOptions = [
            'key1'            => ['option1' => 'value1'],
            'oro_website.url' => ['option2' => 'value2']
        ];
        $event = new ConfigSettingsFormOptionsEvent($this->configManager, $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasSecureUrlField(): void
    {
        $this->configManager->expects(self::once())
            ->method('getScopeEntityName')
            ->willReturn(GlobalScopeManager::SCOPE_NAME);

        $allFormOptions = [
            'key1'                   => ['option1' => 'value1'],
            'oro_website.secure_url' => ['option2' => 'value2']
        ];
        $expected = $allFormOptions;
        $expected['oro_website.secure_url']['resettable'] = false;
        $event = new ConfigSettingsFormOptionsEvent($this->configManager, $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($expected, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasSecureUrlFieldButNotApplicationLevelConfig(): void
    {
        $this->configManager->expects(self::once())
            ->method('getScopeEntityName')
            ->willReturn('organization');

        $allFormOptions = [
            'key1'                   => ['option1' => 'value1'],
            'oro_website.secure_url' => ['option2' => 'value2']
        ];
        $event = new ConfigSettingsFormOptionsEvent($this->configManager, $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }
}
