<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsFormOptionsEvent;
use Oro\Bundle\FrontendBundle\EventListener\ThemeSystemConfigFormOptionsListener;

class ThemeSystemConfigFormOptionsListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ThemeSystemConfigFormOptionsListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new ThemeSystemConfigFormOptionsListener();
    }

    public function testOnFormOptionsWhenNoPageTemplatesField(): void
    {
        $allFormOptions = ['key1' => ['option1' => 'value1']];
        $event = new ConfigSettingsFormOptionsEvent($this->createMock(ConfigManager::class), $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($allFormOptions, $event->getAllFormOptions());
    }

    public function testOnFormOptionsWhenHasPageTemplatesField(): void
    {
        $allFormOptions = [
            'key1'                        => ['option1' => 'value1'],
            'oro_frontend.page_templates' => ['option2' => 'value2']
        ];
        $expected = $allFormOptions;
        $expected['oro_frontend.page_templates']['block_prefix'] = 'oro_frontend_page_template_form_field';
        $event = new ConfigSettingsFormOptionsEvent($this->createMock(ConfigManager::class), $allFormOptions);
        $this->listener->onFormOptions($event);
        self::assertEquals($expected, $event->getAllFormOptions());
    }
}
