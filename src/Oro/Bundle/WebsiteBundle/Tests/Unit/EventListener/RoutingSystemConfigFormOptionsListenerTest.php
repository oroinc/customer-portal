<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigSettingsFormOptionsEvent;
use Oro\Bundle\WebsiteBundle\EventListener\RoutingSystemConfigFormOptionsListener;

class RoutingSystemConfigFormOptionsListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RoutingSystemConfigFormOptionsListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new RoutingSystemConfigFormOptionsListener();
    }

    public function testOnFormOptionsWhenNoUrlAndSecureUrlFields(): void
    {
        $allFormOptions = ['key1' => ['option1' => 'value1']];
        $event = new ConfigSettingsFormOptionsEvent('app', $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasUrlField(): void
    {
        $allFormOptions = [
            'key1'            => ['option1' => 'value1'],
            'oro_website.url' => ['option2' => 'value2']
        ];
        $expected = $allFormOptions;
        $expected['oro_website.url']['resettable'] = false;
        $event = new ConfigSettingsFormOptionsEvent('app', $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($expected, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasUrlFieldButNotApplicationLevelConfig(): void
    {
        $allFormOptions = [
            'key1'            => ['option1' => 'value1'],
            'oro_website.url' => ['option2' => 'value2']
        ];
        $event = new ConfigSettingsFormOptionsEvent('organization', $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasSecureUrlField(): void
    {
        $allFormOptions = [
            'key1'                   => ['option1' => 'value1'],
            'oro_website.secure_url' => ['option2' => 'value2']
        ];
        $expected = $allFormOptions;
        $expected['oro_website.secure_url']['resettable'] = false;
        $event = new ConfigSettingsFormOptionsEvent('app', $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($expected, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasSecureUrlFieldButNotApplicationLevelConfig(): void
    {
        $allFormOptions = [
            'key1'                   => ['option1' => 'value1'],
            'oro_website.secure_url' => ['option2' => 'value2']
        ];
        $event = new ConfigSettingsFormOptionsEvent('organization', $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }
}
