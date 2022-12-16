<?php

declare(strict_types=1);

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DataCollector;

use Oro\Bundle\CommerceMenuBundle\DataCollector\DataCollectorMenuItemLayoutNameProvider;
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
        return [
            ['context' => new LayoutContext(), 'expected' => ''],
            ['context' => new LayoutContext(['menu_item_name' => '']), 'expected' => ''],
            [
                'context' => new LayoutContext(['menu_item_name' => 'sample_name']),
                'expected' => 'Menu Item: sample_name',
            ],
        ];
    }
}
