<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DataCollector;

use Oro\Bundle\CommerceMenuBundle\DataCollector\DataCollectorMenuItemLayoutNameProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\Entity\Stub\MenuItemStub;
use Oro\Component\Layout\LayoutContext;

class DataCollectorMenuItemLayoutNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getNameByContextDataProvider
     */
    public function testGetNameByContext(LayoutContext $context, string $expected): void
    {
        $provider = new DataCollectorMenuItemLayoutNameProvider();

        self::assertEquals($expected, $provider->getNameByContext($context));
    }

    public function getNameByContextDataProvider(): array
    {
        $staClassLayoutContext = new LayoutContext(['menu_item_name' => 'sample_name']);
        $staClassLayoutContext->data()->set('menu_item', new \stdClass());

        $menuItem = (new MenuItemStub())->setLabel('Sample Name');
        $menuItemLayoutContext = new LayoutContext(['menu_item_name' => 'sample_name']);
        $menuItemLayoutContext->data()->set('menu_item', $menuItem);

        return [
            ['context' => new LayoutContext(), 'expected' => ''],
            ['context' => new LayoutContext(['menu_item_name' => '']), 'expected' => ''],
            ['context' => $staClassLayoutContext, 'expected' => ''],
            [
                'context' => $menuItemLayoutContext,
                'expected' => 'Menu Item: ' . $menuItem->getLabel(),
            ],
        ];
    }
}
