<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerHeadersListener;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Event\LoadEntityRulesAndBackendHeadersEvent;

class CustomerHeadersListenerTest extends \PHPUnit\Framework\TestCase
{
    private const DELIMITER = ':';

    /** @var FieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldHelper;

    /** @var CustomerHeadersListener */
    private $listener;

    protected function setUp(): void
    {
        $this->fieldHelper = $this->createMock(FieldHelper::class);
        $this->listener = new CustomerHeadersListener($this->fieldHelper);
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
            Customer::class,
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

    public function testAfterLoadEntityRulesAndBackendHeadersWhenHeaderAlreadyExists()
    {
        $this->fieldHelper->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturnMap([
                [Customer::class, 'owner', 'excluded', null, false],
                [Customer::class, 'parent', 'excluded', null, false]
            ]);

        $headers = [
            [
                'value' => 'owner:id',
            ],
            [
                'value' => 'parent:id',
            ]
        ];
        $event = new LoadEntityRulesAndBackendHeadersEvent(
            Customer::class,
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
            Customer::class,
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
            'owner and parent fields are excluded, no rules and headers are added' => [
                'fieldsConfig' => [
                    [Customer::class, 'owner', 'excluded', null, true],
                    [Customer::class, 'parent', 'excluded', null, true]
                ],
                'expectedHeaders' => [],
                'expectedRules' => [],
            ],
            'owner and parent fields are not excluded, rules and headers are added for both fields' => [
                'fieldsConfig' => [
                    [Customer::class, 'owner', 'excluded', null, false],
                    [Customer::class, 'parent', 'excluded', null, false]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'owner:id',
                        'order' => 50,
                    ],
                    [
                        'value' => 'parent:id',
                        'order' => 30,
                    ]
                ],
                'expectedRules' => [
                    'Owner Id' => [
                        'value' => 'owner:id',
                        'order' => 50,
                    ],
                    'Parent Id' => [
                        'value' => 'parent:id',
                        'order' => 30,
                    ],
                ],
            ],
            'owner field is excluded and parent field is not, rule and header are added for parent field' => [
                'fieldsConfig' => [
                    [Customer::class, 'owner', 'excluded', null, true],
                    [Customer::class, 'parent', 'excluded', null, false]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'parent:id',
                        'order' => 30,
                    ]
                ],
                'expectedRules' => [
                    'Parent Id' => [
                        'value' => 'parent:id',
                        'order' => 30,
                    ],
                ],
            ],
            'owner field is not excluded and parent field is, rule and header are added for owner field' => [
                'fieldsConfig' => [
                    [Customer::class, 'owner', 'excluded', null, false],
                    [Customer::class, 'parent', 'excluded', null, true]
                ],
                'expectedHeaders' => [
                    [
                        'value' => 'owner:id',
                        'order' => 50,
                    ],
                ],
                'expectedRules' => [
                    'Owner Id' => [
                        'value' => 'owner:id',
                        'order' => 50,
                    ],
                ],
            ],
        ];
    }
}
