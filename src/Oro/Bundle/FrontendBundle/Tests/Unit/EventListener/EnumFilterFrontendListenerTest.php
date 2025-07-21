<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Filter\DictionaryFilter;
use Oro\Bundle\FilterBundle\Filter\EnumFilter;
use Oro\Bundle\FilterBundle\Filter\MultiEnumFilter;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\FrontendBundle\EventListener\EnumFilterFrontendListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnumFilterFrontendListenerTest extends TestCase
{
    private DatagridConfiguration $datagridConfig;
    private BuildBefore&MockObject $event;
    private FrontendHelper&MockObject $frontendHelper;
    private EnumFilterFrontendListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->datagridConfig = DatagridConfiguration::create([]);
        $this->event = $this->createMock(BuildBefore::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new EnumFilterFrontendListener($this->frontendHelper);
    }

    public function testOnBuildBeforeWhenNotFrontendRequest(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->event->expects($this->never())
            ->method('getConfig')
            ->willReturn($this->datagridConfig);

        $this->listener->onBuildBefore($this->event);

        $this->assertEmpty($this->datagridConfig->toArray());
    }

    public function testOnBuildBeforeWhenFrontendRequest(): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->event->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->datagridConfig);

        $filterColumns = [
            'sku' => [
                'type' => 'string'
            ],
            'productName' => [
                'type' => 'string'
            ],
            'inventoryStatus' => [
                'type' => EnumFilter::FILTER_TYPE_NAME
            ],
            'multiEnumField' => [
                'type' => MultiEnumFilter::FILTER_TYPE_NAME
            ],
            'dictionaryField' => [
                'type' => DictionaryFilter::FILTER_TYPE_NAME
            ]
        ];

        $this->datagridConfig->offsetSetByPath(Configuration::COLUMNS_PATH, $filterColumns);

        $expectedConfiguration = [
            'filters' => [
                'columns' => [
                    'sku' => [
                        'type' => 'string'
                    ],
                    'productName' => [
                        'type' => 'string'
                    ],
                    'inventoryStatus' => [
                        'type' => EnumFilter::FILTER_TYPE_NAME,
                        'dictionaryValueRoute' => 'oro_frontend_dictionary_value',
                        'dictionarySearchRoute' => 'oro_frontend_dictionary_search'
                    ],
                    'multiEnumField' => [
                        'type' => MultiEnumFilter::FILTER_TYPE_NAME,
                        'dictionaryValueRoute' => 'oro_frontend_dictionary_value',
                        'dictionarySearchRoute' => 'oro_frontend_dictionary_search'
                    ],
                    'dictionaryField' => [
                        'type' => DictionaryFilter::FILTER_TYPE_NAME,
                        'dictionaryValueRoute' => 'oro_frontend_dictionary_value',
                        'dictionarySearchRoute' => 'oro_frontend_dictionary_search'
                    ]
                ]
            ]
        ];

        $this->listener->onBuildBefore($this->event);
        $this->assertEquals($expectedConfiguration, $this->datagridConfig->toArray());
    }
}
