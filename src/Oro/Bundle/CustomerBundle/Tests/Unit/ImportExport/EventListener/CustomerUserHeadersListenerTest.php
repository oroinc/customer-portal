<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerUserHeadersListener;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;

class CustomerUserHeadersListenerTest extends \PHPUnit\Framework\TestCase
{
    private const DELIMITER = ':';

    /** @var FieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldHelper;

    /** @var CustomerUserHeadersListener */
    private $listener;

    protected function setUp(): void
    {
        $this->fieldHelper = $this->createMock(FieldHelper::class);
        $this->listener = new CustomerUserHeadersListener($this->fieldHelper);
    }

    public function testAfterLoadEntityRulesAndBackendHeadersWhenNoCustomer()
    {
        $this->fieldHelper->expects($this->never())
            ->method('getConfigValue');

        $event = new LoadEntityRulesAndBackendHeadersEvent(
            \stdClass::class,
            [],
            [],
            self::DELIMITER,
            'type',
            true
        );
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEmpty($event->getHeaders());
        $this->assertEmpty($event->getRules());
    }

    public function testAfterLoadEntityRulesAndBackendHeadersWhenNotFull()
    {
        $this->fieldHelper->expects($this->never())
            ->method('getConfigValue');

        $event = new LoadEntityRulesAndBackendHeadersEvent(
            CustomerUser::class,
            [],
            [],
            self::DELIMITER,
            'type',
            false
        );
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEmpty($event->getHeaders());
        $this->assertEmpty($event->getRules());
    }

    public function testAfterLoadEntityRulesAndBackendHeadersWhenExist()
    {
        $this->fieldHelper->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturnMap([
                [CustomerUser::class, 'owner', 'excluded', null, false],
                [CustomerUser::class, 'customer', 'excluded', null, false]
            ]);

        $headers = [
            [
                'value' => 'owner:id',
            ],
            [
                'value' => 'customer:name',
            ]
        ];
        $event = new LoadEntityRulesAndBackendHeadersEvent(
            CustomerUser::class,
            $headers,
            [],
            self::DELIMITER,
            'type',
            true
        );
        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEquals($event->getHeaders(), $headers);
        $this->assertEmpty($event->getRules());
    }

    /**
     * @dataProvider fieldsConfigDataProvider
     */
    public function testAfterLoadEntityRulesAndBackendHeadersWhenHeadersNotExist(
        array $fieldsConfig,
        array $expectedHeaders,
        array $expectedRules
    ) {
        $this->fieldHelper->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturnMap($fieldsConfig);

        $event = new LoadEntityRulesAndBackendHeadersEvent(
            CustomerUser::class,
            [],
            [],
            self::DELIMITER,
            'type',
            true
        );

        $this->listener->afterLoadEntityRulesAndBackendHeaders($event);
        $this->assertEquals($event->getHeaders(), $expectedHeaders);
        $this->assertEquals($event->getRules(), $expectedRules);
    }

    public function fieldsConfigDataProvider(): array
    {
        return [
            'owner and customer fields are excluded, no rules and headers are added' => [
                'fieldsConfig' => [
                    [CustomerUser::class, 'owner', 'excluded', null, true],
                    [CustomerUser::class, 'customer', 'excluded', null, true]
                ],
                'expectedHeaders' => [],
                'expectedRules' => [],
            ],
            'owner and customer fields are not excluded, rules and headers are added for both fields' => [
                'fieldsConfig' => [
                    [CustomerUser::class, 'owner', 'excluded', null, false],
                    [CustomerUser::class, 'customer', 'excluded', null, false]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'owner:id',
                        'order' => 80,
                    ],
                    [
                        'value' => 'customer:name',
                        'order' => 40,
                    ]
                ],
                'expectedRules' => [
                    'Owner Id' => [
                        'value' => 'owner:id',
                        'order' => 80,
                    ],
                    'Customer Name' => [
                        'value' => 'customer:name',
                        'order' => 40,
                    ],
                ],
            ],
            'owner field is excluded and customer field is not, rule and header are added for customer field' => [
                'fieldsConfig' => [
                    [CustomerUser::class, 'owner', 'excluded', null, true],
                    [CustomerUser::class, 'customer', 'excluded', null, false]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'customer:name',
                        'order' => 40,
                    ]
                ],
                'expectedRules' => [
                    'Customer Name' => [
                        'value' => 'customer:name',
                        'order' => 40,
                    ],
                ],
            ],
            'owner field is not excluded and customer field is, rule and header are added for owner field' => [
                'fieldsConfig' => [
                    [CustomerUser::class, 'owner', 'excluded', null, false],
                    [CustomerUser::class, 'customer', 'excluded', null, true]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'owner:id',
                        'order' => 80,
                    ],
                ],
                'expectedRules' => [
                    'Owner Id' => [
                        'value' => 'owner:id',
                        'order' => 80,
                    ],
                ],
            ],
        ];
    }
}
